<?php

namespace Tests\Feature\Request;

use App\Models\Category;
use App\Models\Organizer;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('request')]
#[Group('event')]
class StoreEventRequestTest extends TestCase
{
    private array $basePayload;

    protected function setUp(): void
    {
        parent::setUp();

        $user      = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $category  = Category::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $this->basePayload = [
            'title'         => 'Test Event',
            'type'          => 'free',
            'datetime_from' => now()->addDay()->toDateTimeString(),
            'datetime_to'   => now()->addDays(2)->toDateTimeString(),
            'organizer_id'  => $organizer->id,
            'category_id'   => $category->id,
        ];
    }

    #[Test]
    public function title_is_required(): void
    {
        $payload = $this->basePayload;
        unset($payload['title']);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    #[Test]
    public function type_is_required(): void
    {
        $payload = $this->basePayload;
        unset($payload['type']);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('type');
    }

    #[Test]
    public function datetime_from_must_be_after_now(): void
    {
        $payload = array_merge($this->basePayload, [
            'datetime_from' => now()->subDay()->toDateTimeString(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('datetime_from');
    }

    #[Test]
    public function datetime_to_must_be_after_datetime_from(): void
    {
        $payload = array_merge($this->basePayload, [
            'datetime_from' => now()->addDays(2)->toDateTimeString(),
            'datetime_to'   => now()->addDay()->toDateTimeString(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('datetime_to');
    }

    #[Test]
    public function capacity_must_be_at_least_one_when_provided(): void
    {
        $payload = array_merge($this->basePayload, ['capacity' => 0]);

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('capacity');
    }
}
