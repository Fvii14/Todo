<?php

namespace App\Models;

use App\Services\GcsUploaderService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'file_path',
        'file_name',
        'file_type',
        'size',
        'nombre_personalizado',
        'estado',
        'nota_rechazo',
        'slug',
        'conviviente_index',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conviviente()
    {
        // Asegúrate de que la FK es 'conviviente_id' y el modelo correcto.
        return $this->belongsTo(Conviviente::class, 'conviviente_id');
    }

    public function convivienteByIndex()
    {
        return $this->belongsTo(Conviviente::class, 'conviviente_index', 'index')
            ->where('user_id', $this->user_id);
    }

    public function getConvivienteIndexAttribute(): ?int
    {
        return $this->attributes['conviviente_index'];
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    protected $appends = ['temporary_url', 'download_url'];

    // app/Models/UserDocument.php
    public function getTemporaryUrlAttribute(): ?string
    {
        $path = $this->path ?? $this->file_path ?? null;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        try {
            $mimeType = $this->file_type ?? 'application/pdf';
            $overrides = ['responseType' => $mimeType];

            $url = app(\App\Services\GcsUploaderService::class)->getTemporaryUrl($path, 60, $overrides);
            $clean = preg_replace('/([&?])fields=[^&]+(&?)/', '$1', $url);

            return rtrim($clean, '?&');
        } catch (\Throwable $e) {
            Log::warning('No se pudo generar temporary_url', [
                'user_document_id' => $this->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->slug === 'justificante-presentacion-ayuda') {
            $pathInfo = pathinfo($this->file_name);
            $extension = $pathInfo['extension'] ?? '';
            $filename = 'Justificante_Presentacion_Ayuda'.($extension ? '.'.$extension : '');
        } else {
            $filename = $this->nombre_personalizado ?: $this->file_name;

            $pathInfo = pathinfo($this->file_name);
            $extension = $pathInfo['extension'] ?? '';

            if ($extension && ! str_ends_with(strtolower($filename), '.'.strtolower($extension))) {
                $filename = $filename.'.'.$extension;
            }
        }

        if (! $this->file_path) {
            return null;
        }

        $url = (new GcsUploaderService)
            ->getDownloadUrl($this->file_path, $filename, $this->file_type, 60);

        if (! is_string($url)) {
            return null;
        }

        $clean = preg_replace('/([&?])fields=[^&]+(&?)/', '$1', $url);
        $clean = rtrim($clean, '?&');

        return $clean;
    }
}
