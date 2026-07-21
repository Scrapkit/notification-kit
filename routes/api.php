<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Scrapkit\NotificationKit\Http\Controllers\ApproveOutboxMessageController;
use Scrapkit\NotificationKit\Http\Controllers\ArchiveTemplateController;
use Scrapkit\NotificationKit\Http\Controllers\CancelOutboxMessageController;
use Scrapkit\NotificationKit\Http\Controllers\ListOutboxMessagesController;
use Scrapkit\NotificationKit\Http\Controllers\ListTemplatesController;
use Scrapkit\NotificationKit\Http\Controllers\ListTemplateVersionsController;
use Scrapkit\NotificationKit\Http\Controllers\PreviewTemplateController;
use Scrapkit\NotificationKit\Http\Controllers\ShowOutboxMessageController;
use Scrapkit\NotificationKit\Http\Controllers\ShowTemplateController;
use Scrapkit\NotificationKit\Http\Controllers\UnarchiveTemplateController;
use Scrapkit\NotificationKit\Http\Controllers\UpdateTemplateContentController;

Route::name('notification-kit.')->group(function (): void {
    Route::get('templates', ListTemplatesController::class)->name('templates.index');
    Route::get('templates/{template}', ShowTemplateController::class)->name('templates.show');
    Route::put('templates/{template}/content', UpdateTemplateContentController::class)->name('templates.content.update');
    Route::post('templates/{template}/archive', ArchiveTemplateController::class)->name('templates.archive');
    Route::post('templates/{template}/unarchive', UnarchiveTemplateController::class)->name('templates.unarchive');
    Route::get('templates/{template}/versions', ListTemplateVersionsController::class)->name('templates.versions.index');
    Route::post('templates/{template}/preview', PreviewTemplateController::class)->name('templates.preview');

    Route::get('outbox', ListOutboxMessagesController::class)->name('outbox.index');
    Route::get('outbox/{message}', ShowOutboxMessageController::class)->name('outbox.show');
    Route::post('outbox/{message}/approve', ApproveOutboxMessageController::class)->name('outbox.approve');
    Route::post('outbox/{message}/cancel', CancelOutboxMessageController::class)->name('outbox.cancel');
});
