<?php

declare(strict_types=1);

use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Exceptions\InvalidOutboxTransition;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Scrapkit\NotificationKit\Domain\Outbox\States\OutboxState;

it('allows only the designed transitions', function (OutboxStatus $from, OutboxStatus $to, bool $allowed): void {
    expect(OutboxState::for($from)->canTransitionTo($to))->toBe($allowed);
})->with([
    'pending to approved' => [OutboxStatus::Pending, OutboxStatus::Approved, true],
    'pending to cancelled' => [OutboxStatus::Pending, OutboxStatus::Cancelled, true],
    'pending to sent' => [OutboxStatus::Pending, OutboxStatus::Sent, false],
    'pending to failed' => [OutboxStatus::Pending, OutboxStatus::Failed, false],
    'approved to sent' => [OutboxStatus::Approved, OutboxStatus::Sent, true],
    'approved to failed' => [OutboxStatus::Approved, OutboxStatus::Failed, true],
    'approved to cancelled' => [OutboxStatus::Approved, OutboxStatus::Cancelled, false],
    'approved to pending' => [OutboxStatus::Approved, OutboxStatus::Pending, false],
    'sent is terminal' => [OutboxStatus::Sent, OutboxStatus::Failed, false],
    'cancelled is terminal' => [OutboxStatus::Cancelled, OutboxStatus::Approved, false],
    'failed is terminal' => [OutboxStatus::Failed, OutboxStatus::Sent, false],
]);

it('transitions the model and persists the new status', function (): void {
    $message = OutboxMessage::factory()->create();

    $message->transitionTo(OutboxStatus::Approved);

    expect($message->refresh()->status)->toBe(OutboxStatus::Approved);
});

it('throws on an invalid transition', function (): void {
    $message = OutboxMessage::factory()->create(['status' => OutboxStatus::Sent]);

    $message->transitionTo(OutboxStatus::Approved);
})->throws(InvalidOutboxTransition::class);
