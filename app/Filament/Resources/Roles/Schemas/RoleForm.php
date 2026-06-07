<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\AdminPermission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Textarea::make('description')->columnSpanFull(),
                CheckboxList::make('permissions')
                    ->options(collect(AdminPermission::cases())->mapWithKeys(
                        fn (AdminPermission $p) => [$p->value => Str::headline(str_replace('.', ' ', $p->value))]
                    )->all())
                    ->columns(2)
                    ->columnSpanFull()
                    ->required(),
                Toggle::make('is_system')->disabled()->dehydrated(false),
            ]);
    }
}
