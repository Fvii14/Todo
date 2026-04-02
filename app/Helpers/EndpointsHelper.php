<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Http;

class EndpointsHelper
{
    public static function downloadFileFromCdn(string $url, string $fileName): ?string
    {
        try {
            $response = Http::timeout(60)
                ->withoutVerifying()
                ->get($url);

            if ($response->failed()) {
                throw new Exception('Error descargando archivo de CDN. Status: '.$response->status());
            }

            return $response->body();

        } catch (Exception $e) {
            throw new Exception('Error en downloadFileFromCdn: '.$e->getMessage());
        }
    }

    public static function decryptWhatsAppFile(string $cdnFile, array $metadata): ?string
    {
        try {
            $encryptionKey = base64_decode($metadata['encryption_key'] ?? '');
            $hmacKey = base64_decode($metadata['hmac_key'] ?? '');
            $iv = base64_decode($metadata['iv'] ?? '');
            $plaintextHash = base64_decode($metadata['plaintext_hash'] ?? '');
            $encryptedHash = base64_decode($metadata['encrypted_hash'] ?? '');

            if (! $encryptionKey || ! $hmacKey || ! $iv || ! $plaintextHash || ! $encryptedHash) {
                throw new Exception('Metadatos de encriptación inválidos o incompletos');
            }

            $cdnFileHash = hash('sha256', $cdnFile, true);
            if (! hash_equals($encryptedHash, $cdnFileHash)) {
                throw new Exception('Validación de hash del archivo CDN fallida - archivo corrupto o alterado');
            }

            if (strlen($cdnFile) < 10) {
                throw new Exception('El archivo CDN es demasiado pequeño');
            }

            $hmac10 = substr($cdnFile, -10);
            $ciphertext = substr($cdnFile, 0, -10);

            $computedHmac = hash_hmac('sha256', $ciphertext, $hmacKey, true);

            $computedHmac10 = substr($computedHmac, 0, 10);
            if (! hash_equals($hmac10, $computedHmac10)) {
                $hmacInput = $iv.$ciphertext;
                $computedHmac = hash_hmac('sha256', $hmacInput, $hmacKey, true);
                $computedHmac10 = substr($computedHmac, 0, 10);

                if (! hash_equals($hmac10, $computedHmac10)) {
                    throw new Exception('Validación HMAC fallida - archivo corrupto o alterado');
                }
            }

            $decrypted = openssl_decrypt(
                $ciphertext,
                'aes-256-cbc',
                $encryptionKey,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                $error = openssl_error_string();
                throw new Exception('Error al desencriptar: '.($error ?: 'Error desconocido de OpenSSL'));
            }

            $decryptedHash = hash('sha256', $decrypted, true);
            if (! hash_equals($plaintextHash, $decryptedHash)) {
                throw new Exception('Validación de hash del contenido descifrado fallida - archivo corrupto o alterado');
            }

            return $decrypted;

        } catch (Exception $e) {
            throw new Exception('Error en decryptWhatsAppFile: '.$e->getMessage());
        }
    }

    public static function detectMimeType(string $content, string $fileName): string
    {
        $fileHeader = substr($content, 0, 12);

        if (strpos($fileHeader, "\xFF\xD8\xFF") === 0) {
            return 'image/jpeg';
        }
        if (strpos($fileHeader, "\x89\x50\x4E\x47") === 0) {
            return 'image/png';
        }
        if (strpos($fileHeader, "\x25\x50\x44\x46") === 0) {
            return 'application/pdf';
        }
        if (strpos($fileHeader, "\x47\x49\x46") === 0) {
            return 'image/gif';
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'gif' => 'image/gif',
        ];

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    public static function validateFileContent(string $content, string $mimeType, string $fileName): void
    {
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (! in_array($ext, $allowedExtensions)) {
            throw new Exception('Extensión de archivo no permitida: '.$ext);
        }

        if (strlen($content) > 52428800) {
            throw new Exception('Archivo demasiado grande');
        }

        if (strlen($content) === 0) {
            throw new Exception('El archivo está vacío');
        }
    }
}
