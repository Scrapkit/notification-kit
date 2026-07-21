<?php

declare(strict_types=1);

namespace Workbench\App\Mail;

use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\PlaceholderDefinition;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

final class InvoicePaidMail extends ManagedMailable
{
    public function __construct(
        private readonly string $invoiceNumber,
        private readonly string $customerName,
    ) {}

    public static function template(): TemplateDefinition
    {
        return new TemplateDefinition(
            key: 'invoices.paid',
            type: TemplateType::Email,
            name: 'Invoice paid',
            description: 'Sent when a customer invoice is marked paid.',
            defaultSubject: 'Your invoice {{ invoice.number }} is paid',
            defaultBody: "Hi {{ user.name }},\n\nInvoice **{{ invoice.number }}** was paid. Thank you!",
            placeholders: [
                new PlaceholderDefinition('user.name', 'Recipient full name', 'Ada Lovelace'),
                new PlaceholderDefinition('invoice.number', 'Invoice number', 'INV-2041'),
            ],
            sampleData: [
                'user' => ['name' => 'Ada Lovelace'],
                'invoice' => ['number' => 'INV-2041'],
            ],
            requiresConfirmation: true,
        );
    }

    public function templateData(): array
    {
        return [
            'user' => ['name' => $this->customerName],
            'invoice' => ['number' => $this->invoiceNumber],
        ];
    }
}
