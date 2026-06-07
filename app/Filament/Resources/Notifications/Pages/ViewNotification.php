<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use App\Services\NotificationService;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    public function infolist(Schema $schema): Schema
    {
        $analytics = app(NotificationService::class)->getDeliveryAnalytics($this->record->id);

        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('body')->columnSpanFull(),
                TextEntry::make('status')->badge(),
                TextEntry::make('notification_type')->badge(),
                TextEntry::make('target_type')->badge(),
                TextEntry::make('scheduled_at')->dateTime(),
                TextEntry::make('sent_at')->dateTime(),
                TextEntry::make('total_recipients')->label('Recipients'),
                TextEntry::make('delivered_count')->label('Delivered'),
                TextEntry::make('opened_count')->label('Opened'),
                TextEntry::make('failed_count')->label('Failed'),
                TextEntry::make('analytics_delivery_rate')
                    ->label('Delivery rate')
                    ->state(number_format($analytics['delivery_rate'] ?? 0, 1).'%'),
                TextEntry::make('analytics_open_rate')
                    ->label('Open rate')
                    ->state(number_format($analytics['open_rate'] ?? 0, 1).'%'),
            ]);
    }
}
