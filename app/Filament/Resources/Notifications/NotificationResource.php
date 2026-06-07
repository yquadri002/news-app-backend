<?php

namespace App\Filament\Resources\Notifications;

use App\Enums\AdminPermission;
use App\Filament\Concerns\AuthorizesAdminPermission;
use App\Filament\Resources\Notifications\Pages\CreateNotification;
use App\Filament\Resources\Notifications\Pages\EditNotification;
use App\Filament\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Resources\Notifications\Pages\ViewNotification;
use App\Filament\Resources\Notifications\Schemas\NotificationForm;
use App\Filament\Resources\Notifications\Tables\NotificationsTable;
use App\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NotificationResource extends Resource
{
    use AuthorizesAdminPermission;

    protected static ?string $model = Notification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    protected static function requiredPermission(): ?AdminPermission
    {
        return AdminPermission::NotificationsManage;
    }

    public static function form(Schema $schema): Schema
    {
        return NotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
            'view' => ViewNotification::route('/{record}'),
            'edit' => EditNotification::route('/{record}/edit'),
        ];
    }
}
