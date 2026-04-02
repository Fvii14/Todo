<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class GcsSignedUrlService
{
    private StorageClient $client;

    private string $bucket;

    public function __construct()
    {
        // --- 1) Cargar config y decidir ADC vs keyFile ---
        $projectId = env('GOOGLE_CLOUD_PROJECT_ID');
        $bucketEnv = env('GOOGLE_CLOUD_STORAGE_BUCKET'); // tu var “de siempre”
        $keyRel = env('GOOGLE_CLOUD_KEY_FILE');       // SOLO si en local usas key file

        $config = ['projectId' => $projectId];

        // Si tienes ruta de key en local y el archivo existe, úsala. Si no, ADC.
        if ($keyRel) {
            $abs = storage_path($keyRel);
            $exists = file_exists($abs);

            if ($exists) {
                $config['keyFilePath'] = $abs;
            } else {
                Log::warning('[GCS] keyFilePath not found, falling back to ADC');
            }
        }

        try {
            $this->client = new StorageClient($config);
            Log::info('[GCS] StorageClient created OK');
        } catch (Throwable $e) {
            Log::error('[GCS] StorageClient init FAILED', [
                'msg' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            throw $e;
        }

        $this->bucket = (string) $bucketEnv;

        if (! $this->bucket) {
            Log::error('[GCS] GOOGLE_CLOUD_STORAGE_BUCKET is empty');
            abort(500, 'Bucket no configurado (GOOGLE_CLOUD_STORAGE_BUCKET).');
        }
    }

    /** Genera URL firmada V4 para descarga con logs paso a paso */
    public function makeDownloadUrl(string $objectPath, int $minutes = 60, string $downloadAs = 'documento.pdf'): string
    {

        try {
            $bucket = $this->client->bucket($this->bucket);
            if (! $bucket) {
                Log::error('[GCS] bucket() devolvió null', ['bucket' => $this->bucket]);
                abort(500, 'No se pudo acceder al bucket.');
            }

            $object = $bucket->object($objectPath);
            $exists = $object->exists();

            if (! $exists) {
                abort(404, 'Archivo no disponible.');
            }

            $expiresAt = now()->addMinutes($minutes);

            $url = $object->signedUrl(
                $expiresAt,
                [
                    'version' => 'v4',
                    'method' => 'GET',
                    'responseDisposition' => 'attachment; filename="'.addslashes($downloadAs).'"',
                    'responseType' => 'application/pdf',
                ]
            );

            return $url;

        } catch (Throwable $e) {
            Log::error('[GCS] makeDownloadUrl FAILED', [
                'bucket' => $this->bucket,
                'objectPath' => $objectPath,
                'msg' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            throw $e;
        }
    }
}
