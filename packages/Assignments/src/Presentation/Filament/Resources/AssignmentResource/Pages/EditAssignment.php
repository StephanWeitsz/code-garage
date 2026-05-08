<?php

namespace CodeGarage\Assignments\Presentation\Filament\Resources\AssignmentResource\Pages;

use CodeGarage\Assignments\Presentation\Filament\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
