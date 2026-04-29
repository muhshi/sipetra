<?php

namespace App\Filament\Resources\EmployeeProfiles\Pages;

use App\Filament\Resources\EmployeeProfiles\EmployeeProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeProfile extends EditRecord
{
    protected static string $resource = EmployeeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
