<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\IdentityType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=FFFFFF&background=030712'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('identity_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sobat_id')
                    ->label('Sobat ID')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('kd_satker')
                    ->label('Satker')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('employeeProfile.mk_tahun')
                    ->label('Masa Kerja')
                    ->state(fn ($record) => $record->masa_kerja)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('identity_type')
                    ->label('Tipe Identitas')
                    ->options(IdentityType::class),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('revoke_sessions')
                    ->label('Putus Sesi')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Putus Semua Sesi Pengguna')
                    ->modalDescription(fn (User $record): string => "Apakah Anda yakin ingin memutuskan seluruh sesi aktif dan mencabut token OAuth milik {$record->name}? Pengguna akan langsung ter-logout dari semua aplikasi SSO.")
                    ->modalSubmitActionLabel('Ya, Putus Semua Sesi')
                    ->action(function (User $record): void {
                        // 1. Hapus sesi aktif di tabel sessions
                        DB::table('sessions')->where('user_id', $record->id)->delete();

                        // 2. Cabut semua token akses di tabel oauth_access_tokens
                        $tokenIds = DB::table('oauth_access_tokens')->where('user_id', $record->id)->pluck('id');

                        if ($tokenIds->isNotEmpty()) {
                            DB::table('oauth_access_tokens')->whereIn('id', $tokenIds)->update(['revoked' => true]);
                            DB::table('oauth_refresh_tokens')->whereIn('access_token_id', $tokenIds)->update(['revoked' => true]);
                        }

                        Notification::make()
                            ->title('Sesi Berhasil Diputus')
                            ->body("Seluruh sesi dan token aktif untuk {$record->name} telah dicabut.")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignRole')
                        ->label('Assign Roles')
                        ->icon('heroicon-o-shield-check')
                        ->form([
                            Select::make('roles')
                                ->label('Roles')
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->preload()
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each(function ($record) use ($data) {
                            $record->roles()->sync($data['roles']);
                        }))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Roles assigned successfully'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
