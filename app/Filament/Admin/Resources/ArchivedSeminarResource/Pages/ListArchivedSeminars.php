<?php

namespace App\Filament\Admin\Resources\ArchivedSeminarResource\Pages;

use App\Filament\Admin\Resources\ArchivedSeminarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArchivedSeminars extends ListRecords
{
    protected static string $resource = ArchivedSeminarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
