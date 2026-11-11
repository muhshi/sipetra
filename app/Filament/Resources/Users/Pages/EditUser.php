<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->record->employeeProfile;

        if ($profile) {
            $data['employeeProfile'] = [
                'tmt_cpns' => $profile->tmt_cpns?->format('Y-m-d'),
                'tmt_pns' => $profile->tmt_pns?->format('Y-m-d'),
                'tmt_golongan' => $profile->tmt_golongan?->format('Y-m-d'),
                'tmt_jabatan' => $profile->tmt_jabatan?->format('Y-m-d'),
                'status_pegawai' => $profile->status_pegawai,
                'agama' => $profile->agama,
                'mk_tahun' => $profile->mk_tahun,
                'mk_bulan' => $profile->mk_bulan,
            ];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $employeeData = $this->data['employeeProfile'] ?? null;

        if (is_array($employeeData) && ! empty(array_filter($employeeData, fn ($val) => filled($val)))) {
            $this->record->employeeProfile()->updateOrCreate(
                ['user_id' => $this->record->id],
                [
                    'tmt_cpns' => $employeeData['tmt_cpns'] ?? null,
                    'tmt_pns' => $employeeData['tmt_pns'] ?? null,
                    'tmt_golongan' => $employeeData['tmt_golongan'] ?? null,
                    'tmt_jabatan' => $employeeData['tmt_jabatan'] ?? null,
                    'status_pegawai' => $employeeData['status_pegawai'] ?? null,
                    'agama' => $employeeData['agama'] ?? null,
                    'mk_tahun' => filled($employeeData['mk_tahun'] ?? null) ? (int) $employeeData['mk_tahun'] : 0,
                    'mk_bulan' => filled($employeeData['mk_bulan'] ?? null) ? (int) $employeeData['mk_bulan'] : 0,
                ]
            );
        }
    }
}
