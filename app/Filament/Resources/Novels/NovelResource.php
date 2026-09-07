<?php

namespace App\Filament\Resources\Novels;

use App\Filament\Resources\Novels\Pages\CreateNovel;
use App\Filament\Resources\Novels\Pages\EditNovel;
use App\Filament\Resources\Novels\Pages\ListNovels;
use App\Filament\Resources\Novels\Schemas\NovelForm;
use App\Filament\Resources\Novels\Tables\NovelsTable;
use App\Models\Novel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NovelResource extends Resource
{
    protected static ?string $model = Novel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return NovelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NovelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNovels::route('/'),
            'create' => CreateNovel::route('/create'),
            'edit' => EditNovel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
