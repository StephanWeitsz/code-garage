<?php

namespace CodeGarage\Courses\Presentation\Filament\Resources\CourseResource\Pages;

use CodeGarage\Courses\Presentation\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $removeCoverImage = (bool) ($data['remove_cover_image'] ?? false);

        if ($removeCoverImage) {
            $existingPath = (string) ($this->record->cover_image ?? '');

            if ($existingPath !== '' && Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['cover_image'] = null;
        }

        if (filled($data['cover_image_upload'] ?? null)) {
            $existingPath = (string) ($this->record->cover_image ?? '');

            if ($existingPath !== '' && Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['cover_image'] = $data['cover_image_upload'];
        }

        unset($data['cover_image_upload']);
        unset($data['remove_cover_image']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

