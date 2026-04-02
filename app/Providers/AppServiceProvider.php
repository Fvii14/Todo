<?php

namespace App\Providers;

use App\Models\Contratacion;
use App\Models\Conviviente;
use App\Models\User;
use App\Models\UserDocument;
use App\Observers\ContratacionObserver;
use App\Observers\ConvivienteObserver;
use App\Observers\StatusUpdateObserver;
use App\Observers\UserDocumentObserver;
use App\Observers\UserObserver;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar EventHandlerService como singleton para evitar múltiples instancias
        // y asegurar que la protección contra duplicados funcione correctamente
        $this->app->singleton(EventHandlerService::class, function ($app) {
            return new EventHandlerService;
        });
    }

    public function boot()
    {
        User::observe(UserObserver::class);
        if (app()->environment('production') || app()->environment('staging')) {
            URL::forceScheme('https');
        }
        Contratacion::observe(StatusUpdateObserver::class);
        Contratacion::observe(ContratacionObserver::class);

        Storage::extend('gcs', function ($app, $config) {
            $client = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);

            $bucket = $client->bucket($config['bucket']);

            $adapter = new GoogleCloudStorageAdapter(
                $bucket,
                $config['path_prefix'] ?? ''
            );

            return $app['filesystem']->createCustomDriver([
                'driver' => 'gcs',
                'filesystem' => new Filesystem($adapter),
                'config' => $config,
            ]);
        });

        UserDocument::observe(UserDocumentObserver::class);
        Conviviente::observe(ConvivienteObserver::class);
    }
}
