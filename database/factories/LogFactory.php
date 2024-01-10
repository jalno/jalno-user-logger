<?php

namespace Jalno\UserLogger\Database\Factories;

use dnj\AAA\Contracts\IUser;
use Jalno\AAA\Models\User;
use Jalno\UserLogger\Models\Log;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Log>
 */
class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition()
    {
        return [
            'event' => fake()->randomElement(['created', 'updated', 'destroyed']),
            'user_id' => User::factory(),
            'created_at' => now(),
            'properties' => null,
            'ip' => fake()->randomElement([fake()->ipv4(), fake()->ipv6()]),
        ];
    }

    public function withProperties(array|null $properties): static
    {
        return $this->state(fn () => [
            'properties' => $properties,
        ]);
    }

    public function withUser(IUser|int|null $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user ? User::ensureId($user) : null,
        ]);
    }

    public function withEvent(string $event): static
    {
        return $this->state(fn () => [
            'event' => $event,
        ]);
    }

    public function withSubject(Model|null $subject): static
    {
        return $this->state(fn () => [
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
        ]);
    }
}
