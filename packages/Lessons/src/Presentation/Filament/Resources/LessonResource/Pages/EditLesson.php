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
            Actions\Action::make('studentPreview')
                ->label('Student preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('lessons.show', [
                    $this->record->course->slug,
                    $this->record->slug,
                    'preview_as_student' => 1,
                ]))
                ->openUrlInNewTab(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? LessonResource::getUrl('index', request()->query());
    }
}

