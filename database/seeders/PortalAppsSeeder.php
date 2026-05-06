<?php

namespace Database\Seeders;

use App\Models\PortalApp;
use Illuminate\Database\Seeder;

class PortalAppsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            ['name' => 'Alfath', 'url' => 'https://alfath.bpsdemak.com/', 'order' => 1],
            ['name' => 'Magang', 'url' => 'http://magang.bpsdemak.com/', 'order' => 2],
            ['name' => 'CKP', 'url' => 'https://ckp.bpsdemak.com/', 'order' => 3],
            ['name' => 'Surat', 'url' => 'https://surat.bpsdemak.com/', 'order' => 4],
            ['name' => 'Siputri', 'url' => 'https://siputri.bpsdemak.com/', 'order' => 5],
            ['name' => 'Demakai', 'url' => 'https://demakai.bpsdemak.com/', 'order' => 6],
            ['name' => 'Portal', 'url' => 'https://portal.bpsdemak.com/', 'order' => 7],
            ['name' => 'Dinamit', 'url' => 'https://dinamit.bpsdemak.com/', 'order' => 8],
        ];

        foreach ($apps as $app) {
            PortalApp::updateOrCreate(
                ['url' => $app['url']],
                [
                    'name' => $app['name'],
                    'order' => $app['order'],
                    'is_active' => true,
                    'description' => 'Sistem Informasi ' . $app['name'] . ' BPS Kabupaten Demak',
                ]
            );
        }
    }
}
