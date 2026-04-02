<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class GcsUploaderService
{
    protected $bucket;

    public function __construct()
    {
        $storage = new StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFilePath' => config('filesystems.disks.gcs.key_file'),
        ]);

        $this->bucket = $storage->bucket(config('filesystems.disks.gcs.bucket'));
    }

    public function uploadFile(UploadedFile $file, string $path): string
    {
        try {
            $this->bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $path]
            );

            return $path;
        } catch (\Exception $e) {
            Log::error('Error al subir archivo a GCS: '.$e->getMessage());
            throw $e;
        }
    }

    public function uploadString(string $content, string $path, string $contentType = 'image/png'): string
    {
        $this->bucket->upload($content, [
            'name' => $path,
            'metadata' => ['contentType' => $contentType],
        ]);

        return $path;
    }

    // public function getTemporaryUrl(string $path, int $minutes = 10): string
    // {
    //     $object = $this->bucket->object($path);
    //     // QUITAMOS el exists() para evitar el fields=* que rompe
    //     return $object->signedUrl(
    //         Carbon::now()->addMinutes($minutes),
    //         ['version' => 'v4']
    //     );
    // }

    // use Google\Cloud\Storage\StorageObject;
    public function object(string $path)
    {
        return $this->bucket->object($path);
    }

    public function readStream(string $path)
    {
        return $this->bucket->object($path)->downloadAsStream(); // StreamInterface
    }

    public function getTemporaryUrl(string $path, int $minutes = 10, array $overrides = []): string
    {
        $object = $this->bucket->object($path);

        // Forzamos respuesta como PDF inline para el visor del navegador
        $opts = array_merge([
            'version' => 'v4',
            'responseDisposition' => 'inline',
        ], $overrides);

        if (! isset($overrides['responseType'])) {
            $opts['responseType'] = 'application/pdf';
        }

        return $object->signedUrl(now()->addMinutes($minutes), $opts);
    }

    public function getDownloadUrl(string $path, ?string $filename = null, ?string $mimeType = null, int $minutes = 10): string
    {
        $object = $this->bucket->object($path);

        $options = [
            'version' => 'v4',
            'responseDisposition' => 'attachment'.($filename ? "; filename=\"{$filename}\"" : ''),
        ];

        // Solo agregar responseType si se proporciona un MIME type específico
        if ($mimeType) {
            $options['responseType'] = $mimeType;
        }

        return $object->signedUrl(
            Carbon::now()->addMinutes($minutes),
            $options
        );
    }

    public function delete(string $path): void
    {
        try {
            $object = $this->bucket->object($path);
            $object->delete();
        } catch (\Exception $e) {
            Log::error('Error al eliminar archivo de GCS: '.$e->getMessage());
            throw $e;
        }
    }
}
