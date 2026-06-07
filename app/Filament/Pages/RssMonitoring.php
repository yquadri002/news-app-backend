<?php

namespace App\Filament\Pages;

use App\Enums\AdminPermission;
use App\Enums\RssHealthStatus;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Jobs\FetchRssSourceJob;
use App\Models\RssSource;
use App\Repositories\Contracts\FeedFetchLogRepositoryInterface;
use App\Services\RssSourceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Stat;
use UnitEnum;

class RssMonitoring extends Page implements HasTable
{
    use AuthorizesAdminPermission;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'RSS Monitoring';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.rss-monitoring';

    public array $healthReport = [];

    public array $fetchStats = [];

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::SourcesManage;
    }

    public function mount(RssSourceService $rssSourceService, FeedFetchLogRepositoryInterface $logs): void
    {
        $this->healthReport = $rssSourceService->getHealthReport();
        $this->fetchStats = $logs->getDashboardStats([
            'from' => now()->subDays(7)->toDateString(),
            'to' => now()->toDateString(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(RssSource::query()->withCount('articles'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('health_status')->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'healthy' => 'success',
                        'degraded' => 'warning',
                        'unhealthy' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('last_fetched_at')->dateTime()->sortable(),
                TextColumn::make('error_count')->sortable(),
                TextColumn::make('last_error')->limit(40)->toggleable(),
                TextColumn::make('articles_count')->label('Articles'),
                TextColumn::make('is_active')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
            ])
            ->filters([
                SelectFilter::make('health_status')->options(RssHealthStatus::class),
            ])
            ->recordActions([
                Action::make('fetch')
                    ->icon('heroicon-o-arrow-path')
                    ->label('Trigger Fetch')
                    ->action(function (RssSource $record) {
                        FetchRssSourceJob::dispatch($record->id);
                        Notification::make()->title("Fetch dispatched for {$record->name}")->success()->send();
                    }),
            ])
            ->defaultSort('last_fetched_at', 'desc');
    }

    public function getStats(): array
    {
        return [
            Stat::make('Total Sources', (string) ($this->healthReport['total'] ?? 0)),
            Stat::make('Healthy', (string) ($this->healthReport['healthy'] ?? 0))->color('success'),
            Stat::make('Degraded', (string) ($this->healthReport['degraded'] ?? 0))->color('warning'),
            Stat::make('Unhealthy', (string) ($this->healthReport['unhealthy'] ?? 0))->color('danger'),
            Stat::make('Fetches (7d)', (string) ($this->fetchStats['total_fetches'] ?? 0)),
            Stat::make('Failed (7d)', (string) ($this->fetchStats['failed'] ?? 0))->color('danger'),
        ];
    }
}
