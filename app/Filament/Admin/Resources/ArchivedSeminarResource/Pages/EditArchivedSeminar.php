<?php

namespace App\Filament\Admin\Resources\ArchivedSeminarResource\Pages;

use App\Filament\Admin\Resources\ArchivedSeminarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArchivedSeminar extends EditRecord
{
    protected static string $resource = ArchivedSeminarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
