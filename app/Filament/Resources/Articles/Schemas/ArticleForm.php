<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Enums\ModerationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')->required()->columnSpanFull(),
                Select::make('rss_source_id')->relationship('rssSource', 'name')->searchable()->preload(),
                Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
                Select::make('status')->options(ArticleStatus::class)->required(),
                Select::make('moderation_status')->options(ModerationStatus::class)->required(),
                TextInput::make('author'),
                TextInput::make('source_name'),
                TextInput::make('image_url')->url()->columnSpanFull(),
                TextInput::make('external_url')->url()->columnSpanFull(),
                Textarea::make('summary')->rows(3)->columnSpanFull(),
                Textarea::make('content')->rows(8)->columnSpanFull(),
                Toggle::make('is_breaking'),
                DateTimePicker::make('published_at'),
                Textarea::make('rejection_reason')->columnSpanFull(),
            ]);
    }
}
