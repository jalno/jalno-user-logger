<?php

namespace Jalno\UserLogger\Models;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\HasOwner;
use Jalno\AAA\Models\User;
use Jalno\UserLogger\Casts\Serialize;
use Jalno\UserLogger\Contracts\ILog;
use Jalno\UserLogger\Database\Factories\LogFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @property array $parameters
 * @property int|null $user_id
 * @property IUser|null $user
 * @property string|null $event
 * @property string|null $subject_type
 * @property string|int|null $subject_id
 * @property string|null $ip
 * @property string|null $title
 * @property \DateTimeInterface|int $time
 * @property \DateTimeInterface $created_at
 */
class Log extends Model implements ILog
{
    use HasOwner;
    use HasFactory;

    public static function newFactory(): LogFactory
    {
        return LogFactory::new();
    }

    public const CREATED_AT = 'time';
    public const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'userpanel_logs';

    protected $casts = [
        'parameters' => Serialize::class,
        self::CREATED_AT => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        // To make this works with Jalno, We save subject in Log's parameters.
        if (isset($this->parameters['subject']['type'], $this->parameters['subject']['id'])) {
            $this->attributes['subject_type'] = $this->parameters['subject']['type'];
            $this->attributes['subject_id'] = $this->parameters['subject']['id'];
        }

        return $this->morphTo();
    }

    /**
     * @param array{id?:int,event?:string|string[],user?:int|IUser|null,subject?:Model|string|array{type:string,id?:string}|null,ip?:string|string[]|null} $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['id'])) {
            $query->where('id', $filters['id']);
        }
        if (array_key_exists('user', $filters)) {
            $this->scopeWithUser($query, $filters['user']);
        }
        if (array_key_exists('ip', $filters)) {
            $this->scopeWithIP($query, $filters['ip']);
        }
    }

    /**
     * @param string[]|string|null $ip
     */
    public function scopeWithIP(Builder $query, array|string|null $ip): void
    {
        if (null === $ip) {
            $query->whereNull('ip');

            return;
        }
        if (!is_array($ip)) {
            $ip = [$ip];
        }
        $query->whereIn('ip', $ip);
    }

    public function scopeWithUser(Builder $query, int|IUser|null $user): void
    {
        if (null === $user) {
            $query->whereNull($this->getOwnerUserColumn());

            return;
        }
        $user = User::ensureId($user);
        $query->where($this->getOwnerUserColumn(), $user);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function isAnonymous(): bool
    {
        return is_null($this->user_id);
    }

    public function getSubject(): ?Model
    {
        return $this->subject;
    }

    public function getSubjectType(): ?string
    {
        if (is_null($this->subject_type) and isset($this->parameters['subject']['type'], $this->parameters['subject']['id'])) {
            $this->attributes['subject_type'] = $this->parameters['subject']['type'];
            $this->attributes['subject_id'] = $this->parameters['subject']['id'];
        }
        return $this->subject_type;
    }

    public function getSubjectId(): string|int|null
    {
        if (is_null($this->subject_id) and isset($this->parameters['subject']['type'], $this->parameters['subject']['id'])) {
            $this->attributes['subject_type'] = $this->parameters['subject']['type'];
            $this->attributes['subject_id'] = $this->parameters['subject']['id'];
        }
        return $this->subject_id;
    }

    public function getEvent(): string
    {
        if (is_null($this->event) and isset($this->parameters['event'])) {
            $this->attributes['event'] = $this->parameters['event'];
        }
        return $this->event;
    }

    public function getProperties(): mixed
    {
        return $this->parameters;
    }

    public function getExtraProperty(string $propertyName, mixed $defaultValue = null): mixed
    {
        return Arr::get($this->parameters, $propertyName, $defaultValue);
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        if (is_numeric($this->time)) {
            return Carbon::createFromTimestamp($this->time);
        }
        return $this->time;
    }

    public function owner(): BelongsTo
    {
        return $this->user();
    }

    public function getOwnerUserId(): ?int
    {
        return $this->user_id;
    }

    public function getOwnerUserColumn(): string
    {
        return 'user'; // It should be user_id But, It's in Jalno Legacy Style.
    }

    public function getDateFormat(): string
    {
        return 'U';
    }


    public function setAttribute($key, $value)
    {
        if ($key == 'created_at') {
            $key = 'time';
        }
        if ($key == 'event') {
            $parameters = $this->parameters ?? [];
            $parameters['event'] = $value;

            $key = 'parameters';
            $value = $parameters;
        }
        return parent::setAttribute($key, $value);
    }
    /**
     * Get an attribute from the model.
     * In Jalno's UserPanel we use type key to store id of the type, and now, we try to convert attribute to attribute_id
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($key == 'user' or $key == 'user_id') {
            $this->attributes['user_id'] = $this->attributes['user_id'] ?? $this->attributes['user'];
            unset($this->attributes['user']);
        }
        if ($key == 'created_at') {
            return parent::getAttribute(self::CREATED_AT);
        }
        return parent::getAttribute($key);
    }
}
