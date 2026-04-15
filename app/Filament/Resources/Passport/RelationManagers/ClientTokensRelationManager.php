<?php

declare(strict_types=1);

namespace App\Filament\Resources\Passport\RelationManagers;

use App\Filament\Resources\Passport\TokenResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ClientTokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return TokenResource::table($table);
    }
}
