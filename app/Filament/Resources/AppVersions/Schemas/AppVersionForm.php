<?php

namespace App\Filament\Resources\AppVersions\Schemas;

use App\Enums\AppPlatform;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AppVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('platform')->options(AppPlatform::class)->required(),
                TextInput::make('version_name')->required(),
                TextInput::make('version_code')->numeric()->required(),
                TextInput::make('min_supported_version_code')->numeric(),
                TextInput::make('download_url')->url()->columnSpanFull(),
                Textarea::make('release_notes')->rows(4)->columnSpanFull(),
                Toggle::make('is_force_update'),
                Toggle::make('is_soft_update'),
                Toggle::make('is_active')->default(true),
                DateTimePicker::make('released_at'),
            ]);
    }
}
