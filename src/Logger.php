<?php

namespace Jalno\UserLogger;

use Jalno\UserLogger\Contracts\ILogger;
use Jalno\UserLogger\Models\Log;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Logger implements ILogger
{
    protected string|int|Authenticatable|null $user = null;
    protected ?Model $subject = null;
    protected mixed $properties = null;
    protected ?\DateTimeInterface $createDate = null;
    protected ?string $ip = null;
    protected ?string $event = null;
    protected ?string $title = null;
    protected ?string $type = null;
    protected ?\Closure $postBuild = null;

    public function causedBy(string|int|Authenticatable|null $user): self
    {
        $logger = clone $this;
        $logger->user = $user;

        return $logger;
    }

    public function causedByAnonymous(): self
    {
        return $this->causedBy(null);
    }

    public function byAnonymous(): self
    {
        return $this->causedBy(null);
    }

    public function by(string|int|Authenticatable|null $user): self
    {
        return $this->causedBy($user);
    }

    public function on(?Model $subject): self
    {
        return $this->performedOn($subject);
    }

    public function performedOn(?Model $subject): self
    {
        $logger = clone $this;
        $logger->subject = $subject;

        return $logger;
    }

    public function event(string $event): self
    {
        $logger = clone $this;
        $logger->event = $event;

        return $logger;
    }

    public function withIP(?string $ip): self
    {
        $logger = clone $this;
        $logger->ip = $ip;

        return $logger;
    }

    public function withProperties(?array $properties): self
    {
        $logger = clone $this;
        $logger->properties = $properties;

        return $logger;
    }

    public function withProperty(string $key, mixed $value): self
    {
        $logger = clone $this;
        if (null === $logger->properties) {
            $logger->properties = [];
        }
        $logger->properties[$key] = $value;

        return $logger;
    }

    public function createdAt(?\DateTimeInterface $dateTime): self
    {
        $logger = clone $this;
        $logger->createDate = $dateTime;

        return $logger;
    }

    public function withRequest(?Request $request, bool $captureIP = true, bool $captureUser = true): self
    {
        if (!$request) {
            return $this;
        }
        $logger = clone $this;
        if ($captureIP) {
            $logger->ip = $request->ip();
        }
        if ($captureUser) {
            $logger->user = $request->user();
        }

        return $logger;
    }

    public function withTitle(string $title): self
    {
        $logger = clone $this;
        $logger->title = $title;

        return $logger;
    }

    public function withType(string $type): self
    {
        $logger = clone $this;
        $logger->type = $type;

        return $logger;
    }

    public function postBuild(\Closure $postBuild) 
    {
        $logger = clone $this;
        $logger->postBuild = $postBuild;

        return $logger;
    }

    public function build(): Log
    {
        $time = $this->createDate ?? now();
        $log = new Log();
        $log->ip = $this->ip;
        $log->event = $this->event;
        $log->title = $this->title;
        $log->user = $this->user instanceof Authenticatable ? $this->user->getAuthIdentifier() : $this->user;
        $log->parameters = array_merge_recursive($log->parameters ?? [], [
            'subject' => [
                'type' => get_class($this->subject),
                'id' => $this->subject->getKey(),
            ],
            'properties' => $this->properties,
        ]);
        $log->type = $this->type ?: \packages\userpanel\logs\JalnoUserLogger::class; // @phpstan-ignore-line
        $log->time = $time->getTimestamp();

        if (is_callable($this->postBuild)) {
            call_user_func($this->postBuild, $log);
        }

        return $log;
    }

    public function log(string $event = null): Log
    {
        $logger = $this;
        if ($event) {
            $logger = clone $this;
            $logger->event = $event;
        }
        $model = $logger->build();
        $model->save();

        return $model;
    }
}
