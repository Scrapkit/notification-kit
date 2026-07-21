<?php

declare(strict_types=1);

namespace Scrapkit\NotificationKit\Console;

use Illuminate\Console\Command;
use Scrapkit\NotificationKit\Domain\Templates\Actions\SyncTemplatesAction;
use Scrapkit\NotificationKit\Support\KitConfig;

final class SyncTemplatesCommand extends Command
{
    protected $signature = 'notification-kit:sync';

    protected $description = 'Upsert the template definitions declared by the classes in notification-kit.manageables';

    public function handle(SyncTemplatesAction $action): int
    {
        $result = $action->execute(KitConfig::manageables());

        $this->components->info(sprintf(
            '%d template(s) created, %d refreshed.',
            count($result->created),
            count($result->updated),
        ));

        foreach ($result->orphaned as $key) {
            $this->components->warn("No class declares [{$key}] any more. Archive it from the UI if it is no longer used.");
        }

        return self::SUCCESS;
    }
}
