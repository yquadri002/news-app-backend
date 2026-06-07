<?php

namespace App\Filament\Resources\AdPlacements;

use App\Enums\AdminPermission;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Filament\Resources\AdPlacements\Pages\CreateAdPlacement;
use App\Filament\Resources\AdPlacements\Pages\EditAdPlacement;
use App\Filament\Resources\AdPlacements\Pages\ListAdPlacements;
use App\Filament\Resources\AdPlacements\Schemas\AdPlacementForm;
use App\Filament\Resources\AdPlacements\Tables\AdPlacementsTable;
use App\Models\AdPlacement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdPlacementResource extends Resource
{
    use AuthorizesAdminPermission;

    protected static ?string $model = AdPlacement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::AdsManage;
    }

    public static function form(Schema $schema): Schema
    {
        return AdPlacementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdPlacementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdPlacements::route('/'),
            'create' => CreateAdPlacement::route('/create'),
            'edit' => EditAdPlacement::route('/{record}/edit'),
        ];
    }
}
