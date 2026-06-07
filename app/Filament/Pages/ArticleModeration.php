<?php

namespace App\Filament\Pages;

use App\Enums\AdminPermission;
use App\Enums\ModerationStatus;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ArticleModeration extends Page implements HasTable
{
    use AuthorizesAdminPermission;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Article Moderation';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.article-moderation';

    public string $activeTab = 'pending';

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::SourcesManage;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                ImageColumn::make('image_url')->circular(),
                TextColumn::make('title')->searchable()->limit(60),
                TextColumn::make('rssSource.name')->label('Source'),
                TextColumn::make('category.name')->badge(),
                TextColumn::make('moderation_status')->badge(),
                IconColumn::make('is_duplicate')->boolean(),
                TextColumn::make('duplicateOf.title')->label('Duplicate of')->limit(30),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('moderation_status')->options(ModerationStatus::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Article $record) => ! $record->is_duplicate && $record->moderation_status === ModerationStatus::Pending)
                    ->action(function (Article $record, ArticleRepositoryInterface $repo) {
                        $repo->approve($record->id);
                        Notification::make()->title('Article approved')->success()->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Article $record) => ! $record->is_duplicate && $record->moderation_status === ModerationStatus::Pending)
                    ->schema([Textarea::make('reason')->required()])
                    ->action(function (Article $record, array $data, ArticleRepositoryInterface $repo) {
                        $repo->reject($record->id, $data['reason']);
                        Notification::make()->title('Article rejected')->warning()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->activeTab) {
            'duplicates' => Article::query()->where('is_duplicate', true)->with(['rssSource', 'category', 'duplicateOf']),
            default => Article::query()
                ->where('moderation_status', ModerationStatus::Pending)
                ->where('is_duplicate', false)
                ->with(['rssSource', 'category']),
        };
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }
}
