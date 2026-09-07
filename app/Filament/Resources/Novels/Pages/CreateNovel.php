<?php

namespace App\Filament\Resources\Novels\Pages;

use App\Filament\Resources\Novels\NovelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNovel extends CreateRecord
{
    protected static string $resource = NovelResource::class;
}
