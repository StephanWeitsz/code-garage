<?php

namespace CodeGarage\Courses\Presentation\Filament\Resources\CourseResource\Pages;

use CodeGarage\Courses\Presentation\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

