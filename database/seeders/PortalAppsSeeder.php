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
            ['name' => 'Alfath', 'url' => 'https://alfath.bpsdemak.com/', 'order' => 1, 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Magang', 'url' => 'http://magang.bpsdemak.com/', 'order' => 2, 'icon' => 'heroicon-o-briefcase'],
            ['name' => 'CKP', 'url' => 'https://ckp.bpsdemak.com/', 'order' => 3, 'icon' => 'heroicon-o-document-chart-bar'],
            ['name' => 'Surat', 'url' => 'https://surat.bpsdemak.com/', 'order' => 4, 'icon' => 'heroicon-o-envelope'],
            ['name' => 'Siputri', 'url' => 'https://siputri.bpsdemak.com/', 'order' => 5, 'icon' => 'heroicon-o-identification'],
            ['name' => 'Demakai', 'url' => 'https://demakai.bpsdemak.com/', 'order' => 6, 'icon' => 'heroicon-o-shopping-bag'],
            ['name' => 'Portal', 'url' => 'https://portal.bpsdemak.com/', 'order' => 7, 'icon' => 'heroicon-o-home'],
            ['name' => 'Dinamit', 'url' => 'https://dinamit.bpsdemak.com/', 'order' => 8, 'icon' => 'heroicon-o-fire'],
        ];

        foreach ($apps as $app) {
            PortalApp::updateOrCreate(
                ['url' => $app['url']],
                [
                    'name' => $app['name'],
                    'order' => $app['order'],
                    'icon' => $app['icon'],
                    'is_active' => true,
                    'description' => 'Sistem Informasi ' . $app['name'] . ' BPS Kabupaten Demak',
                ]
            );
        }
    }
}
