<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessons extends ListRecords
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Create lesson')
                ->icon('heroicon-m-plus')
                ->url(fn (): string => LessonResource::getUrl('create', [
                    'course_id' => $this->selectedCourseId(),
                    'course_section_id' => $this->selectedSectionId(),
                ])),
        ];
    }

    protected function selectedCourseId(): mixed
    {
        return data_get($this->tableFilters, 'course_outline.course_id')
            ?? data_get($this->tableFilters, 'course_outline.course_id.value')
            ?? data_get($this->tableFilters, 'course_id')
            ?? data_get($this->tableFilters, 'course_id.value');
    }

    protected function selectedSectionId(): mixed
    {
        return data_get($this->tableFilters, 'course_outline.course_section_id')
            ?? data_get($this->tableFilters, 'course_outline.course_section_id.value')
            ?? data_get($this->tableFilters, 'course_section_id')
            ?? data_get($this->tableFilters, 'course_section_id.value');
    }
}

