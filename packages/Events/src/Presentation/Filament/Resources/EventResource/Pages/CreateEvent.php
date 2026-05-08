<?php

namespace CodeGarage\Events\Presentation\Filament\Resources\EventResource\Pages;

use CodeGarage\Events\Presentation\Filament\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;
}
