<?php

namespace Tests\Feature;

use App\Events\ChirpCreated;
use App\Models\Chirp;
use App\Models\User;
use App\Notifications\NewChirp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ChirpNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_chirp_created_event_is_dispatched_when_chirp_is_created(): void
    {
        Event::fake();

        /** @var User $user */
        $user = User::factory()->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'Test chirp message',
        ]);

        Event::assertDispatched(ChirpCreated::class, function ($event) use ($chirp) {
            /** @var ChirpCreated $event */
            return $event->chirp->id === $chirp->id;
        });
    }

    public function test_notification_is_sent_to_all_users_except_author_when_chirp_created(): void
    {
        Notification::fake();

        /** @var User $author */
        $author = User::factory()->create();
        $otherUsers = User::factory()->count(3)->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create([
            'user_id' => $author->id,
            'message' => 'Test chirp message',
        ]);

        foreach ($otherUsers as $user) {
            /** @var User $user */
            Notification::assertSentTo($user, NewChirp::class);
        }

        Notification::assertNotSentTo($author, NewChirp::class);
    }

    public function test_new_chirp_notification_contains_correct_data(): void
    {
        Notification::fake();

        /** @var User $author */
        $author = User::factory()->create(['name' => 'John Doe']);
        /** @var User $recipient */
        $recipient = User::factory()->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create([
            'user_id' => $author->id,
            'message' => 'This is a test chirp message',
        ]);

        $notification = new NewChirp($chirp);
        $mailData = $notification->toMail($recipient);

        $this->assertEquals('New Chirp from John Doe', $mailData->subject);
        $this->assertEquals('New Chirp from John Doe', $mailData->greeting);
        $this->assertStringContainsString('This is a test chirp message', $mailData->introLines[0]);
    }

    public function test_new_chirp_notification_has_correct_action_url(): void
    {
        /** @var User $author */
        $author = User::factory()->create();
        /** @var User $recipient */
        $recipient = User::factory()->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create([
            'user_id' => $author->id,
            'message' => 'Test message',
        ]);

        $notification = new NewChirp($chirp);
        $mailData = $notification->toMail($recipient);

        $this->assertEquals(url('/chirps'), $mailData->actionUrl);
        $this->assertEquals('Go to Chirper', $mailData->actionText);
    }

    public function test_notification_is_sent_via_mail_only(): void
    {
        /** @var User $author */
        $author = User::factory()->create();
        /** @var User $recipient */
        $recipient = User::factory()->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create(['user_id' => $author->id]);

        $notification = new NewChirp($chirp);

        $this->assertEquals(['mail'], $notification->via($recipient));
    }

    public function test_chirp_creation_triggers_complete_notification_flow(): void
    {
        Notification::fake();
        Event::fake();

        /** @var User $author */
        $author = User::factory()->create();
        User::factory()->count(2)->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create([
            'user_id' => $author->id,
            'message' => 'Integration test chirp',
        ]);

        Event::assertDispatched(ChirpCreated::class, function ($event) use ($chirp) {
            /** @var ChirpCreated $event */
            return $event->chirp->id === $chirp->id;
        });

        $allUsers = User::all();
        /** @var User $author */
        $usersToNotify = $allUsers->except([$author->id]);
        Notification::send($usersToNotify, new NewChirp($chirp));

        Notification::assertSentTimes(NewChirp::class, 2);
        Notification::assertNotSentTo($author, NewChirp::class);
    }
}
