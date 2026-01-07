<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Storage;
use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') != 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }

        Storage::extend('gcs', function ($app, $config) {
            $storageClient = new StorageClient([
                'projectId' => $config['project_id'] ?? $config['key_file']['project_id'] ?? null,
                'keyFile' => $config['key_file'],
            ]);
            $bucket = $storageClient->bucket($config['bucket']);
            $bucketPrefix = $config['path_prefix'] ?? ''; // Support path_prefix if user adds it
            $adapter = new GoogleCloudStorageAdapter($bucket, $bucketPrefix);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
