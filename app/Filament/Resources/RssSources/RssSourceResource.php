<?php

namespace App\Filament\Resources\RssSources;

use App\Enums\AdminPermission;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Filament\Resources\RssSources\Pages\CreateRssSource;
use App\Filament\Resources\RssSources\Pages\EditRssSource;
use App\Filament\Resources\RssSources\Pages\ListRssSources;
use App\Filament\Resources\RssSources\Schemas\RssSourceForm;
use App\Filament\Resources\RssSources\Tables\RssSourcesTable;
use App\Models\RssSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RssSourceResource extends Resource
{
    use AuthorizesAdminPermission;

    protected static ?string $model = RssSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::SourcesManage;
    }

    public static function form(Schema $schema): Schema
    {
        return RssSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RssSourcesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRssSources::route('/'),
            'create' => CreateRssSource::route('/create'),
            'edit' => EditRssSource::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
