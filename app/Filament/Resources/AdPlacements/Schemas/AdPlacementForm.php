<?php

namespace App\Filament\Resources\AdPlacements\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdPlacementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('placement_key')->required()->unique(ignoreRecord: true),
                TextInput::make('format')->required(),
                TextInput::make('frequency_cap')->numeric()->default(3),
                TextInput::make('frequency_period_minutes')->numeric()->default(60),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_enabled')->default(true),
                KeyValue::make('remote_config')->columnSpanFull(),
            ]);
    }
}
