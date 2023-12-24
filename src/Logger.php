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

    public function build(): Log
    {
        $log = new Log();
        $log->event = $this->event;
        $log->user_id = $this->user instanceof Authenticatable ? $this->user->getAuthIdentifier() : $this->user;
        $log->subject()->associate($this->subject);
        $log->properties = $this->properties;
        $log->created_at = $this->createDate ?? now();
        $log->ip = $this->ip;

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
