<?php

namespace App\Filament\Resources\Novels\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;

class NovelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        if ($state && !$get('slug')) {
                           $set('slug', Str::slug($state));                            
                        }
                    })
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255),
                Select::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->required(),
                Select::make('uploader_id')
                    ->label('Uploader')
                    ->relationship('uploader', 'name')
                    ->required(),
                Select::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->nullable(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'paused' => 'Paused',
                    ])
                    ->required(),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'text' => 'Text',
                        'manga' => 'Manga',
                    ])
                    ->required(),
                TextInput::make('revenue_share_rate')
                    ->label('Revenue Share Rate')
                    ->numeric()
                    ->required()
                    ->default(70.00)
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01),
                TextInput::make('views_total')
                    ->label('Total Views')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('rating_avg')
                    ->label('Average Rating')
                    ->numeric()
                    ->default(0.00)
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.01),
                Toggle::make('is_hot')
                    ->label('Is Hot')
                    ->default(false),
            ]);
    }
}
