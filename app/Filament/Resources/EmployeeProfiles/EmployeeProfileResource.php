<?php

namespace App\Filament\Resources\EmployeeProfiles;

use App\Filament\Resources\EmployeeProfiles\Pages\CreateEmployeeProfile;
use App\Filament\Resources\EmployeeProfiles\Pages\EditEmployeeProfile;
use App\Filament\Resources\EmployeeProfiles\Pages\ListEmployeeProfiles;
use App\Filament\Resources\EmployeeProfiles\Schemas\EmployeeProfileForm;
use App\Filament\Resources\EmployeeProfiles\Tables\EmployeeProfilesTable;
use App\Models\EmployeeProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeProfileResource extends Resource
{
    protected static ?string $model = EmployeeProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Kelola Pegawai';

    protected static ?string $modelLabel = 'Profil Pegawai';

    protected static ?string $pluralModelLabel = 'Profil Pegawai';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EmployeeProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeProfiles::route('/'),
            'create' => CreateEmployeeProfile::route('/create'),
            'edit' => EditEmployeeProfile::route('/{record}/edit'),
        ];
    }
}
