<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLesson extends ViewRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('studentPreview')
                ->label('Student preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('lessons.show', [
                    $this->record->course->slug,
                    $this->record->slug,
                    'preview_as_student' => 1,
                ]))
                ->openUrlInNewTab(),
            Actions\EditAction::make()
                ->url(fn (): string => LessonResource::getUrl('edit', [
                    'record' => $this->record,
                    ...request()->query(),
                ])),
        ];
    }
}

