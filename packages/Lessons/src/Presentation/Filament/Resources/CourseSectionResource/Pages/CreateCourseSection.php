<?php

namespace CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource\Pages;

use CodeGarage\Lessons\Presentation\Filament\Resources\CourseSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseSection extends CreateRecord
{
    protected static string $resource = CourseSectionResource::class;

    public ?string $courseId = null;

    public function mount(): void
    {
        $this->courseId = request()->query('course_id');

        parent::mount();
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        $this->form->fill(array_filter([
            'course_id' => $this->courseId,
        ], fn ($value) => filled($value)));
    }

    protected function getRedirectUrl(): string
    {
        $parameters = [];

        if ($this->courseId) {
            $parameters['tableFilters'] = [
                'course_scope' => array_filter([
                    'course_id' => $this->courseId,
                ], fn ($value) => filled($value)),
            ];
        }

        return CourseSectionResource::getUrl('index', $parameters);
    }
}
