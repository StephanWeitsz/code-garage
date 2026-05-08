<?php

namespace CodeGarage\Courses\Presentation\Filament\Resources\CourseResource\Pages;

use CodeGarage\Courses\Presentation\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}

