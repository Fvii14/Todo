<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use iio\libmergepdf\Driver\TcpdiDriver;
use iio\libmergepdf\Merger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MultiFileProcessorService
{
    protected $gcsUploader;

    protected $tempPath;

    public function __construct(GcsUploaderService $gcsUploader)
    {
        $this->gcsUploader = $gcsUploader;
        $this->tempPath = sys_get_temp_dir();

    }

    /**
     * Procesa múltiples archivos y los convierte a un único PDF
     */
    public function processMultipleFiles(array $files, int $userId, string $documentSlug): array
    {
        $processedFiles = [];
        $tempFiles = [];
        $pdfFiles = [];

        \Log::info('Iniciando procesamiento de múltiples archivos', [
            'userId' => $userId,
            'documentSlug' => $documentSlug,
            'fileCount' => count($files),
        ]);

        try {
            \Log::info('Archivos recibidos', [
                'count' => count($files),
                'fileNames' => array_map(fn ($f) => $f->getClientOriginalName(), $files),
            ]);

            // Si solo hay un archivo, procesarlo directamente
            if (count($files) === 1) {
                $file = $files[0];
                $mimeType = $file->getMimeType();
                $isImage = $this->isImage($file);
                $isPdf = $this->isPdf($file);

                \Log::info('Procesando archivo único', [
                    'fileName' => $file->getClientOriginalName(),
                    'mimeType' => $mimeType,
                    'isImage' => $isImage,
                    'isPdf' => $isPdf,
                ]);

                if ($isImage) {
                    $tempFile = $this->saveTempFile($file);
                    $tempFiles[] = $tempFile;
                    $pdfFile = $this->convertImageToPdf($tempFile, $file->getClientOriginalName());
                    $pdfFiles[] = $pdfFile;
                } elseif ($isPdf) {
                    $tempFile = $this->saveTempFile($file);
                    $tempFiles[] = $tempFile;
                    $pdfFile = $this->copyPdfFile($tempFile, $file->getClientOriginalName());
                    $pdfFiles[] = $pdfFile;
                } else {
                    throw new Exception('Tipo de archivo no soportado: '.$mimeType);
                }
            } else {
                // Procesar múltiples archivos
                foreach ($files as $index => $file) {
                    $mimeType = $file->getMimeType();
                    $isImage = $this->isImage($file);
                    $isPdf = $this->isPdf($file);

                    \Log::info("Procesando archivo {$index}", [
                        'fileName' => $file->getClientOriginalName(),
                        'mimeType' => $mimeType,
                        'isImage' => $isImage,
                        'isPdf' => $isPdf,
                    ]);

                    $tempFile = $this->saveTempFile($file);
                    $tempFiles[] = $tempFile;

                    if ($isImage) {
                        $pdfFile = $this->convertImageToPdf($tempFile, $file->getClientOriginalName());
                        $pdfFiles[] = $pdfFile;
                        $processedFiles[] = [
                            'original' => $file,
                            'temp' => $tempFile,
                            'pdf' => $pdfFile,
                            'type' => 'image',
                        ];
                    } elseif ($isPdf) {
                        $pdfFile = $this->copyPdfFile($tempFile, $file->getClientOriginalName());
                        $pdfFiles[] = $pdfFile;
                        $processedFiles[] = [
                            'original' => $file,
                            'temp' => $tempFile,
                            'pdf' => $pdfFile,
                            'type' => 'pdf',
                        ];
                    } else {
                        throw new Exception('Tipo de archivo no soportado: '.$mimeType);
                    }
                }
            }

            // Si solo hay un PDF, no necesitamos fusionar
            if (count($pdfFiles) === 1) {
                $finalPdfPath = $this->uploadFinalPdf($pdfFiles[0], $userId, $documentSlug);
            } else {
                $mergedPdfPath = $this->mergePdfs($pdfFiles, $userId, $documentSlug);
                $finalPdfPath = $this->uploadFinalPdf($mergedPdfPath, $userId, $documentSlug);
                $tempFiles[] = $mergedPdfPath;
            }

            $this->cleanupTempFiles(array_merge($tempFiles, $pdfFiles));

            return [
                'success' => true,
                'final_pdf_path' => $finalPdfPath,
                'processed_files' => $processedFiles,
            ];

        } catch (Exception $e) {
            \Log::error('Error procesando múltiples archivos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->cleanupTempFiles(array_merge($tempFiles, $pdfFiles));
            throw $e;
        }
    }

    /**
     * Guarda un archivo temporalmente
     */
    protected function saveTempFile(UploadedFile $file): string
    {
        try {
            $tempFileName = Str::uuid().'_'.$file->getClientOriginalName();

            // Usar el directorio temporal del sistema en lugar de storage
            $tempDir = sys_get_temp_dir();

            \Log::info('Guardando archivo temporal', [
                'fileName' => $file->getClientOriginalName(),
                'tempDir' => $tempDir,
                'fileSize' => $file->getSize(),
                'filePath' => $file->getPathname(),
                'isValid' => $file->isValid(),
            ]);

            // Verificar que el archivo es válido antes de procesarlo
            if (! $file->isValid()) {
                throw new \Exception('El archivo no es válido: '.$file->getError());
            }

            // Verificar que el archivo temporal existe y es legible
            if (! file_exists($file->getPathname())) {
                throw new \Exception('El archivo temporal no existe: '.$file->getPathname());
            }

            if (! is_readable($file->getPathname())) {
                throw new \Exception('El archivo temporal no es legible: '.$file->getPathname());
            }

            $fullPath = $tempDir.DIRECTORY_SEPARATOR.$tempFileName;

            // Copiar el archivo en lugar de moverlo para evitar problemas
            if (! copy($file->getPathname(), $fullPath)) {
                throw new \Exception("No se pudo copiar el archivo temporal: {$fullPath}");
            }

            if (! file_exists($fullPath)) {
                throw new \Exception("El archivo copiado no existe: {$fullPath}");
            }

            if (filesize($fullPath) === 0) {
                throw new \Exception("El archivo copiado está vacío: {$fullPath}");
            }

            \Log::info('Archivo temporal guardado exitosamente', [
                'originalPath' => $file->getPathname(),
                'copiedPath' => $fullPath,
                'size' => filesize($fullPath),
            ]);

            return $fullPath;

        } catch (\Exception $e) {
            \Log::error('Error guardando archivo temporal', [
                'fileName' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'filePath' => $file->getPathname(),
                'isValid' => $file->isValid(),
            ]);
            throw $e;
        }
    }

    /**
     * Comprime una imagen para reducir el uso de memoria
     */
    protected function compressImage(string $imagePath): string
    {
        try {
            $imageInfo = getimagesize($imagePath);
            if (! $imageInfo) {
                \Log::warning('No se pudo obtener información de la imagen, usando original', ['path' => $imagePath]);

                return $imagePath;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            if ($width > 2000 || $height > 2000) {
                \Log::info('Comprimiendo imagen grande', [
                    'original' => "{$width}x{$height}",
                    'path' => $imagePath,
                ]);

                switch ($mimeType) {
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($imagePath);
                        break;
                    case 'image/png':
                        $image = imagecreatefrompng($imagePath);
                        break;
                    case 'image/gif':
                        $image = imagecreatefromgif($imagePath);
                        break;
                    default:
                        \Log::warning('Tipo de imagen no soportado para compresión', ['mime' => $mimeType]);

                        return $imagePath;
                }

                if (! $image) {
                    \Log::warning('No se pudo crear imagen para compresión, usando original');

                    return $imagePath;
                }

                $maxDimension = 2000;
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = intval(($height * $maxDimension) / $width);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = intval(($width * $maxDimension) / $height);
                }

                $newImage = imagecreatetruecolor($newWidth, $newHeight);

                if ($mimeType === 'image/png') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                $compressedPath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_compressed_'.basename($imagePath);

                switch ($mimeType) {
                    case 'image/jpeg':
                        imagejpeg($newImage, $compressedPath, 85); // Calidad 85%
                        break;
                    case 'image/png':
                        imagepng($newImage, $compressedPath, 6); // Compresión 6 (0-9)
                        break;
                    case 'image/gif':
                        imagegif($newImage, $compressedPath);
                        break;
                }

                imagedestroy($image);
                imagedestroy($newImage);

                return $compressedPath;
            }

            return $imagePath;

        } catch (\Exception $e) {
            \Log::warning('Error comprimiendo imagen, usando original', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);

            return $imagePath;
        }
    }

    /**
     * Convierte imagen a JPG con tamaño natural
     */
    protected function convertToNaturalJpg(string $imagePath): string
    {
        try {
            $imageInfo = getimagesize($imagePath);
            if (! $imageInfo) {
                throw new Exception('No se pudo obtener información de la imagen');
            }

            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            if ($originalWidth <= 0 || $originalHeight <= 0) {
                throw new Exception("Dimensiones de imagen inválidas: {$originalWidth}x{$originalHeight}");
            }

            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($imagePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($imagePath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($imagePath);
                    break;
                default:
                    throw new Exception("Tipo de imagen no soportado: {$mimeType}");
            }

            if (! $image) {
                throw new Exception('No se pudo crear imagen desde archivo');
            }

            if (imagesx($image) <= 0 || imagesy($image) <= 0) {
                throw new Exception('Imagen creada con dimensiones inválidas');
            }

            $targetImage = imagecreatetruecolor($originalWidth, $originalHeight);

            if (! $targetImage) {
                throw new Exception('No se pudo crear imagen de destino');
            }

            if ($mimeType === 'image/png') {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefill($targetImage, 0, 0, $transparent);
            } else {
                // Fondo blanco para otros formatos
                $white = imagecolorallocate($targetImage, 255, 255, 255);
                imagefill($targetImage, 0, 0, $white);
            }

            imagecopy($targetImage, $image, 0, 0, 0, 0, $originalWidth, $originalHeight);

            $processedImagePath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_NATURAL_'.pathinfo($imagePath, PATHINFO_FILENAME).'.jpg';

            imagejpeg($targetImage, $processedImagePath, 95); // Calidad 95%

            imagedestroy($image);
            imagedestroy($targetImage);

            return $processedImagePath;

        } catch (\Exception $e) {
            \Log::error('Error convirtiendo imagen a A4 JPG', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calcula dimensiones y posición para ajuste A4
     */
    protected function calculateA4Fit(int $originalWidth, int $originalHeight, int $a4Width, int $a4Height, string $fill, int $autoThreshold, string $focus): array
    {
        $originalRatio = $originalWidth / $originalHeight;
        $a4Ratio = $a4Width / $a4Height;

        if ($fill === 'auto') {
            $fill = $this->determineAutoFill($originalWidth, $originalHeight, $a4Width, $a4Height, $autoThreshold);
        }

        if ($fill === 'contain') {
            if ($originalRatio > $a4Ratio) {
                $newWidth = $a4Width;
                $newHeight = intval($a4Width / $originalRatio);
                $x = 0;
                $y = intval(($a4Height - $newHeight) / 2);
            } else {
                $newHeight = $a4Height;
                $newWidth = intval($a4Height * $originalRatio);
                $x = intval(($a4Width - $newWidth) / 2);
                $y = 0;
            }
        } else {
            if ($originalRatio > $a4Ratio) {
                $newHeight = $a4Height;
                $newWidth = intval($a4Height * $originalRatio);
                $x = $this->calculateFocusX($focus, $newWidth, $a4Width);
                $y = 0;
            } else {
                $newWidth = $a4Width;
                $newHeight = intval($a4Width / $originalRatio);
                $x = 0;
                $y = $this->calculateFocusY($focus, $newHeight, $a4Height);
            }
        }

        return [$newWidth, $newHeight, $x, $y];
    }

    /**
     * Determina automáticamente si usar cover o contain
     */
    protected function determineAutoFill(int $originalWidth, int $originalHeight, int $a4Width, int $a4Height, int $autoThreshold): string
    {
        $originalRatio = $originalWidth / $originalHeight;
        $a4Ratio = $a4Width / $a4Height;

        if ($originalRatio > $a4Ratio) {
            $cropPercentage = (($originalHeight * $a4Ratio) / $originalHeight) * 100;
        } else {
            $cropPercentage = (($originalWidth / $a4Ratio) / $originalWidth) * 100;
        }

        $cropPercentage = 100 - $cropPercentage;

        return $cropPercentage <= $autoThreshold ? 'cover' : 'contain';
    }

    /**
     * Convierte una imagen a PDF usando Dompdf simplificado
     */
    protected function convertImageToPdf(string $imagePath, string $originalName): string
    {
        try {
            \Log::info('Convirtiendo imagen a PDF', [
                'imagePath' => $imagePath,
                'originalName' => $originalName,
            ]);

            // Verificar que la imagen existe
            if (! file_exists($imagePath)) {
                throw new Exception("La imagen no existe: {$imagePath}");
            }

            // Obtener información de la imagen
            $imageInfo = getimagesize($imagePath);
            if (! $imageInfo) {
                throw new Exception('No se pudo obtener información de la imagen');
            }

            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];

            \Log::info('Dimensiones de imagen', [
                'width' => $imageWidth,
                'height' => $imageHeight,
            ]);

            // Configurar Dompdf con opciones básicas
            $options = new Options;
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('dpi', 72);

            $dompdf = new Dompdf($options);

            // Usar tamaño A4 por defecto para evitar problemas
            $dompdf->setPaper('A4', 'portrait');

            // Crear HTML simple
            $html = $this->createSimpleImageHtml($imagePath, $originalName);

            $dompdf->loadHtml($html);

            try {
                $dompdf->render();
            } catch (Exception $e) {
                \Log::error('Error en Dompdf render', [
                    'error' => $e->getMessage(),
                    'imagePath' => $imagePath,
                ]);
                throw new Exception('Error generando PDF: '.$e->getMessage());
            }

            $pdfPath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_'.pathinfo($originalName, PATHINFO_FILENAME).'.pdf';
            file_put_contents($pdfPath, $dompdf->output());

            \Log::info('PDF generado exitosamente', [
                'pdfPath' => $pdfPath,
                'size' => filesize($pdfPath),
            ]);

            return $pdfPath;

        } catch (Exception $e) {
            \Log::error('Error convirtiendo imagen a PDF', [
                'imagePath' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Configura el tamaño de papel personalizado basado en las dimensiones de la imagen
     */
    protected function setCustomPaperSize($dompdf, int $imageWidth, int $imageHeight): void
    {
        try {
            // Convertir píxeles a puntos (96 DPI estándar, 72 puntos por pulgada)
            $dpi = 96;
            $widthPoints = ($imageWidth / $dpi) * 72;
            $heightPoints = ($imageHeight / $dpi) * 72;

            \Log::info('Configurando tamaño de papel personalizado', [
                'imagePixels' => "{$imageWidth}x{$imageHeight}",
                'imagePoints' => "{$widthPoints}x{$heightPoints}",
                'dpi' => $dpi,
            ]);

            // Formato correcto para setPaper: [x, y, width, height] en puntos
            $customSize = [0, 0, $widthPoints, $heightPoints];

            $dompdf->setPaper($customSize);

            \Log::info('Tamaño de papel personalizado configurado exitosamente', [
                'customSize' => $customSize,
                'paperDimensions' => "{$widthPoints}x{$heightPoints} puntos",
            ]);

        } catch (Exception $e) {
            \Log::warning('Error configurando tamaño personalizado, usando A4 por defecto', [
                'error' => $e->getMessage(),
                'imageDimensions' => "{$imageWidth}x{$imageHeight}",
            ]);

            // Fallback a A4 si hay error
            $isLandscape = $imageWidth > $imageHeight;
            $dompdf->setPaper('A4', $isLandscape ? 'landscape' : 'portrait');
        }
    }

    /**
     * Crea HTML con tamaño personalizado para que el PDF coincida exactamente con la imagen
     */
    protected function createImageHtmlWithCustomSize(string $imagePath, string $originalName, int $imageWidth, int $imageHeight): string
    {
        if (! file_exists($imagePath)) {
            throw new Exception("La imagen no existe: {$imagePath}");
        }

        // Leer imagen en chunks para evitar cargar todo en memoria
        $imageData = $this->readImageInChunks($imagePath);
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        \Log::info('Creando HTML con tamaño personalizado', [
            'imagePath' => $imagePath,
            'mimeType' => $mimeType,
            'imageSize' => $this->formatBytes(filesize($imagePath)),
            'base64Length' => $this->formatBytes(strlen($imageData)),
            'dimensions' => "{$imageWidth}x{$imageHeight}",
        ]);

        // HTML mínimo absoluto - solo la imagen
        return "<img src='data:{$mimeType};base64,{$imageData}' style='width:{$imageWidth}px;height:{$imageHeight}px;margin:0;padding:0;display:block;'>";
    }

    /**
     * Escala la imagen a un tamaño máximo para reducir el uso de memoria en Dompdf
     */
    protected function scaleImageForPdf(string $imagePath): string
    {
        try {
            $imageInfo = getimagesize($imagePath);
            if (! $imageInfo) {
                throw new Exception('No se pudo obtener información de la imagen');
            }

            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // Tamaño máximo para PDF (equivalente a A4 a 150 DPI)
            $maxWidth = 1240;  // 210mm * 150 DPI / 25.4
            $maxHeight = 1754; // 297mm * 150 DPI / 25.4

            // Si la imagen ya es pequeña, no escalar
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                return $imagePath;
            }

            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);

            \Log::info('Escalando imagen para PDF', [
                'original' => "{$originalWidth}x{$originalHeight}",
                'escalada' => "{$newWidth}x{$newHeight}",
                'ratio' => round($ratio, 3),
            ]);

            // Crear imagen escalada
            $image = null;
            $scaledImage = null;

            try {
                switch ($mimeType) {
                    case 'image/jpeg':
                        $image = @imagecreatefromjpeg($imagePath);
                        break;
                    case 'image/png':
                        $image = @imagecreatefrompng($imagePath);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($imagePath);
                        break;
                    default:
                        throw new Exception("Tipo de imagen no soportado: {$mimeType}");
                }

                if (! $image) {
                    throw new Exception('No se pudo crear imagen desde archivo');
                }

                $scaledImage = imagecreatetruecolor($newWidth, $newHeight);
                if (! $scaledImage) {
                    throw new Exception('No se pudo crear imagen escalada');
                }

                // Preservar transparencia para PNG
                if ($mimeType === 'image/png') {
                    imagealphablending($scaledImage, false);
                    imagesavealpha($scaledImage, true);
                    $transparent = imagecolorallocatealpha($scaledImage, 255, 255, 255, 127);
                    imagefill($scaledImage, 0, 0, $transparent);
                }

                // Escalar la imagen
                imagecopyresampled($scaledImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

                // Guardar imagen escalada
                $scaledImagePath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_scaled_'.pathinfo($imagePath, PATHINFO_FILENAME).'.jpg';

                imagejpeg($scaledImage, $scaledImagePath, 90); // Calidad 90%

                return $scaledImagePath;

            } finally {
                // Liberar memoria inmediatamente
                if ($image) {
                    imagedestroy($image);
                    $image = null;
                }
                if ($scaledImage) {
                    imagedestroy($scaledImage);
                    $scaledImage = null;
                }

                // Forzar liberación de memoria
                $this->forceGarbageCollection();
            }

        } catch (\Exception $e) {
            \Log::warning('Error escalando imagen, usando original', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);

            return $imagePath; // En caso de error, usar imagen original
        }
    }

    /**
     * Crea HTML para convertir imagen a PDF
     */
    protected function createImageHtml(string $imagePath, string $originalName): string
    {
        if (! file_exists($imagePath)) {
            throw new Exception("La imagen no existe: {$imagePath}");
        }

        // Usar base64 pero con imagen escalada para reducir memoria
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$originalName}</title>
            <style>
                * {
                    margin: 0 !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                }
                
                html, body { 
                    margin: 0 !important; 
                    padding: 0 !important; 
                    font-family: Arial, sans-serif; 
                    width: 100% !important; 
                    height: 100% !important; 
                    overflow: hidden !important;
                }
                
                .image-container { 
                    width: 210mm; /* Ancho A4 */
                    height: 297mm; /* Alto A4 */
                    display: flex !important; 
                    align-items: center !important; 
                    justify-content: center !important; 
                    background: white !important; 
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                .image-container img { 
                    max-width: 100% !important; 
                    max-height: 100% !important; 
                    width: auto !important; 
                    height: auto !important; 
                    object-fit: contain !important; 
                    display: block !important; 
                    margin: 0 !important; 
                    padding: 0 !important;
                }
            </style>
        </head>
        <body>
            <div class='image-container'>
                <img src='data:{$mimeType};base64,{$imageData}' alt='{$originalName}'>
            </div>
        </body>
        </html>";
    }

    /**
     * Copia un archivo PDF existente
     */
    protected function copyPdfFile(string $tempPath, string $originalName): string
    {
        $pdfPath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_'.pathinfo($originalName, PATHINFO_FILENAME).'.pdf';

        if (! copy($tempPath, $pdfPath)) {
            throw new Exception("No se pudo copiar el archivo PDF: {$tempPath}");
        }

        return $pdfPath;
    }

    /**
     * Une múltiples PDFs en uno solo
     */
    protected function mergePdfs(array $pdfFiles, int $userId, string $documentSlug): string
    {
        try {
            $merger = new Merger(new TcpdiDriver);

            foreach ($pdfFiles as $pdfFile) {
                if (! file_exists($pdfFile)) {
                    throw new Exception("El archivo PDF no existe: {$pdfFile}");
                }
                $merger->addRaw(file_get_contents($pdfFile));
            }

            $mergedPdf = $merger->merge();
            $outputPath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_merged_'.$documentSlug.'.pdf';
            file_put_contents($outputPath, $mergedPdf);

            \Log::info('PDFs unidos exitosamente', ['outputPath' => $outputPath]);

            return $outputPath;

        } catch (Exception $e) {
            \Log::error('Error uniendo PDFs', [
                'pdfFiles' => $pdfFiles,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sube el PDF final a GCS
     */
    protected function uploadFinalPdf(string $pdfPath, int $userId, string $documentSlug): string
    {
        if (! file_exists($pdfPath)) {
            throw new Exception("El PDF final no existe: {$pdfPath}");
        }

        $uuid = (string) Str::uuid();
        $gcsPath = "documentos/usuario_{$userId}/{$uuid}_{$documentSlug}_merged.pdf";

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $pdfPath,
            basename($pdfPath),
            'application/pdf',
            null,
            true
        );

        $this->gcsUploader->uploadFile($uploadedFile, $gcsPath);

        \Log::info('PDF final subido a GCS exitosamente', ['gcsPath' => $gcsPath]);

        return $gcsPath;
    }

    /**
     * Verifica si un archivo es una imagen
     */
    protected function isImage(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();

        return in_array($mimeType, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    /**
     * Verifica si un archivo es PDF
     */
    protected function isPdf(UploadedFile $file): bool
    {
        return $file->getMimeType() === 'application/pdf';
    }

    /**
     * Limpia archivos temporales
     */
    protected function cleanupTempFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                try {
                    unlink($file);
                    \Log::info('Archivo temporal eliminado', ['file' => $file]);
                } catch (Exception $e) {
                    \Log::warning('No se pudo eliminar archivo temporal', ['file' => $file, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    /**
     * Comprime imagen para reducir memoria en base64
     */
    protected function compressImageForPdf(string $imagePath): string
    {
        try {
            $imageInfo = getimagesize($imagePath);
            if (! $imageInfo) {
                \Log::warning('No se pudo obtener información de la imagen, usando original', ['path' => $imagePath]);

                return $imagePath;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // Tamaño máximo ultra-reducido para PDF (A4 a 72 DPI)
            $maxWidth = 595;   // 210mm * 72 DPI / 25.4
            $maxHeight = 842;  // 297mm * 72 DPI / 25.4

            // Si la imagen ya es pequeña, no comprimir
            if ($width <= $maxWidth && $height <= $maxHeight) {
                \Log::info('Imagen ya es suficientemente pequeña, no se comprime', [
                    'dimensions' => "{$width}x{$height}",
                    'maxAllowed' => "{$maxWidth}x{$maxHeight}",
                ]);

                return $imagePath;
            }

            \Log::info('Comprimiendo imagen agresivamente para PDF', [
                'original' => "{$width}x{$height}",
                'target' => "{$maxWidth}x{$maxHeight}",
                'path' => $imagePath,
            ]);

            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int) ($width * $ratio);
            $newHeight = (int) ($height * $ratio);

            // Crear imagen comprimida
            $image = null;
            $compressedImage = null;

            try {
                switch ($mimeType) {
                    case 'image/jpeg':
                        $image = @imagecreatefromjpeg($imagePath);
                        break;
                    case 'image/png':
                        $image = @imagecreatefrompng($imagePath);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($imagePath);
                        break;
                    default:
                        \Log::warning('Tipo de imagen no soportado para compresión', ['mime' => $mimeType]);

                        return $imagePath;
                }

                if (! $image) {
                    \Log::warning('No se pudo crear imagen para compresión, usando original');

                    return $imagePath;
                }

                $compressedImage = imagecreatetruecolor($newWidth, $newHeight);

                if ($mimeType === 'image/png') {
                    imagealphablending($compressedImage, false);
                    imagesavealpha($compressedImage, true);
                    $transparent = imagecolorallocatealpha($compressedImage, 255, 255, 255, 127);
                    imagefilledrectangle($compressedImage, 0, 0, $newWidth, $newHeight, $transparent);
                } else {
                    // Fondo blanco para otros formatos
                    $white = imagecolorallocate($compressedImage, 255, 255, 255);
                    imagefill($compressedImage, 0, 0, $white);
                }

                imagecopyresampled($compressedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                $compressedPath = $this->tempPath.DIRECTORY_SEPARATOR.Str::uuid().'_compressed_'.basename($imagePath);

                // Guardar con máxima compresión
                switch ($mimeType) {
                    case 'image/jpeg':
                        imagejpeg($compressedImage, $compressedPath, 60); // Calidad 60% para máxima compresión
                        break;
                    case 'image/png':
                        imagepng($compressedImage, $compressedPath, 9); // Compresión máxima (9)
                        break;
                    case 'image/gif':
                        imagegif($compressedImage, $compressedPath);
                        break;
                }

                return $compressedPath;

            } finally {
                // Liberar memoria inmediatamente
                if ($image) {
                    imagedestroy($image);
                    $image = null;
                }
                if ($compressedImage) {
                    imagedestroy($compressedImage);
                    $compressedImage = null;
                }

                // Forzar liberación de memoria
                $this->forceGarbageCollection();
            }

        } catch (\Exception $e) {
            \Log::warning('Error comprimiendo imagen, usando original', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);

            return $imagePath;
        }
    }

    /**
     * Crea HTML simple y optimizado para imagen
     */
    protected function createSimpleImageHtml(string $imagePath, string $originalName): string
    {
        if (! file_exists($imagePath)) {
            throw new Exception("La imagen no existe: {$imagePath}");
        }

        // Leer imagen en chunks para evitar cargar todo en memoria
        $imageData = $this->readImageInChunks($imagePath);
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        \Log::info('Creando HTML simple y optimizado para imagen', [
            'imagePath' => $imagePath,
            'mimeType' => $mimeType,
            'imageSize' => $this->formatBytes(filesize($imagePath)),
            'base64Length' => $this->formatBytes(strlen($imageData)),
        ]);

        // HTML mínimo y optimizado
        return "<!DOCTYPE html><html><head><meta charset='utf-8'><title>{$originalName}</title><style>*{margin:0;padding:0;box-sizing:border-box}html,body{margin:0;padding:0;width:100%;height:100%;overflow:hidden}.c{width:210mm;height:297mm;display:flex;align-items:center;justify-content:center;background:white}.c img{max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain}</style></head><body><div class='c'><img src='data:{$mimeType};base64,{$imageData}' alt='{$originalName}'></div></body></html>";
    }

    /**
     * Lee imagen en chunks para minimizar uso de memoria
     */
    protected function readImageInChunks(string $imagePath): string
    {
        $chunkSize = 8192; // 8KB chunks
        $imageData = '';

        $handle = fopen($imagePath, 'rb');
        if (! $handle) {
            throw new Exception("No se pudo abrir la imagen: {$imagePath}");
        }

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    break;
                }
                $imageData .= $chunk;

                // Liberar memoria del chunk anterior
                unset($chunk);
            }
        } finally {
            fclose($handle);
        }

        return base64_encode($imageData);
    }

    /**
     * Lee PDF en chunks para ahorrar memoria
     */
    protected function readPdfInChunks(string $pdfPath): string
    {
        $chunkSize = 8192; // 8KB chunks
        $pdfContent = '';

        $handle = fopen($pdfPath, 'rb');
        if (! $handle) {
            throw new Exception("No se pudo abrir el PDF: {$pdfPath}");
        }

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false) {
                    break;
                }
                $pdfContent .= $chunk;

                // Liberar memoria del chunk anterior
                unset($chunk);
            }
        } finally {
            fclose($handle);
        }

        return $pdfContent;
    }

    /**
     * Fuerza la liberación de memoria del sistema
     */
    protected function forceGarbageCollection(): void
    {
        // Esta función no está disponible en PHP puro.
        // En un entorno de producción, se recomienda usar un sistema de gestión de memoria
        // como APCu, Memcached, o simplemente reiniciar el servidor.
        // Para fines de desarrollo, podemos intentar forzar la recolección de basura
        // si la extensión APCu está habilitada.
        if (extension_loaded('apcu')) {
            apcu_clear_cache();
        }
        if (extension_loaded('memcached')) {
            // Si se está usando Memcached, limpiar la caché
            // Esto puede ser complicado y depende de la configuración exacta.
            // Una forma más robusta sería reiniciar el servidor.
        }
        // También podemos intentar liberar memoria manualmente si es posible,
        // pero esto es muy dependiente del sistema y el entorno.
    }

    /**
     * Formatea bytes para una presentación legible
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
