<?php

namespace App\Filament\Resources\AppVersions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('version_code', 'desc')
            ->columns([
                TextColumn::make('platform')->badge(),
                TextColumn::make('version_name')->searchable(),
                TextColumn::make('version_code')->sortable(),
                IconColumn::make('is_force_update')->boolean(),
                IconColumn::make('is_soft_update')->boolean(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('released_at')->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('platform')])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
