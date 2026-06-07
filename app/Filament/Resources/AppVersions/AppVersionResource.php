<?php

namespace App\Filament\Resources\AppVersions;

use App\Enums\AdminPermission;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Filament\Resources\AppVersions\Pages\CreateAppVersion;
use App\Filament\Resources\AppVersions\Pages\EditAppVersion;
use App\Filament\Resources\AppVersions\Pages\ListAppVersions;
use App\Filament\Resources\AppVersions\Schemas\AppVersionForm;
use App\Filament\Resources\AppVersions\Tables\AppVersionsTable;
use App\Models\AppVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AppVersionResource extends Resource
{
    use AuthorizesAdminPermission;

    protected static ?string $model = AppVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'version_name';

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::AppUpdatesManage;
    }

    public static function form(Schema $schema): Schema
    {
        return AppVersionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppVersionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppVersions::route('/'),
            'create' => CreateAppVersion::route('/create'),
            'edit' => EditAppVersion::route('/{record}/edit'),
        ];
    }
}
