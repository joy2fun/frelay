<?php

namespace App\Filament\Resources\EndpointResource\Pages;

use App\Filament\Resources\EndpointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEndpoints extends ListRecords
{
    protected static string $resource = EndpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
