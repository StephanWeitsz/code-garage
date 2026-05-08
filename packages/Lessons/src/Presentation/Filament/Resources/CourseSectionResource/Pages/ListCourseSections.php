<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseSections extends ListRecords
{
    protected static string $resource = CourseSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Create section')
                ->icon('heroicon-m-plus')
                ->url(fn (): string => CourseSectionResource::getUrl('create', [
                    'course_id' => $this->selectedCourseId(),
                ])),
        ];
    }

    protected function selectedCourseId(): mixed
    {
        return data_get($this->tableFilters, 'course_scope.course_id')
            ?? data_get($this->tableFilters, 'course_scope.course_id.value')
            ?? data_get($this->tableFilters, 'course_id')
            ?? data_get($this->tableFilters, 'course_id.value');
    }
}
