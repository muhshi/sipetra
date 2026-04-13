<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\ClientRole;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientUserAccessesRelationManager extends RelationManager
{
    protected static string $relationship = 'userAccesses';

    protected static ?string $title = 'Manajemen Akses User';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Pilih User / Pegawai')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search) => User::where('is_active', true)
                    ->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->limit(20)
                    ->get()
                    ->mapWithKeys(fn (User $user) => [
                        $user->id => "{$user->name}".($user->nip ? " ({$user->nip})" : ''),
                    ]))
                ->getOptionLabelUsing(fn ($value) => User::find($value)?->name ?? $value)
                ->required(),

            Select::make('client_role_id')
                ->label('Role di Aplikasi Ini')
                ->options(fn () => ClientRole::where('client_id', $this->getOwnerRecord()->id)
                    ->pluck('name', 'id'))
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.nip')
                    ->label('NIP')
                    ->placeholder('—'),

                TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah Role'),
                DeleteAction::make()
                    ->label('Cabut Akses')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah User'),
            ]);
    }
}
