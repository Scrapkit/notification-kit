<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Domain\Templates\Events\TemplateArchived;
use Scrapkit\NotificationKit\Domain\Templates\Events\TemplateContentUpdated;
use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Workbench\App\Models\User;

beforeEach(function (): void {
    Gate::define('viewNotificationKit', fn (): bool => true);
    $this->user = User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'x']);
    $this->actingAs($this->user);
});

it('lists templates without archived ones by default', function (): void {
    NotificationTemplate::factory()->email()->create(['key' => 'invoices.paid', 'name' => 'Invoice paid']);
    NotificationTemplate::factory()->notification()->create(['key' => 'users.suspended', 'name' => 'User suspended']);
    NotificationTemplate::factory()->archived()->create(['key' => 'legacy.newsletter', 'name' => 'Legacy']);

    $response = $this->getJson('notification-kit/api/v1/templates')->assertOk();

    expect($response->json('data.*.key'))->toBe(['invoices.paid', 'users.suspended']);
});

it('filters by type, archived state, confirmation and search', function (): void {
    NotificationTemplate::factory()->email()->confirmable()->create(['key' => 'invoices.paid', 'name' => 'Invoice paid']);
    NotificationTemplate::factory()->notification()->create(['key' => 'users.suspended', 'name' => 'User suspended']);
    NotificationTemplate::factory()->email()->archived()->create(['key' => 'legacy.newsletter', 'name' => 'Legacy']);

    expect($this->getJson('notification-kit/api/v1/templates?type=notification')->json('data.*.key'))
        ->toBe(['users.suspended'])
        ->and($this->getJson('notification-kit/api/v1/templates?archived=only')->json('data.*.key'))
        ->toBe(['legacy.newsletter'])
        ->and($this->getJson('notification-kit/api/v1/templates?archived=with')->json('data.*.key'))
        ->toBe(['invoices.paid', 'legacy.newsletter', 'users.suspended'])
        ->and($this->getJson('notification-kit/api/v1/templates?requires_confirmation=1')->json('data.*.key'))
        ->toBe(['invoices.paid'])
        ->and($this->getJson('notification-kit/api/v1/templates?search=suspend')->json('data.*.key'))
        ->toBe(['users.suspended']);
});

it('rejects an unknown type filter', function (): void {
    $this->getJson('notification-kit/api/v1/templates?type=carrier-pigeon')
        ->assertStatus(422)
        ->assertJsonValidationErrors('type');
});

it('shows a template with its defaults and placeholders', function (): void {
    $template = NotificationTemplate::factory()->create([
        'key' => 'invoices.paid',
        'default_subject' => 'Default subject',
    ]);

    $this->getJson("notification-kit/api/v1/templates/{$template->key}")
        ->assertOk()
        ->assertJsonPath('data.key', 'invoices.paid')
        ->assertJsonPath('data.default_subject', 'Default subject')
        ->assertJsonPath('data.is_customized', false)
        ->assertJsonPath('data.type', TemplateType::Email->value)
        ->assertJsonStructure(['data' => ['placeholders', 'sample_data', 'metadata']]);
});

it('updates content, records a version and fires the event', function (): void {
    Event::fake([TemplateContentUpdated::class]);
    $template = NotificationTemplate::factory()->create();

    $this->putJson("notification-kit/api/v1/templates/{$template->key}/content", [
        'subject' => 'Nuovo oggetto',
        'body' => 'Nuovo corpo {{ user.name }}',
        'metadata' => ['icon' => 'bell'],
        'requires_confirmation' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.subject', 'Nuovo oggetto')
        ->assertJsonPath('data.is_customized', true);

    expect($template->refresh())
        ->requires_confirmation->toBeTrue()
        ->metadata->toBe(['icon' => 'bell'])
        ->and($template->versions()->count())->toBe(1)
        ->and($template->versions()->first()->edited_by_id)->toBe($this->user->id);

    Event::assertDispatched(TemplateContentUpdated::class);
});

it('resets to the code default when the override is null', function (): void {
    $template = NotificationTemplate::factory()->customized()->create(['default_subject' => 'Default subject']);

    $this->putJson("notification-kit/api/v1/templates/{$template->key}/content", [
        'subject' => null,
        'body' => null,
        'requires_confirmation' => false,
    ])->assertOk();

    expect($template->refresh())
        ->subject->toBeNull()
        ->body->toBeNull()
        ->and($template->effectiveSubject())->toBe('Default subject');
});

it('validates the content payload', function (): void {
    $template = NotificationTemplate::factory()->create();

    $this->putJson("notification-kit/api/v1/templates/{$template->key}/content", [
        'subject' => str_repeat('a', 300),
        'requires_confirmation' => 'maybe',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['subject', 'requires_confirmation']);
});

it('archives and unarchives a template without deleting it', function (): void {
    Event::fake([TemplateArchived::class]);
    $template = NotificationTemplate::factory()->create();

    $this->postJson("notification-kit/api/v1/templates/{$template->key}/archive")->assertOk();
    expect($template->refresh()->isArchived())->toBeTrue();
    Event::assertDispatched(TemplateArchived::class);

    $this->postJson("notification-kit/api/v1/templates/{$template->key}/unarchive")->assertOk();
    expect($template->refresh()->isArchived())->toBeFalse()
        ->and(NotificationTemplate::query()->whereKey($template->id)->exists())->toBeTrue();
});

it('returns the version history newest first', function (): void {
    $template = NotificationTemplate::factory()->create();

    foreach (['first', 'second'] as $subject) {
        $this->putJson("notification-kit/api/v1/templates/{$template->key}/content", [
            'subject' => $subject,
            'body' => 'body',
            'requires_confirmation' => false,
        ])->assertOk();
    }

    $response = $this->getJson("notification-kit/api/v1/templates/{$template->key}/versions")->assertOk();

    expect($response->json('data.*.subject'))->toBe(['second', 'first'])
        ->and($response->json('data.0.edited_by'))->toBe('Bob');
});

it('previews stored content with sample data', function (): void {
    $template = NotificationTemplate::factory()->create([
        'subject' => 'Ciao {{ user.name }}',
        'body' => 'Benvenuto **{{ user.name }}**',
        'sample_data' => ['user' => ['name' => 'Ada']],
    ]);

    $this->postJson("notification-kit/api/v1/templates/{$template->key}/preview")
        ->assertOk()
        ->assertJsonPath('data.subject', 'Ciao Ada')
        ->assertJsonPath('data.body_html', '<p>Benvenuto <strong>Ada</strong></p>')
        ->assertJsonPath('data.missing_placeholders', []);
});

it('previews draft content sent from the editor', function (): void {
    $template = NotificationTemplate::factory()->create(['sample_data' => ['user' => ['name' => 'Ada']]]);

    $this->postJson("notification-kit/api/v1/templates/{$template->key}/preview", [
        'subject' => 'Bozza {{ user.name }}',
        'body' => 'Corpo {{ missing.key }}',
    ])
        ->assertOk()
        ->assertJsonPath('data.subject', 'Bozza Ada')
        ->assertJsonPath('data.missing_placeholders', ['missing.key']);
});

it('returns 404 for an unknown template', function (): void {
    $this->getJson('notification-kit/api/v1/templates/does.not.exist')->assertNotFound();
});
