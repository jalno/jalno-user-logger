<?php

namespace dnj\UserLogger\Tests\Feature\Http;

use dnj\AAA\Models\Type;
use dnj\AAA\Models\User;
use dnj\UserLogger\Contracts\ILog;
use dnj\UserLogger\Models\Log;
use dnj\UserLogger\Tests\TestCase;

class LogsControllerTest extends TestCase
{
    public function testUnauthenticated(): void
    {
        $this->getJson(route('user-logger.logs.index'))->assertUnauthorized();
    }

    public function testUnauthorized(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson(route('user-logger.logs.index'))->assertForbidden();
    }

    public function testSearch(): void
    {
        $me = $this->createUserWithModelAbility(ILog::class, 'viewAny');

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());

        /**
         * @var User $myChild
         */
        $myChild = User::factory()->withType($myChildType)->create();

        // Unknown User with Unknown Type
        $unknownUser = User::factory()->create();

        $myLog = Log::factory()->withUser($me)->withEvent('login')->create();
        $myChildLog = Log::factory()->withSubject($myLog)->withEvent('destroyed')->withUser($myChild)->create();
        $unknownLog = Log::factory()->withUser($unknownUser)->create();

        $this->actingAs($me);
        $response = $this->getJson(route('user-logger.logs.index'))->assertOk();
        $this->assertIsArray($response['data']);
        $this->assertCount(2, $response['data']);
        $this->assertEqualsCanonicalizing([$myLog->id, $myChildLog->id], array_column($response['data'], 'id'));
        $this->assertNotContains($unknownLog->id, array_column($response['data'], 'id'));
    }

    public function testShow(): void
    {
        $me = $this->createUserWithModelAbility(ILog::class, 'view');
        $this->actingAs($me);

        $myLog = Log::factory()
            ->withUser($me)
            ->withSubject($me->type)
            ->withEvent('updated')
            ->withProperties(['meta' => ['key1' => 'value2']])
            ->create();

        $this->getJson(route('user-logger.logs.show', ['log' => $myLog->id]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    'event' => 'updated',
                    'user_id' => $me->id,
                    'subject_type' => get_class($me->type),
                    'subject_id' => $me->type->getKey(),
                    'properties' => ['meta' => ['key1' => 'value2']],
                ],
            ]);
    }

    public function testDestroy(): void
    {
        $me = $this->createUserWithModelAbility(ILog::class, 'destroy');
        $this->actingAs($me);

        $myChildType = Type::factory()->create();
        $myChildType->parents()->attach($me->getTypeId());
        $myChild = User::factory()->withType($myChildType)->create();

        $childLog = Log::factory()
            ->withUser($myChild)
            ->withEvent('login')
            ->create();

        $this->deleteJson(route('user-logger.logs.destroy', ['log' => $childLog->id]))->assertNoContent();
    }
}
