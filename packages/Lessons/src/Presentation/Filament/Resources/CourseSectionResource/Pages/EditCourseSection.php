<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseSection extends EditRecord
{
    protected static string $resource = CourseSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? CourseSectionResource::getUrl('index', request()->query());
    }
}
