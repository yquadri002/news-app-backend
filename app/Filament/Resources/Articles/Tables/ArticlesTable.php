<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Enums\ModerationStatus;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('image_url')->circular()->defaultImageUrl(fn () => null),
                TextColumn::make('title')->searchable()->limit(50)->sortable(),
                TextColumn::make('rssSource.name')->label('Source')->toggleable(),
                TextColumn::make('category.name')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('moderation_status')->badge(),
                IconColumn::make('is_breaking')->boolean(),
                IconColumn::make('is_duplicate')->boolean(),
                TextColumn::make('view_count')->numeric()->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')->options(ArticleStatus::class),
                SelectFilter::make('moderation_status')->options(ModerationStatus::class),
                TernaryFilter::make('is_breaking'),
                TernaryFilter::make('is_duplicate'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->moderation_status === ModerationStatus::Pending)
                    ->action(function ($record, ArticleRepositoryInterface $repo) {
                        $repo->approve($record->id);
                        Notification::make()->title('Article approved')->success()->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->moderation_status === ModerationStatus::Pending)
                    ->schema([Textarea::make('reason')->required()])
                    ->action(function ($record, array $data, ArticleRepositoryInterface $repo) {
                        $repo->reject($record->id, $data['reason']);
                        Notification::make()->title('Article rejected')->warning()->send();
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
