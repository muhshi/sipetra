<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalAppResource\Pages;
use App\Models\PortalApp;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PortalAppResource extends Resource
{
    protected static ?string $model = PortalApp::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Portal';

    protected static ?string $modelLabel = 'Aplikasi Portal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Aplikasi')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Aplikasi (Singkatan)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('full_name')
                        ->label('Kepanjangan Nama Aplikasi')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('url')
                        ->label('URL / Link Aplikasi')
                        ->required()
                        ->url()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('icon')
                        ->label('Icon Aplikasi')
                        ->readOnly()
                        ->extraInputAttributes([
                            'x-on:click' => '$el.closest(\'[data-field-wrapper]\').querySelector(\'button\').click()',
                            'style' => 'cursor: pointer;',
                        ])
                        ->suffixAction(
                            \Filament\Actions\Action::make('selectIcon')
                                ->icon('heroicon-m-squares-2x2')
                                ->modalHeading('Pilih Icon')
                                ->modalIcon(false)
                                ->modalWidth('4xl')
                                ->modalContent(fn ($component): \Illuminate\Contracts\View\View => view('filament.components.icon-picker-modal', ['statePath' => $component->getStatePath()]))
                                ->modalSubmitAction(false)
                                ->modalCancelAction(false)
                        )
                        ->helperText('Icon ditampilkan di card jika logo tidak diunggah'),
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo Aplikasi (Override icon)')
                        ->image()
                        ->directory('portal-apps')
                        ->visibility('public'),
                ])->columns(2),

                Section::make('Pengaturan')->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                    Forms\Components\TextInput::make('order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aplikasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortalApps::route('/'),
            'create' => Pages\CreatePortalApp::route('/create'),
            'edit' => Pages\EditPortalApp::route('/{record}/edit'),
        ];
    }
}
