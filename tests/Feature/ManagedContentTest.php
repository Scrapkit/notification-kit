<?php

declare(strict_types=1);

use Scrapkit\NotificationKit\Domain\Templates\Models\NotificationTemplate;
use Workbench\App\Mail\WelcomeMail;
use Workbench\App\Models\User;
use Workbench\App\Notifications\InvoicePaidNotification;

it('sends mail with content edited in the database', function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();
    NotificationTemplate::query()->where('key', 'users.welcome')->firstOrFail()->update([
        'subject' => 'Benvenuto!',
        'body' => 'Ciao **{{ user.name }}**!',
    ]);

    $mail = new WelcomeMail('Ada');

    expect($mail->envelope()->subject)->toBe('Benvenuto!')
        ->and($mail->rendered()->bodyHtml)->toBe('<p>Ciao <strong>Ada</strong>!</p>')
        ->and($mail->render())->toContain('Ada');
});

it('falls back to code defaults when the template was never synced', function (): void {
    $mail = new WelcomeMail('Ada');

    expect($mail->envelope()->subject)->toBe(WelcomeMail::template()->defaultSubject)
        ->and($mail->render())->toContain('Ada');
});

it('renders notification content through the trait', function (): void {
    $this->artisan('notification-kit:sync')->assertSuccessful();
    NotificationTemplate::query()->where('key', 'invoices.paid_notification')->firstOrFail()->update([
        'body' => 'Invoice {{ invoice.number }} paid.',
    ]);

    $payload = (new InvoicePaidNotification('INV-9'))->toDatabase(new User);

    expect($payload['body'])->toContain('INV-9')
        ->and($payload['title'])->toBe('Invoice paid');
});
