<?php

namespace App\Filament\Resources\RssSources\Schemas;

use App\Enums\RssHealthStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RssSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('url')->url()->required()->maxLength(2048)->columnSpanFull(),
                Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
                TextInput::make('priority')->numeric()->default(1)->minValue(1)->maxValue(100),
                TextInput::make('fetch_interval_minutes')->numeric()->default(60)->minValue(5),
                Select::make('health_status')->options(RssHealthStatus::class)->disabled()->dehydrated(false),
                Toggle::make('is_active')->default(true),
                Toggle::make('is_validated')->disabled()->dehydrated(false),
            ]);
    }
}
