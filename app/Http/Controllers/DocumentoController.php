<?php

namespace App\Http\Controllers;

use App\Models\Contratacion;
use App\Models\Document;
use App\Models\HistorialActividad;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\GcsUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentoController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre_documento' => 'required|string|unique:documents,name',
                'slug' => 'required|string|unique:documents,slug',
                'allowed_formats' => 'required|array',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors(['file' => 'No se ha validado el documento porque '.$e->validator->errors()])->withInput();
        }

        try {
            Document::create([
                'name' => $request->input('nombre_documento'),
                'description' => $request->input('descripcion_documento'),
                'slug' => $request->input('slug'),
                'allowed_types' => implode(', ', $request->input('allowed_formats')),
            ]);

            return back()->with('success', 'Documento creado correctamente');
        } catch (\Exception $ex) {
            return back()->withErrors(['file' => 'No se pudo crear el documento porque '.$ex->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $question = Document::findOrFail($id);
        $question->delete();

        return redirect()->route('ayudas.index')->with('success', 'Documento eliminado correctamente.');
    }

    public static function documentacionUsuario($user_id)
    {
        // Obtener documentos del usuario
        $documentos = UserDocument::where('user_id', $user_id)->get();

        return $documentos;
    }

    public function allDocuments(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search', '');

        $query = Document::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('allowed_types', 'LIKE', "%{$search}%");
            });
        }

        $allDocuments = $query->orderBy('name', 'asc')->paginate($perPage);

        $allDocuments->appends(['search' => $search, 'per_page' => $perPage]);

        return view('admin.documentos', compact('allDocuments', 'search'));
    }

    public function searchDocuments(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 15);

        $query = Document::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('allowed_types', 'LIKE', "%{$search}%");
            });
        }

        $documents = $query->orderBy('name', 'asc')->paginate($perPage);

        return response()->json([
            'documents' => $documents->items(),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
            ],
            'search' => $search,
        ]);
    }

    /**
     * Obtener todos los documentos disponibles para el selector
     */
    public function getAllDocuments()
    {
        $documents = Document::select('id', 'name', 'slug', 'description')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }

    public function index(Request $request, $id)
    {
        $documentosUsuario = $this->documentacionUsuario($id);
        $gcs = new GcsUploaderService;
        $documentosUsuario = $documentosUsuario->map(function ($documento) use ($gcs) {
            $mimeType = $documento->file_type ?? 'application/pdf';
            $overrides = ['responseType' => $mimeType];

            return [
                'document_id' => $documento->document->name,
                'temporaryUrl' => $gcs->getTemporaryUrl($documento->file_path, 60, $overrides),
            ];
        });
        $user = User::where('id', $id)->first();

        return view('admin.documentacion-usuario', compact('documentosUsuario', 'user'));
    }

    public function uploadMissingDocument(Request $request, GcsUploaderService $gcs, $contratacionId)
    {
        // Log para debugging
        Log::info('uploadMissingDocument iniciado', [
            'contratacion_id' => $contratacionId,
            'request_data' => $request->all(),
            'files_count' => count($request->file('files', [])),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        Log::info('DEBUG: Punto 1 - Inicio del método uploadMissingDocument', ['contratacion_id' => $contratacionId]);

        try {
            Log::info('DEBUG: Punto 2 - Iniciando validación', ['contratacion_id' => $contratacionId]);

            $data = $request->validate([
                'document_id' => 'required|integer|exists:documents,id',
                'contratacion_id' => 'required|integer|exists:contrataciones,id',
                'slug' => 'nullable|string',
                'conviviente_index' => 'nullable|integer|min:1',
                'files' => 'required|array|min:1',
                'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            ]);

            Log::info('DEBUG: Punto 3 - Validación exitosa', ['contratacion_id' => $contratacionId, 'data' => $data]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en uploadMissingDocument', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'contratacion_id' => $contratacionId,
            ]);

            return response()->json([
                'error' => 'Error de validación',
                'message' => 'Los datos enviados no son válidos',
                'errors' => $e->errors(),
            ], 422);
        }

        Log::info('DEBUG: Punto 4 - Verificando ID de contratación', ['contratacion_id' => $contratacionId, 'data_contratacion_id' => $data['contratacion_id']]);

        if ($data['contratacion_id'] != $contratacionId) {
            Log::error('DEBUG: Error - ID de contratación no coincide', ['contratacion_id' => $contratacionId, 'data_contratacion_id' => $data['contratacion_id']]);

            return response()->json(['error' => 'El ID de contratación no coincide'], 400);
        }

        Log::info('DEBUG: Punto 5 - Verificando slug', ['contratacion_id' => $contratacionId, 'slug' => $data['slug']]);

        if (empty($data['slug'])) {
            Log::info('DEBUG: Punto 5.1 - Slug vacío, buscando documento', ['contratacion_id' => $contratacionId, 'document_id' => $data['document_id']]);
            $document = \App\Models\Document::find($data['document_id']);
            Log::info('DEBUG: Punto 5.2 - Documento encontrado', ['contratacion_id' => $contratacionId, 'document' => $document ? 'encontrado' : 'no encontrado']);
            if ($document) {
                $data['slug'] = $document->slug;
                Log::info('DEBUG: Punto 5.3 - Slug actualizado', ['contratacion_id' => $contratacionId, 'new_slug' => $data['slug']]);
            }
        } else {
            Log::info('DEBUG: Punto 5.1 - Slug ya tiene valor', ['contratacion_id' => $contratacionId, 'slug' => $data['slug']]);
        }

        Log::info('DEBUG: Punto 5.5 - Después de verificar slug', ['contratacion_id' => $contratacionId, 'slug' => $data['slug']]);

        $contratacion = Contratacion::findOrFail($contratacionId);

        Log::info('DEBUG: Punto 5.6 - Contratación encontrada', ['contratacion_id' => $contratacionId, 'user_id' => $contratacion->user_id]);

        $user = $contratacion->user;
        $files = $request->file('files');

        if (! $files || count($files) === 0) {
            Log::error('DEBUG: Error - No se han proporcionado archivos', ['contratacion_id' => $contratacionId]);

            return response()->json(['error' => 'No se han proporcionado archivos'], 400);
        }

        Log::info('DEBUG: Punto 5.7 - Archivos verificados', ['contratacion_id' => $contratacionId, 'files_count' => count($files)]);

        try {
            Log::info('DEBUG: Punto 5.8 - Iniciando procesamiento de archivos', ['contratacion_id' => $contratacionId, 'files_count' => count($files)]);

            if (count($files) === 1) {
                Log::info('DEBUG: Punto 5.9 - Procesando archivo único', ['contratacion_id' => $contratacionId]);

                return $this->uploadSingleFile($files[0], $data, $contratacion, $user, $gcs);
            }

            $multiFileProcessor = app(\App\Services\MultiFileProcessorService::class);
            $result = $multiFileProcessor->processMultipleFiles($files, $user->id, $data['slug']);

            if (! $result['success']) {
                throw new \Exception('Error procesando múltiples archivos');
            }

            $ud = null;

            try {
                $fileSize = 1024; // 1KB por defecto

                $convivienteIndex = null;
                if (isset($data['conviviente_index']) && $data['conviviente_index'] !== '' && $data['conviviente_index'] !== null) {
                    $convivienteIndex = (int) $data['conviviente_index'];
                }

                $userDocumentData = [
                    'user_id' => (int) $user->id,
                    'document_id' => (int) $data['document_id'], // Convertir a entero
                    'slug' => $data['slug'],
                    'file_path' => $result['final_pdf_path'],
                    'file_name' => 'documentos_combinados.pdf',
                    'file_type' => 'application/pdf',
                    'size' => (int) $fileSize, // Convertir a entero
                    'estado' => 'validado',
                    'conviviente_index' => $convivienteIndex,
                ];

                $userDocumentData['user_id'] = (int) $userDocumentData['user_id'];
                $userDocumentData['document_id'] = (int) $userDocumentData['document_id'];
                $userDocumentData['size'] = (int) $userDocumentData['size'];

                try {
                    $ud = UserDocument::create($userDocumentData);
                } catch (\Illuminate\Database\QueryException $e) {
                    \Log::error('Error de base de datos al crear UserDocument', [
                        'error' => $e->getMessage(),
                        'sql' => $e->getSql(),
                        'bindings' => $e->getBindings(),
                        'data' => $userDocumentData,
                    ]);
                    throw $e;
                } catch (\Exception $e) {
                    \Log::error('Error general al crear UserDocument', [
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }

            } catch (\Exception $e) {
                \Log::error('Error creando UserDocument', [
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            if (! $ud) {
                throw new \Exception('No se pudo crear el UserDocument');
            }

            $ud->load('document');

            try {
                HistorialActividad::create([
                    'contratacion_id' => $contratacion->id,
                    'actividad' => 'Documento múltiple "'.$ud->document->name.'" subido por el tramitador',
                    'observaciones' => 'Se combinaron '.count($files).' archivos en un PDF',
                ]);
            } catch (\Exception $e) {
                \Log::error('Error creando HistorialActividad', [
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);
                throw $e;
            }

            try {
                $ud->temporary_url = $gcs->getTemporaryUrl($result['final_pdf_path']);
            } catch (\Throwable $e) {
                \Log::warning('[uploadMissingDocument] URL temp error: '.$e->getMessage());
                $ud->temporary_url = null;
            }

            $this->verificarDocumentosGeneralesValidados($contratacion->id);

            return response()->json($ud, 201);

        } catch (\Exception $e) {
            Log::error('Error en uploadMissingDocument: '.$e->getMessage());

            return response()->json(['error' => 'Error procesando archivos: '.$e->getMessage()], 500);
        }
    }

    /**
     * Subir documentos de tramitación personalizados
     */
    public function uploadDocumentoTramitacion(Request $request, GcsUploaderService $gcs, $contratacionId)
    {
        // Log para debugging
        Log::info('uploadDocumentoTramitacion iniciado', [
            'contratacion_id' => $contratacionId,
            'request_data' => $request->all(),
            'files_count' => count($request->file('files', [])),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        try {
            $data = $request->validate([
                'document_id' => 'required|integer|exists:documents,id',
                'contratacion_id' => 'required|integer|exists:contrataciones,id',
                'slug' => 'required|string',
                'nombre_personalizado' => 'nullable|string',
                'files' => 'required|array|min:1',
                'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en uploadDocumentoTramitacion', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'contratacion_id' => $contratacionId,
            ]);

            return response()->json([
                'error' => 'Error de validación',
                'message' => 'Los datos enviados no son válidos',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($data['contratacion_id'] != $contratacionId) {
            return response()->json(['error' => 'El ID de contratación no coincide'], 400);
        }

        $contratacion = Contratacion::findOrFail($contratacionId);
        $user = $contratacion->user;
        $files = $request->file('files');

        if (! $files || count($files) === 0) {
            return response()->json(['error' => 'No se han proporcionado archivos'], 400);
        }

        try {
            if (count($files) === 1) {
                return $this->uploadSingleFileTramitacion($files[0], $data, $contratacion, $user, $gcs);
            }

            $multiFileProcessor = app(\App\Services\MultiFileProcessorService::class);
            $result = $multiFileProcessor->processMultipleFiles($files, $user->id, $data['slug']);

            if (! $result['success']) {
                throw new \Exception('Error procesando múltiples archivos');
            }

            // Obtener el tamaño real del archivo
            $fileSize = file_exists($result['final_pdf_path']) ? filesize($result['final_pdf_path']) : 1024;

            $userDocumentData = [
                'user_id' => (int) $user->id,
                'document_id' => (int) $data['document_id'],
                'slug' => $data['slug'],
                'file_path' => $result['final_pdf_path'],
                'file_name' => 'documentos_combinados.pdf',
                'file_type' => 'application/pdf',
                'size' => (int) $fileSize,
                'estado' => 'validado',
                'nombre_personalizado' => $data['nombre_personalizado'] ?? null,
            ];

            $ud = UserDocument::create($userDocumentData);
            $ud->load('document');

            try {
                HistorialActividad::create([
                    'contratacion_id' => $contratacion->id,
                    'actividad' => 'Documento de tramitación "'.($data['nombre_personalizado'] ?? $ud->document->name).'" subido por el tramitador',
                    'observaciones' => 'Se combinaron '.count($files).' archivos en un PDF',
                ]);

                // Verificar si es justificante de presentación de ayuda
                $this->verificarJustificantePresentacion($contratacion, $data['slug']);
            } catch (\Exception $e) {
                \Log::error('Error creando HistorialActividad', [
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);
                throw $e;
            }

            try {
                $ud->temporary_url = $gcs->getTemporaryUrl($result['final_pdf_path']);
            } catch (\Throwable $e) {
                \Log::warning('[uploadDocumentoTramitacion] URL temp error: '.$e->getMessage());
                $ud->temporary_url = null;
            }

            return response()->json($ud, 201);

        } catch (\Exception $e) {
            Log::error('Error en uploadDocumentoTramitacion: '.$e->getMessage());

            return response()->json(['error' => 'Error procesando archivos: '.$e->getMessage()], 500);
        }
    }

    /**
     * Método auxiliar para subir un solo archivo de tramitación
     */
    protected function uploadSingleFileTramitacion($file, $data, $contratacion, $user, $gcs)
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension();
        $path = "documentos/usuario_{$user->id}/{$uuid}.{$ext}";
        $gcs->uploadFile($file, $path);

        $ud = UserDocument::create([
            'user_id' => (int) $user->id,
            'document_id' => (int) $data['document_id'],
            'slug' => $data['slug'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
            'estado' => 'validado',
            'nombre_personalizado' => $data['nombre_personalizado'] ?? null,
        ]);

        $ud->load('document');

        try {
            HistorialActividad::create([
                'contratacion_id' => $contratacion->id,
                'actividad' => 'Documento de tramitación "'.($data['nombre_personalizado'] ?? $ud->document->name).'" subido por el tramitador',
                'observaciones' => null,
            ]);

            // Verificar si es justificante de presentación de ayuda
            $this->verificarJustificantePresentacion($contratacion, $data['slug']);
        } catch (\Exception $e) {
            Log::error('Error creando HistorialActividad', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }

        // Generación URL
        try {
            $ud->temporary_url = $gcs->getTemporaryUrl($path);
        } catch (\Throwable $e) {
            Log::warning('[uploadDocumentoTramitacion] URL temp error: '.$e->getMessage());
            $ud->temporary_url = null;
        }

        return response()->json($ud, 201);
    }

    /**
     * Método auxiliar para subir un solo archivo (método original)
     */
    protected function uploadSingleFile($file, $data, $contratacion, $user, $gcs)
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension();
        $path = "documentos/usuario_{$user->id}/{$uuid}.{$ext}";

        $gcs->uploadFile($file, $path);

        $convIndex = $data['conviviente_index'] ?? null;

        $convivienteIndex = null;
        if ($convIndex !== null && $convIndex !== '' && $convIndex !== '') {
            $convivienteIndex = (int) $convIndex;
        }

        $ud = UserDocument::create([
            'user_id' => (int) $user->id,
            'document_id' => (int) $data['document_id'],
            'slug' => $data['slug'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
            'estado' => 'validado',
            'conviviente_index' => $convivienteIndex,
        ]);

        $ud->load('document');

        HistorialActividad::create([
            'contratacion_id' => $contratacion->id,
            'actividad' => 'Documento "'.$ud->document->name.'" subido por el tramitador',
            'observaciones' => null,
        ]);

        // Generación URL
        try {
            $ud->temporary_url = $gcs->getTemporaryUrl($path);
        } catch (\Throwable $e) {
            Log::warning('[uploadMissingDocument] URL temp error: '.$e->getMessage());
            $ud->temporary_url = null;
        }

        return response()->json($ud, 201);
    }

    /**
     * Verifica si todos los documentos generales están validados y maneja el flujo de cotejo
     */
    private function verificarDocumentosGeneralesValidados($contratacionId)
    {
        try {
            $contratacion = Contratacion::with(['ayuda.ayudaDocumentos.documento', 'user.userDocuments'])->find($contratacionId);

            if (! $contratacion) {
                return;
            }

            // Obtener documentos generales obligatorios
            $documentosGeneralesObligatorios = $contratacion->ayuda->ayudaDocumentos
                ->where('es_obligatorio', true)
                ->filter(function ($ayudaDoc) {
                    return $ayudaDoc->documento && $ayudaDoc->documento->tipo === 'general';
                })
                ->pluck('documento_id');

            if ($documentosGeneralesObligatorios->isEmpty()) {
                return;
            }

            // Verificar que todos los documentos generales estén validados
            $documentosGeneralesValidados = $contratacion->user->userDocuments
                ->whereIn('document_id', $documentosGeneralesObligatorios)
                ->where('estado', 'validado');

            $documentosIdsValidados = $documentosGeneralesValidados->pluck('document_id')->unique();
            $todosGeneralesValidados = $documentosGeneralesObligatorios->diff($documentosIdsValidados)->isEmpty();

            // Mostrar pregunta sobre documentos extra si:
            // 1. Todos los documentos generales están validados, O
            // 2. La tarea es cotejo con opción documentación (independientemente del estado de validación)
            if ($todosGeneralesValidados) {
                $this->mostrarPreguntaDocumentosExtra($contratacionId);
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar documentos generales validados', [
                'contratacion_id' => $contratacionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Verifica si se subió un justificante de presentación de ayuda y procesa el cambio de fase
     */
    private function verificarJustificantePresentacion($contratacion, $slug)
    {
        try {
            // Verificar si es el justificante de presentación de ayuda
            if ($slug !== 'justificante-presentacion-ayuda') {
                return;
            }

            // Verificar que la contratación está en tramitación (OPx)
            $contratacion->load('estadosContratacion');
            if (! $contratacion->estadosContratacion->contains('codigo', 'OP1-Tramitacion')) {
                return;
            }
            // Registrar en el historial
            HistorialActividad::create([
                'contratacion_id' => $contratacion->id,
                'actividad' => 'Justificante de presentación de ayuda subido (en tramitación OPx)',
                'observaciones' => 'Cambio automático al subir justificante de presentación de ayuda',
            ]);

        } catch (\Exception $e) {
            // Error silencioso
        }
    }

    /**
     * Enviar WhatsApp con template 384 para tramitación presentada
     */
    // !! Brevo esto lo tenemos que replicar con eventos y router

    private function enviarWhatsAppTramitacionPresentada($contratacion)
    {
        try {
            // Obtener el usuario de la contratación
            $user = $contratacion->user;
            if (! $user) {
                Log::warning('No se encontró usuario para la contratación en tramitación presentada', [
                    'contratacion_id' => $contratacion->id,
                ]);

                return;
            }

            // Obtener teléfono del usuario
            $telefono = \App\Models\Answer::where('user_id', $user->id)
                ->where('question_id', 45) // ID de la pregunta del teléfono
                ->whereNull('conviviente_id')
                ->value('answer');

            if (! $telefono) {
                Log::warning('No se encontró teléfono para usuario en tramitación presentada', [
                    'user_id' => $user->id,
                    'contratacion_id' => $contratacion->id,
                ]);

                return;
            }

            // Formatear teléfono
            $telefonoFormateado = preg_replace('/[^0-9]/', '', $telefono);
            if (! str_starts_with($telefonoFormateado, '34')) {
                $telefonoFormateado = '34'.$telefonoFormateado;
            }

            // Enviar WhatsApp con template 384
            $brevoService = app(\App\Services\BrevoService::class);
            $response = $brevoService->sendWhatsAppMessageWithParams($telefonoFormateado, 384, [
                'NOMBRE' => $user->name,
            ]);

            Log::info('WhatsApp enviado para tramitación presentada', [
                'user_id' => $user->id,
                'contratacion_id' => $contratacion->id,
                'telefono' => $telefonoFormateado,
                'template_id' => 384,
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp para tramitación presentada', [
                'contratacion_id' => $contratacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Enviar WhatsApp con template 376 para cotejo con opción documentación
     */
    // !! Brevo esto lo tenemos que replicar con eventos y router

    private function enviarWhatsAppCotejoDocumentacion($user)
    {
        try {
            // Obtener teléfono del usuario
            $telefono = \App\Models\Answer::where('user_id', $user->id)
                ->where('question_id', 45) // ID de la pregunta del teléfono
                ->whereNull('conviviente_id')
                ->value('answer');

            if (! $telefono) {
                Log::warning('No se encontró teléfono para usuario en cotejo documentación', [
                    'user_id' => $user->id,
                ]);

                return;
            }

            // Formatear teléfono
            $telefonoFormateado = preg_replace('/[^0-9]/', '', $telefono);
            if (! str_starts_with($telefonoFormateado, '34')) {
                $telefonoFormateado = '34'.$telefonoFormateado;
            }

            // Enviar WhatsApp con template 376
            $brevoService = app(\App\Services\BrevoService::class);
            $response = $brevoService->sendWhatsAppMessageWithParams($telefonoFormateado, 376, [
                'NOMBRE' => $user->name,
            ]);

            Log::info('WhatsApp enviado para cotejo documentación', [
                'user_id' => $user->id,
                'telefono' => $telefonoFormateado,
                'template_id' => 376,
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp para cotejo documentación', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
