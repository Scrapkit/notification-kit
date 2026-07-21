<?php

declare(strict_types=1);

namespace Workbench\App\Mail;

use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\PlaceholderDefinition;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

final class WelcomeMail extends ManagedMailable
{
    public function __construct(private readonly string $userName) {}

    public static function template(): TemplateDefinition
    {
        return new TemplateDefinition(
            key: 'users.welcome',
            type: TemplateType::Email,
            name: 'Welcome',
            description: 'Sent right after a user registers.',
            defaultSubject: 'Welcome aboard',
            defaultBody: "Hi {{ user.name }},\n\nWelcome to the app!",
            placeholders: [
                new PlaceholderDefinition('user.name', 'Recipient full name', 'Ada Lovelace'),
            ],
            sampleData: ['user' => ['name' => 'Ada Lovelace']],
        );
    }

    public function templateData(): array
    {
        return ['user' => ['name' => $this->userName]];
    }
}
