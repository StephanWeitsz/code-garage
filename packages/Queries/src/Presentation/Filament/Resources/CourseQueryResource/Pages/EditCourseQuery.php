<?php

namespace CodeGarage\Queries\Presentation\Filament\Resources\CourseQueryResource\Pages;

use CodeGarage\Queries\Presentation\Filament\Resources\CourseQueryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseQuery extends EditRecord
{
    protected static string $resource = CourseQueryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? null, ['resolved', 'closed'], true) && blank($data['resolved_at'] ?? null)) {
            $data['resolved_at'] = now();
        }

        if (! in_array($data['status'] ?? null, ['resolved', 'closed'], true)) {
            $data['resolved_at'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false),
        ];
    }
}
