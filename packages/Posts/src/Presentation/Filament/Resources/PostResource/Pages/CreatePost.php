<?php

namespace CodeGarage\Posts\Presentation\Filament\Resources\PostResource\Pages;

use CodeGarage\Posts\Presentation\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
