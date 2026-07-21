<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\SendOutcome;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageApproved;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageCancelled;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageFailed;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessagePendingConfirmation;
use Scrapkit\NotificationKit\Domain\Outbox\Events\MessageSent;
use Scrapkit\NotificationKit\Domain\Outbox\Jobs\SendApprovedOutboxMessage;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Scrapkit\NotificationKit\Exceptions\ConfirmationRequiredException;
use Scrapkit\NotificationKit\Facades\NotificationKit;
use Scrapkit\NotificationKit\Mail\OutboxMail;
use Workbench\App\Mail\InvoicePaidMail;
use Workbench\App\Mail\WelcomeMail;
use Workbench\App\Models\User;

beforeEach(function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();
});

it('sends a non confirmable email straight away', function (): void {
    Mail::fake();
    NotificationTemplate::query()->where('key', 'users.welcome')->firstOrFail()
        ->update(['subject' => 'Benvenuto!']);

    $dispatch = NotificationKit::to('ada@example.com')->send(new WelcomeMail('Ada'));

    expect($dispatch->outcome)->toBe(SendOutcome::SentDirectly)
        ->and($dispatch->message)->toBeNull()
        ->and($dispatch->needsConfirmation())->toBeFalse();

    Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail): bool => $mail->hasTo('ada@example.com'));
    expect(OutboxMessage::query()->count())->toBe(0);
});

it('holds a confirmable email as a pending outbox message instead of sending it', function (): void {
    Mail::fake();
    Event::fake([MessagePendingConfirmation::class]);

    $dispatch = NotificationKit::to('ada@example.com')->send(new InvoicePaidMail('INV-1', 'Ada'));

    expect($dispatch->outcome)->toBe(SendOutcome::PendingConfirmation)
        ->and($dispatch->needsConfirmation())->toBeTrue();

    Mail::assertNothingSent();

    $message = $dispatch->message;

    expect($message)->not->toBeNull()
        ->and($message->status)->toBe(OutboxStatus::Pending)
        ->and($message->template_key)->toBe('invoices.paid')
        ->and($message->mailable_class)->toBe(InvoicePaidMail::class)
        ->and($message->rendered_subject)->toBe('Your invoice INV-1 is paid')
        ->and($message->rendered_body_html)->toContain('INV-1')
        ->and($message->recipients)->toBe([
            ['type' => 'to', 'address' => 'ada@example.com', 'name' => null],
        ]);

    Event::assertDispatched(MessagePendingConfirmation::class);
});

it('records who requested a confirmable send', function (): void {
    Mail::fake();
    $user = User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
    $this->actingAs($user);

    $message = NotificationKit::to('ada@example.com')->send(new InvoicePaidMail('INV-1', 'Ada'))->message;

    expect($message->requested_by_id)->toBe($user->id)
        ->and($message->requested_by_type)->toBe($user->getMorphClass());
});

it('captures cc and bcc recipients in the snapshot', function (): void {
    Mail::fake();

    $message = NotificationKit::to('ada@example.com')
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->send(new InvoicePaidMail('INV-1', 'Ada'))
        ->message;

    expect($message->recipients)->toBe([
        ['type' => 'to', 'address' => 'ada@example.com', 'name' => null],
        ['type' => 'cc', 'address' => 'cc@example.com', 'name' => null],
        ['type' => 'bcc', 'address' => 'bcc@example.com', 'name' => null],
    ]);
});

it('queues the send when a pending message is approved', function (): void {
    Queue::fake();
    Event::fake([MessageApproved::class]);
    $user = User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
    $message = OutboxMessage::factory()->create();

    NotificationKit::approve($message, $user);

    expect($message->refresh())
        ->status->toBe(OutboxStatus::Approved)
        ->decided_by_id->toBe($user->id)
        ->decided_at->not->toBeNull();

    Queue::assertPushed(SendApprovedOutboxMessage::class);
    Event::assertDispatched(MessageApproved::class);
});

it('cancels a pending message without sending anything', function (): void {
    Mail::fake();
    Event::fake([MessageCancelled::class]);
    $message = OutboxMessage::factory()->create();

    NotificationKit::cancel($message, null);

    expect($message->refresh()->status)->toBe(OutboxStatus::Cancelled)
        ->and($message->decided_at)->not->toBeNull();

    Mail::assertNothingSent();
    Event::assertDispatched(MessageCancelled::class);
});

it('sends the snapshot captured at request time, not the latest content', function (): void {
    Mail::fake();

    $message = NotificationKit::to('ada@example.com')->send(new InvoicePaidMail('INV-1', 'Ada'))->message;

    NotificationTemplate::query()->where('key', 'invoices.paid')->firstOrFail()->update([
        'subject' => 'Edited after the request',
        'body' => 'Completely different body',
    ]);

    NotificationKit::approve($message->refresh(), null);

    Mail::assertSent(OutboxMail::class, function (OutboxMail $mail): bool {
        return $mail->envelope()->subject === 'Your invoice INV-1 is paid'
            && $mail->hasTo('ada@example.com');
    });

    expect($message->refresh()->status)->toBe(OutboxStatus::Sent)
        ->and($message->sent_at)->not->toBeNull();
});

it('marks the message sent and fires the event after a successful send', function (): void {
    Mail::fake();
    Event::fake([MessageSent::class]);
    $message = OutboxMessage::factory()->approved()->create();

    (new SendApprovedOutboxMessage($message))->handle();

    expect($message->refresh()->status)->toBe(OutboxStatus::Sent);
    Event::assertDispatched(MessageSent::class);
});

it('ignores a job whose message is no longer approved', function (): void {
    Mail::fake();
    $message = OutboxMessage::factory()->sent()->create();

    (new SendApprovedOutboxMessage($message))->handle();

    Mail::assertNothingSent();
});

it('marks the message failed when the job exhausts its retries', function (): void {
    Event::fake([MessageFailed::class]);
    $message = OutboxMessage::factory()->approved()->create();

    (new SendApprovedOutboxMessage($message))->failed(new RuntimeException('smtp down'));

    expect($message->refresh())
        ->status->toBe(OutboxStatus::Failed)
        ->error->toContain('smtp down');

    Event::assertDispatched(MessageFailed::class);
});

it('refuses to send a confirmable mailable through the mail facade directly', function (): void {
    Mail::to('ada@example.com')->send(new InvoicePaidMail('INV-1', 'Ada'));
})->throws(ConfirmationRequiredException::class);
