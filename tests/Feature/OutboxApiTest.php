<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Scrapkit\NotificationKit\Domain\Outbox\Enums\OutboxStatus;
use Scrapkit\NotificationKit\Domain\Outbox\Jobs\SendApprovedOutboxMessage;
use Scrapkit\NotificationKit\Domain\Outbox\Models\OutboxMessage;
use Workbench\App\Models\User;

beforeEach(function (): void {
    Gate::define('viewNotificationKit', fn (): bool => true);
    $this->user = User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
    $this->actingAs($this->user);
});

it('lists outbox messages newest first with filters', function (): void {
    $pending = OutboxMessage::factory()->create(['template_key' => 'invoices.paid', 'created_at' => now()->subDay()]);
    $sent = OutboxMessage::factory()->sent()->create(['template_key' => 'users.welcome']);

    expect($this->getJson('notification-kit/api/v1/outbox')->json('data.*.uuid'))
        ->toBe([$sent->uuid, $pending->uuid])
        ->and($this->getJson('notification-kit/api/v1/outbox?status=pending')->json('data.*.uuid'))
        ->toBe([$pending->uuid])
        ->and($this->getJson('notification-kit/api/v1/outbox?template_key=users.welcome')->json('data.*.uuid'))
        ->toBe([$sent->uuid]);
});

it('shows the snapshot an approver decides on', function (): void {
    $message = OutboxMessage::factory()->create([
        'rendered_subject' => 'Your invoice INV-1 is paid',
        'rendered_body_html' => '<p>Hi Ada</p>',
    ]);

    $this->getJson("notification-kit/api/v1/outbox/{$message->uuid}")
        ->assertOk()
        ->assertJsonPath('data.rendered_subject', 'Your invoice INV-1 is paid')
        ->assertJsonPath('data.rendered_body_html', '<p>Hi Ada</p>')
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonStructure(['data' => ['recipients', 'template_key', 'created_at']]);
});

it('approves a pending message and queues the send', function (): void {
    Queue::fake();
    $message = OutboxMessage::factory()->create();

    $this->postJson("notification-kit/api/v1/outbox/{$message->uuid}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');

    expect($message->refresh()->decided_by_id)->toBe($this->user->id);
    Queue::assertPushed(SendApprovedOutboxMessage::class);
});

it('cancels a pending message', function (): void {
    Mail::fake();
    $message = OutboxMessage::factory()->create();

    $this->postJson("notification-kit/api/v1/outbox/{$message->uuid}/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    Mail::assertNothingSent();
});

it('refuses a decision on a message that is no longer pending', function (): void {
    $message = OutboxMessage::factory()->sent()->create();

    $this->postJson("notification-kit/api/v1/outbox/{$message->uuid}/approve")
        ->assertStatus(409);

    expect($message->refresh()->status)->toBe(OutboxStatus::Sent);
});

it('returns 404 for an unknown uuid', function (): void {
    $this->getJson('notification-kit/api/v1/outbox/'.Str::uuid())->assertNotFound();
});
