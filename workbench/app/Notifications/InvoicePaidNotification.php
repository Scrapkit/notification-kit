<?php

declare(strict_types=1);

namespace Workbench\App\Notifications;

use Illuminate\Notifications\Notification;
use Scrapkit\NotificationKit\Contracts\Manageable;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\PlaceholderDefinition;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Notifications\Concerns\HasManagedContent;

final class InvoicePaidNotification extends Notification implements Manageable
{
    use HasManagedContent;

    public function __construct(private readonly string $invoiceNumber) {}

    public static function template(): TemplateDefinition
    {
        return new TemplateDefinition(
            key: 'invoices.paid_notification',
            type: TemplateType::Notification,
            name: 'Invoice paid (in-app)',
            description: 'Shown in the in-app notification center when an invoice is paid.',
            defaultSubject: 'Invoice paid',
            defaultBody: 'Invoice {{ invoice.number }} was paid.',
            placeholders: [
                new PlaceholderDefinition('invoice.number', 'Invoice number', 'INV-2041'),
            ],
            sampleData: ['invoice' => ['number' => 'INV-2041']],
            metadata: ['icon' => 'banknote'],
        );
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $content = $this->renderManaged(['invoice' => ['number' => $this->invoiceNumber]]);

        return [
            'title' => $content->subject,
            'body' => $content->bodyHtml,
        ];
    }
}
