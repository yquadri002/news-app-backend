<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Enums\NotificationStatus;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('notification_type')->badge(),
                TextColumn::make('target_type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_at')->dateTime()->sortable(),
                TextColumn::make('sent_at')->dateTime()->sortable(),
                TextColumn::make('total_recipients')->numeric(),
                TextColumn::make('delivered_count')->numeric(),
                TextColumn::make('opened_count')->numeric(),
                TextColumn::make('creator.name')->label('Created by'),
            ])
            ->filters([
                SelectFilter::make('status')->options(NotificationStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status?->value ?? $record->status, ['draft', 'scheduled']))
                    ->action(function ($record, NotificationService $service) {
                        $service->sendNow($record->id);
                        Notification::make()->title('Notification dispatched')->success()->send();
                    }),
                Action::make('cancel')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status?->value ?? $record->status, ['draft', 'scheduled']))
                    ->action(function ($record, NotificationService $service) {
                        $service->cancel($record->id);
                        Notification::make()->title('Notification cancelled')->warning()->send();
                    }),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
