<?php

namespace App\Models;

use App\Models\Passport\Client as BaseClient;

/**
 * Legacy Bridge Model
 * 
 * Class ini dipertahankan sebagai jembatan untuk komponen yang mungkin masih 
 * mereferensikan App\Models\PassportClient secara hardcoded atau via database.
 */
class PassportClient extends BaseClient
{
    /**
     * Pastikan morph class tetap mengarah ke base model jika diperlukan
     */
    public function getMorphClass(): string
    {
        return \N3XT0R\LaravelPassportAuthorizationCore\Models\Passport\Client::class;
    }
}
