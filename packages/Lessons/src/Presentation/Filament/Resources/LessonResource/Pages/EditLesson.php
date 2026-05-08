<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? LessonResource::getUrl('index', request()->query());
    }
}

