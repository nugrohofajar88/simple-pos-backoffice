<?php

namespace App\Providers;

use App\Support\Contracts\WhatsappGateway;
use App\Support\FonnteService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Notifikasi WA pesanan tamu (masuk -> admin, dikonfirmasi -> customer).
        $this->app->bind(WhatsappGateway::class, FonnteService::class);

        // Driver penyimpanan Google Drive — dipakai disk 'gdrive' untuk backup off-site.
        // Lazy: hanya diinstansiasi saat disk 'gdrive' benar-benar dipakai.
        Storage::extend('google', function ($app, array $config) {
            $client = new \Google\Client();
            $client->setClientId($config['clientId'] ?? '');
            $client->setClientSecret($config['clientSecret'] ?? '');
            $client->refreshToken($config['refreshToken'] ?? '');

            $service = new \Google\Service\Drive($client);
            $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/');
            $driver = new \League\Flysystem\Filesystem($adapter);

            return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter, $config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
