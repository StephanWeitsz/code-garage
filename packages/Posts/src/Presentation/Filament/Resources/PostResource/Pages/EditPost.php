<?php

namespace CodeGarage\Posts\Presentation\Filament\Resources\PostResource\Pages;

use CodeGarage\Posts\Presentation\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
        ];
    }
}
