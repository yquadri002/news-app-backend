<?php

namespace App\Filament\Resources\RssSources\Tables;

use App\Jobs\FetchRssSourceJob;
use App\Services\RssSourceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RssSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->badge(),
                TextColumn::make('health_status')->badge(),
                TextColumn::make('priority')->sortable(),
                TextColumn::make('last_fetched_at')->dateTime()->sortable(),
                TextColumn::make('error_count')->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('articles_count')->counts('articles'),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('health_status'),
                SelectFilter::make('is_active')->options(['1' => 'Active', '0' => 'Inactive']),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('validate')
                    ->icon('heroicon-o-check-badge')
                    ->action(function ($record, RssSourceService $service) {
                        $result = $service->validateSource($record->id);
                        Notification::make()
                            ->title($result['valid'] ? 'Source is valid' : 'Validation failed')
                            ->body($result['error'] ?? "Found {$result['item_count']} items")
                            ->success($result['valid'])
                            ->danger(! $result['valid'])
                            ->send();
                    }),
                Action::make('fetch')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($record) {
                        FetchRssSourceJob::dispatch($record->id);
                        Notification::make()->title('Fetch job dispatched')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
