<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('body')->required()->rows(4)->columnSpanFull(),
                Select::make('notification_type')->options(NotificationType::class)->default(NotificationType::Manual)->required(),
                Select::make('target_type')->options(NotificationTargetType::class)->required(),
                TextInput::make('image_url')->url()->columnSpanFull(),
                Select::make('article_id')->relationship('article', 'title')->searchable()->preload(),
                DateTimePicker::make('scheduled_at')->label('Schedule for'),
            ]);
    }
}
