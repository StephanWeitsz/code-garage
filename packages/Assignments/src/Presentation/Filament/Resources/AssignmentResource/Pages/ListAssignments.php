<?php

namespace CodeGarage\Assignments\Presentation\Filament\Resources\AssignmentResource\Pages;

use CodeGarage\Assignments\Presentation\Filament\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssignments extends ListRecords
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
