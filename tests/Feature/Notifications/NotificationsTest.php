<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationDispatcherInterface;
use App\Modules\Notifications\DTO\Input\SendNotificationData;
use App\Modules\Notifications\Enums\NotificationType;
use App\Modules\Notifications\Jobs\SendNotificationEmailJob;
use App\Modules\Notifications\Mail\UserNotificationMail;
use Database\Seeders\OnboardingQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(OnboardingQuestionnaireSeeder::class);
    }

    public function test_list_requires_authentication(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
    }

    public function test_dispatcher_creates_notification_and_queues_email(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $dispatcher = app(NotificationDispatcherInterface::class);

        $notification = $dispatcher->send($user, new SendNotificationData(
            type: NotificationType::System,
            title: 'Welcome',
            body: 'Hello from Evolv',
        ));

        $this->assertDatabaseHas('user_notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'title' => 'Welcome',
        ]);

        Queue::assertPushed(SendNotificationEmailJob::class, fn ($job) => $job->notificationId === $notification->id);
    }

    public function test_email_job_sends_mailable(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $dispatcher = app(NotificationDispatcherInterface::class);

        $notification = $dispatcher->send($user, new SendNotificationData(
            type: NotificationType::CoachTip,
            title: 'Tip',
            body: 'Take a short break',
            sendEmail: false,
        ));

        (new SendNotificationEmailJob($notification->id))->handle(app(\App\Modules\Notifications\Contracts\NotificationRepositoryInterface::class));

        Mail::assertSent(UserNotificationMail::class, fn ($mail) => $mail->hasTo($user->email));

        $this->assertNotNull(
            \App\Modules\Notifications\Models\UserNotification::query()->find($notification->id)?->emailed_at,
        );
    }

    public function test_list_and_show_notifications(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $dispatcher = app(NotificationDispatcherInterface::class);
        $created = $dispatcher->send($user, new SendNotificationData(
            type: NotificationType::System,
            title: 'Inbox item',
            body: 'Details here',
            sendEmail: false,
        ));

        $this->getJson('/api/v1/notifications', $headers)
            ->assertOk()
            ->assertJsonPath('notifications.0.id', $created->id)
            ->assertJsonPath('meta.unread_count', 1);

        $this->getJson('/api/v1/notifications/'.$created->id, $headers)
            ->assertOk()
            ->assertJsonPath('title', 'Inbox item')
            ->assertJsonPath('is_read', true);

        $this->getJson('/api/v1/notifications?unread_only=1', $headers)
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    public function test_show_returns_not_found_for_foreign_notification(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('api')->plainTextToken;

        $notification = app(NotificationDispatcherInterface::class)->send($owner, new SendNotificationData(
            type: NotificationType::System,
            title: 'Private',
            body: 'Owner only',
            sendEmail: false,
        ));

        $this->getJson('/api/v1/notifications/'.$notification->id, [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertNotFound()
            ->assertJson([
                'message' => 'Notification not found.',
                'error' => 'notification_not_found',
            ]);
    }

    public function test_preferences_control_email_delivery(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->patchJson('/api/v1/notifications/preferences', [
            'email_enabled' => false,
        ], $headers)->assertOk()->assertJsonPath('email_enabled', false);

        app(NotificationDispatcherInterface::class)->send($user, new SendNotificationData(
            type: NotificationType::System,
            title: 'No email',
            body: 'In-app only',
        ));

        Queue::assertNothingPushed();
    }

    public function test_daily_plan_creates_notifications(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->mock(\App\Modules\AI\Services\LlmRouter::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andThrow(new \App\Modules\AI\Exceptions\LlmException('offline'));
        });

        $this->getJson('/api/v1/coach/daily-plan', $headers)->assertOk();

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type' => 'daily_plan',
        ]);

        $this->getJson('/api/v1/notifications', $headers)
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }
}
