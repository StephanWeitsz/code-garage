<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\LessonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    public ?string $courseId = null;

    public ?string $courseSectionId = null;

    public function mount(): void
    {
        $this->courseId = request()->query('course_id');
        $this->courseSectionId = request()->query('course_section_id');

        parent::mount();
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        $this->form->fill(array_filter([
            'course_id' => $this->courseId,
            'course_section_id' => $this->courseSectionId,
        ], fn ($value) => filled($value)));
    }

    protected function getRedirectUrl(): string
    {
        $parameters = [];

        if ($this->courseId || $this->courseSectionId) {
            $parameters['tableFilters'] = [
                'course_outline' => array_filter([
                    'course_id' => $this->courseId,
                    'course_section_id' => $this->courseSectionId,
                ], fn ($value) => filled($value)),
            ];
        }

        return LessonResource::getUrl('index', $parameters);
    }
}

