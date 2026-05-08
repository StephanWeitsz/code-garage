<?php

namespace CodeGarage\Events\Presentation\Filament\Resources\EventResource\Pages;

use CodeGarage\Events\Presentation\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'closed' && blank($data['closed_at'] ?? null)) {
            $data['closed_at'] = now();
        }

        if (($data['status'] ?? null) !== 'closed') {
            $data['closed_at'] = null;
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
