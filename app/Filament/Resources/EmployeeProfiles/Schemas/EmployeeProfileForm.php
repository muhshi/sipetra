<?php

namespace App\Filament\Resources\EmployeeProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pegawai')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Pilih User')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabledOn('edit'),
                                TextInput::make('status_pegawai')
                                    ->label('Status Pegawai')
                                    ->placeholder('PNS, PPPK, dll'),
                            ]),
                    ]),

                Section::make('Detil Kepegawaian')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tmt_cpns')
                                    ->label('TMT CPNS')
                                    ->type('date'),
                                TextInput::make('tmt_pns')
                                    ->label('TMT PNS')
                                    ->type('date'),
                                TextInput::make('tmt_golongan')
                                    ->label('TMT Golongan')
                                    ->type('date'),
                                TextInput::make('tmt_jabatan')
                                    ->label('TMT Jabatan')
                                    ->type('date'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kd_gol')
                                    ->label('Kode Golongan'),
                                TextInput::make('kd_jab')
                                    ->label('Kode Jabatan'),
                                TextInput::make('mk_tahun')
                                    ->label('Masa Kerja (Tahun)')
                                    ->numeric(),
                                TextInput::make('mk_bulan')
                                    ->label('Masa Kerja (Bulan)')
                                    ->numeric(),
                            ]),
                    ]),

                Section::make('Data Pribadi & Pendidikan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('agama')
                                    ->label('Agama'),
                                TextInput::make('no_ijazah')
                                    ->label('Nomor Ijazah'),
                                TextInput::make('tgl_ijazah')
                                    ->label('Tanggal Ijazah')
                                    ->type('date'),
                            ]),
                    ]),
            ]);
    }
}
