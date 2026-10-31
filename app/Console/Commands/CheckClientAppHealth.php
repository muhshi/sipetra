<?php

namespace App\Console\Commands;

use App\Models\PortalApp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CheckClientAppHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-client-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa status uptime dan latensi semua aplikasi client SSO yang terdaftar di Portal';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apps = PortalApp::where('is_active', true)->get();

        if ($apps->isEmpty()) {
            $this->info('Tidak ada aplikasi aktif untuk diperiksa.');

            return self::SUCCESS;
        }

        $results = [];

        foreach ($apps as $app) {
            $startTime = microtime(true);
            $status = 'offline';
            $httpCode = 0;

            try {
                $response = Http::timeout(2)
                    ->connectTimeout(1)
                    ->withoutVerifying()
                    ->get($app->url);

                $duration = (int) round((microtime(true) - $startTime) * 1000);
                $httpCode = $response->status();

                if ($response->successful() || $response->redirect()) {
                    $status = 'online';
                } else {
                    $status = 'warning';
                }
            } catch (\Throwable $e) {
                $duration = (int) round((microtime(true) - $startTime) * 1000);
                $status = 'offline';
            }

            $healthData = [
                'status' => $status,
                'latency_ms' => $duration,
                'http_code' => $httpCode,
                'checked_at' => now()->toIso8601String(),
            ];

            Cache::put('app_health_'.$app->id, $healthData, now()->addMinutes(5));

            $results[] = [
                'ID' => $app->id,
                'Aplikasi' => $app->name,
                'URL' => $app->url,
                'Status' => $status,
                'Latency' => $duration.' ms',
                'HTTP Code' => $httpCode ?: 'ERR',
            ];
        }

        $this->table(['ID', 'Aplikasi', 'URL', 'Status', 'Latency', 'HTTP Code'], $results);
        $this->info('Pemeriksaan kesehatan aplikasi selesai diperbarui di Cache.');

        return self::SUCCESS;
    }
}
