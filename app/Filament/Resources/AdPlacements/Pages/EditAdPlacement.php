<?php

namespace App\Filament\Resources\AdPlacements\Pages;

use App\Filament\Resources\AdPlacements\AdPlacementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdPlacement extends EditRecord
{
    protected static string $resource = AdPlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
