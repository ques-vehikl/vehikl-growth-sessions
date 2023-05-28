<?php

namespace Tests\Feature\GrowthSessions;

use App\AnyDesk;
use App\GrowthSession;
use App\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class GrowthSessionsStoreTest extends TestCase
{
    public function testAGrowthSessionCannotBeCreatedWithAnAnydeskIdThatDoesNotExist()
    {
        $growthSessionAttributes = GrowthSession::factory()->raw();
        $user = User::factory()->vehiklMember()->create();


        $growthSessionAttributes['anydesk_id'] = 999999;
        $growthSessionAttributes['start_time'] = '09:00 am';
        $growthSessionAttributes['end_time'] = '10:00 am';

        $this->actingAs($user)->postJson(route('growth_sessions.store'), $growthSessionAttributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['anydesk_id' => 'The selected anydesk id is invalid.']);
    }

    public function testAUserCanCreateAGrowthSessionWithAnAnyDesk()
    {
        $this->setTestNow('2020-01-15');
        $user = User::factory()->vehiklMember()->create();
        $anyDesk = AnyDesk::factory()->create();

        $response = $this->actingAs($user)->postJson(
            route('growth_sessions.store'),
            $this->defaultParameters(['anydesk_id' => $anyDesk->id])
        );

        $response->assertSuccessful();
        $growth = GrowthSession::find(1);
        $this->assertEquals($anyDesk->id, $growth->anydesk_id);
    }

    public function provideWatcherPayload()
    {
        return [
            'allows watchers' => [true],
            'does not allow watchers' => [false],
        ];
    }

    /** @dataProvider provideWatcherPayload */
    public function testAGrowthSessionCanBeCreatedWithAllowWatchers($watcherFlag)
    {
        $this->setTestNow('2020-01-15');
        $user = User::factory()->vehiklMember()->create();

        $this->actingAs($user)->postJson(
            route('growth_sessions.store'),
            $this->defaultParameters(['allow_watchers' => $watcherFlag])
        )->assertSuccessful();

        $this->assertEquals($watcherFlag, $user->fresh()->growthSessions->first()->allow_watchers);
    }

    public function testAGrowthSessionCannotBeCreatedDuringTheWeekend()
    {
        $this->setTestNow('2020-01-15');
        $growthSessionAttributes = GrowthSession::factory()->raw();
        $user = User::factory()->vehiklMember()->create();

        $growthSessionAttributes['date'] = '2020-01-18';
        $growthSessionAttributes['start_time'] = '09:00 am';
        $growthSessionAttributes['end_time'] = '10:00 am';

        $this->actingAs($user)->postJson(route('growth_sessions.store'), $growthSessionAttributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['date' => 'A growth session can not be hosted on weekends.']);
    }

    public function testAUserCanCreateAPubliclyAvailableGrowthSession()
    {
        $this->setTestNow('2020-01-15');

        /** @var User $user */
        $user = User::factory()->create(['is_vehikl_member' => TRUE]);

        $this->actingAs($user)->postJson(
            route('growth_sessions.store'),
            $this->defaultParameters(['is_public' => TRUE])
        );

        $this->assertTrue(GrowthSession::find(1)->is_public);
    }

    public function testVehiklMembersCanCreateAGrowthSession(): void
    {
        $vehiklMember = User::factory()->vehiklMember()->create();
        $growthSessionsAttributes = GrowthSession::factory()->make()->toArray();

        $this->actingAs($vehiklMember)
            ->post(route('growth_sessions.store'), $growthSessionsAttributes)
            ->assertSuccessful();

        $this->assertNotEmpty(GrowthSession::query()->where('title', $growthSessionsAttributes['title'])->first());
    }

    public function testNonVehiklMembersCannotCreateAGrowthSession(): void
    {
        /** @var User $nonVehiklMember */
        $nonVehiklMember = User::factory()->create();
        $growthSessionsAttributes = GrowthSession::factory()->make()->toArray();

        $this->actingAs($nonVehiklMember)
            ->post(route('growth_sessions.store'), $growthSessionsAttributes)
            ->assertForbidden();

        $this->assertEmpty(GrowthSession::query()->where('title', $growthSessionsAttributes['title'])->first());
    }

    public function testAnAttendeeLimitCanBeSetWhenCreatingAtGrowthSession()
    {
        $this->setTestNow('2020-01-15');

        $user = User::factory()->vehiklMember()->create();

        $expectedAttendeeLimit = 420;
        $this->actingAs($user)->postJson(
            route('growth_sessions.store'),
            $this->defaultParameters(['attendee_limit' => $expectedAttendeeLimit])
        )->assertSuccessful();

        $this->assertEquals($expectedAttendeeLimit, $user->growthSessions->first()->attendee_limit);
    }

    public function testAnAttendeeLimitCannotBeLessThanFour()
    {
        $vehiklMember = User::factory()->vehiklMember()->create();

        $expectedAttendeeLimit = 3;
        $this->actingAs($vehiklMember)->postJson(
            route('growth_sessions.store'),
            $this->defaultParameters(['attendee_limit' => $expectedAttendeeLimit])
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['attendee_limit' => 'The attendee limit must be at least 4']);
    }

    private function defaultParameters(array $params = []): array
    {
        return array_merge([
            'topic' => 'The fundamentals of foo',
            'title' => 'Foo',
            'location' => 'At the central mobbing area',
            'start_time' => now()->format('h:i a'),
            'date' => today(),
            'discord_channel' => null,
        ], $params);
    }

    public function testIncludesAttendeesInformationEvenForANewlyCreatedGrowthSession(): void
    {
        $vehiklMember = User::factory()->vehiklMember()->create();
        $growthSessionsAttributes = GrowthSession::factory()->make()->toArray();

        $this->actingAs($vehiklMember)
            ->post(route('growth_sessions.store'), $growthSessionsAttributes)
            ->assertJsonFragment([
                'attendees' => []
            ]);
    }

    public function testAUserCannotCreateTwoGrowthSessionsInTheSameTimeSlot()
    {
        $vehiklMember = User::factory()->vehiklMember()->create();
        $growthSessionsAttributes = GrowthSession::factory()->make()->toArray();

        $this->actingAs($vehiklMember)
            ->postJson(route('growth_sessions.store'), $growthSessionsAttributes)
            ->assertSuccessful();

        $this->actingAs($vehiklMember)
            ->postJson(route('growth_sessions.store'), $growthSessionsAttributes)
            ->assertUnprocessable();
    }
}
