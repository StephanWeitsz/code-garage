<?php

namespace CodeGarage\DevelopmentRequests\Presentation\Filament\Resources\DevelopmentRequestResource\Pages;

use CodeGarage\DevelopmentRequests\Presentation\Filament\Resources\DevelopmentRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDevelopmentRequest extends EditRecord
{
    protected static string $resource = DevelopmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
