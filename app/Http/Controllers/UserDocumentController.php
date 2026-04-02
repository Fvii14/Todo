<?php

// app/Http/Controllers/UserDocumentController.php

namespace App\Http\Controllers;

use App\Models\AyudaDocumento;
use App\Models\Ccaa;
use App\Models\Contratacion;
use App\Models\HistorialActividad;
use App\Models\Provincia;
use App\Models\UserDocument;
use App\Services\GcsUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserDocumentController extends Controller
{
    public function index(Request $request)
    {
        $ccaas = Ccaa::orderBy('nombre_ccaa')->get();

        $query = UserDocument::with(['user', 'document']);

        if (Auth::id() !== 4 && Auth::id() !== 1) {
            $currentTramitadorId = Auth::id();
            $query->whereHas('user', function ($userQuery) {
                $userQuery->whereHas('contrataciones');
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('ccaa')) {
            $provincias = Provincia::where('id_ccaa', $request->ccaa)
                ->pluck('nombre_provincia');
            $query->whereHas('user.answers', function ($q) use ($provincias) {
                $q->where('question_id', 36)
                    ->whereIn('answer', $provincias);
            });
        }

        // paginamos 15 por página
        $userDocuments = $query->orderBy('created_at', 'desc')->paginate(15);

        $latestContracts = \App\Models\Contratacion::query()
            ->select('user_id', 'id')
            ->whereIn('user_id', $userDocuments->pluck('user_id')->unique())
            ->orderByDesc('fecha_contratacion')
            ->get()
            ->unique('user_id')
            ->pluck('id', 'user_id');

        return view('admin.docs-history', compact('userDocuments', 'ccaas', 'latestContracts'));
    }

    public function update(Request $request, UserDocument $userDocument)
    {
        $data = $request->validate([
            'estado' => 'required|in:pendiente,validado,rechazado',
            'contratacion_id' => 'required|exists:contrataciones,id',
            'nota_rechazo' => 'nullable|string|max:2000',
        ]);

        $estadoAnterior = $userDocument->estado;
        $userDocument->estado = $data['estado'];
        // Guardar/limpiar nota de rechazo según estado
        if ($data['estado'] === 'rechazado') {
            $userDocument->nota_rechazo = $request->input('nota_rechazo');
        } else {
            $userDocument->nota_rechazo = null;
        }
        $userDocument->save();

        HistorialActividad::create([
            'contratacion_id' => $data['contratacion_id'],
            'actividad' => "Estado de documento \"{$userDocument->document->name}\": '{$estadoAnterior}' → '{$userDocument->estado}'",
            'observaciones' => null,
        ]);

        // Verificar si todos los documentos generales están validados
        $this->verificarDocumentosGeneralesValidados($data['contratacion_id']);

        $mensaje = null;
        if ($data['estado'] === 'rechazado' && ! empty($userDocument->nota_rechazo)) {
            $mensaje = 'Nota de rechazo guardada correctamente';
        }

        return response()->json([
            'estado' => $userDocument->estado,
            'nota_rechazo' => $userDocument->nota_rechazo,
            'message' => $mensaje,
            'success' => true,
        ]);
    }

    public function destroy(Request $request, UserDocument $userDocument)
    {
        // Log::debug('Intentando eliminar documento', ['id' => $userDocument->id, 'file_path' => $userDocument->file_path]);

        try {

            if ($userDocument->file_path) {

                try {
                    // Elimina en GCS
                    $gcs = new \App\Services\GcsUploaderService;
                    // $gcs->delete($userDocument->file_path);

                } catch (\Throwable $e) {
                    Log::error('Error en GCS delete', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

                    return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
                }
            }
            $userDocument->delete();

            // Guardar en historial de actividad
            $contratacionId = $request->input('contratacion_id');
            if ($contratacionId) {
                HistorialActividad::create([
                    'contratacion_id' => $contratacionId,
                    'user_id' => Auth::id(),
                    'actividad' => 'Documento eliminado: '.($userDocument->document->name ?? ''),
                    'observaciones' => null,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar documento', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request, GcsUploaderService $gcs)
    {
        $request->validate([
            'document_id' => 'required|integer|exists:documents,id',
            'file' => 'nullable|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
            'firma_base64' => 'nullable|string',
            'conviviente_index' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $docId = $request->input('document_id');
        $nombrePersonalizado = $request->input('nombre_personalizado');
        $slug = $request->input('slug');
        $convivienteIndex = $request->input('conviviente_index')
            ?? $request->input('convivienteIndex')
            ?? $request->input('conviviente_index')
            ?? $request->get('conviviente_index')
            ?? $request->query('conviviente_index')
            ?? null;

        if ($convivienteIndex !== null && $convivienteIndex !== '' && $convivienteIndex !== 'null' && $convivienteIndex !== 'NULL') {
            $convivienteIndex = (int) $convivienteIndex;
        } else {
            $convivienteIndex = null;
        }

        // 🔎 Buscamos la contratación del usuario con la ayuda correspondiente
        $ayudaId = AyudaDocumento::where('documento_id', $docId)->value('ayuda_id');
        $contratacion = null;

        if ($ayudaId) {
            $contratacion = Contratacion::where('user_id', $user->id)
                ->where('ayuda_id', $ayudaId)
                ->latest()
                ->first();
        }

        $userDocumentsBeforeQuery = UserDocument::where('user_id', $user->id)
            ->where('document_id', $docId)
            ->where('slug', $slug);

        if ($convivienteIndex !== null) {
            $userDocumentsBeforeQuery->where('conviviente_index', $convivienteIndex);
        } else {
            $userDocumentsBeforeQuery->whereNull('conviviente_index');
        }

        // 📄 Caso 1: Subida múltiple
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = "documentos/usuario_{$user->id}/".Str::uuid().'.'.$file->getClientOriginalExtension();
                $gcs->uploadFile($file, $path);

                UserDocument::create([
                    'user_id' => $user->id,
                    'document_id' => $docId,
                    'slug' => $slug,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'nombre_personalizado' => $nombrePersonalizado,
                    'estado' => 'pendiente',
                    'conviviente_index' => $convivienteIndex,
                ]);
            }

            $this->verificarDocumentacionCompleta($user->id, $docId);

            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'contratacion_id' => $contratacion ? $contratacion->id : null,
            ]);
        }

        // 📄 Caso 2: Subida simple
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = "documentos/usuario_{$user->id}/".Str::uuid().'.'.$file->getClientOriginalExtension();
            $gcs->uploadFile($file, $path);

            $whereConditions = [
                'user_id' => $user->id,
                'document_id' => $docId,
                'slug' => $slug,
            ];

            // Si hay conviviente_index, incluirlo en la condición; si no, buscar documentos sin conviviente_index
            if ($convivienteIndex !== null && $convivienteIndex !== '') {
                $whereConditions['conviviente_index'] = $convivienteIndex;
            } else {
                $whereConditions['conviviente_index'] = null;
            }

            UserDocument::updateOrCreate(
                $whereConditions,
                [
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'nombre_personalizado' => $nombrePersonalizado,
                    'estado' => 'pendiente',
                    'conviviente_index' => $convivienteIndex ?: null,
                ]
            );

            $this->verificarDocumentacionCompleta($user->id, $docId);

            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'contratacion_id' => $contratacion ? $contratacion->id : null,
            ]);
        }

        // ✍️ Caso 3: Firma base64
        if ($request->filled('firma_base64')) {
            $base64 = $request->input('firma_base64');
            if (Str::startsWith($base64, 'data:image')) {
                $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            }

            $binary = base64_decode($base64, true);
            if ($binary === false) {
                throw ValidationException::withMessages(['firma_base64' => 'Firma inválida']);
            }
            $convivienteIndex = $request->input('conviviente_index');
            $filename = "firmas/usuario_{$user->id}/firma_{$user->id}".($convivienteIndex ? "_conviviente_{$convivienteIndex}" : '').'.png';
            $gcs->uploadString($binary, $filename);

            // Incluir conviviente_index en la condición para diferenciar firmas del solicitante y de convivientes
            $whereConditions = [
                'user_id' => $user->id,
                'document_id' => $docId,
                'slug' => 'firma',
            ];

            if ($convivienteIndex !== null && $convivienteIndex !== '') {
                $whereConditions['conviviente_index'] = $convivienteIndex;
            } else {
                $whereConditions['conviviente_index'] = null;
            }

            UserDocument::updateOrCreate(
                $whereConditions,
                [
                    'file_path' => $filename,
                    'file_name' => "firma_{$user->id}".($convivienteIndex ? "_conviviente_{$convivienteIndex}" : '').'.png',
                    'file_type' => 'image/png',
                    'size' => strlen($binary),
                    'nombre_personalizado' => 'Firma',
                    'estado' => 'pendiente',
                    'conviviente_index' => $convivienteIndex ?: null,
                ]
            );

            $this->verificarDocumentacionCompleta($user->id, $docId);

            // ✅ AQUÍ añadimos también el `contratacion_id`
            return response()->json([
                'success' => true,
                'message' => 'Firma subida correctamente',
                'contratacion_id' => $contratacion ? $contratacion->id : null,
            ]);
        }

        return response()->json(['success' => false, 'error' => 'No se proporcionó ningún archivo ni firma']);
    }

    // TODO: HAY QUE CAMBIAR EL ESTADO PORQUE NO ES EL CORRECTO
    private function verificarDocumentacionCompleta($userId, $docId)
    {
        $ayudaId = AyudaDocumento::where('documento_id', $docId)->value('ayuda_id');

        if ($ayudaId) {
            $contratacion = Contratacion::where('user_id', $userId)
                ->where('ayuda_id', $ayudaId)
                ->first();

            if ($contratacion) {
                $documentosObligatorios = AyudaDocumento::where('ayuda_id', $ayudaId)
                    ->pluck('documento_id');

                $documentosSubidos = UserDocument::where('user_id', $userId)
                    ->whereIn('document_id', $documentosObligatorios)
                    ->pluck('document_id');

                if ($documentosObligatorios->diff($documentosSubidos)->isEmpty()) {
                    $contratacion->estado = 'procesando';
                    $contratacion->save();
                }
            }
        }
    }

    public function subirDocumentoSubsanacion(Request $request, GcsUploaderService $gcs, $subsanacionDocId)
    {

        // ✅ Validamos solo si viene al menos algo
        $request->validate([
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'files.*' => 'nullable|file|max:10240',
            'firma_base64' => 'nullable|string',
        ]);

        // 🔎 Buscar registro de subsanación
        $subsanacion = DB::table('subsanacion_documents')->where('id', $subsanacionDocId)->first();
        if (! $subsanacion) {
            Log::error('❌ [Subsanación] No se encontró el registro en subsanacion_documents', [
                'subsanacionDocId' => $subsanacionDocId,
            ]);

            return response()->json(['success' => false, 'message' => 'Documento de subsanación no encontrado.'], 404);
        }

        // 🔎 Buscar contratación y usuario
        $contratacion = Contratacion::findOrFail($subsanacion->contratacion_id);
        $user = $contratacion->user;

        // 🔎 Buscar documento en la tabla documents
        $document = DB::table('documents')->where('id', $subsanacion->document_id)->first();
        if (! $document) {
            Log::error('❌ [Subsanación] Documento no encontrado en documents', [
                'document_id' => $subsanacion->document_id,
            ]);

            return response()->json(['success' => false, 'message' => 'Documento asociado a la subsanación no encontrado.'], 404);
        }

        // =============================
        // 📂 1️⃣ CASO FIRMA BASE64
        // =============================
        if ($request->filled('firma_base64')) {

            $base64 = $request->input('firma_base64');
            if (Str::startsWith($base64, 'data:image')) {
                $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            }

            $binary = base64_decode($base64, true);
            if ($binary === false) {
                return response()->json(['success' => false, 'message' => 'Firma inválida'], 422);
            }

            $filename = "firmas/usuario_{$user->id}/firma_{$user->id}.png";
            $gcs->uploadString($binary, $filename);

            UserDocument::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'slug' => 'firma',
                'file_path' => $filename,
                'file_name' => "firma_{$user->id}.png",
                'file_type' => 'image/png',
                'size' => strlen($binary),
                'nombre_personalizado' => 'Firma',
                'estado' => 'pendiente',
                'conviviente_index' => null,
            ]);

            DB::table('subsanacion_documents')->where('id', $subsanacionDocId)->update([
                'estado' => 'subido',
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Firma subida correctamente.',
                'contratacion_id' => $subsanacion->contratacion_id,
            ], 200);
        }

        // =============================
        // 📂 2️⃣ CASO MULTI-UPLOAD
        // =============================
        if ($request->hasFile('files')) {

            foreach ($request->file('files') as $file) {
                $uuid = (string) Str::uuid();
                $ext = $file->getClientOriginalExtension();
                $path = "documentos/usuario_{$user->id}/{$uuid}.{$ext}";
                $gcs->uploadFile($file, $path);

                UserDocument::create([
                    'user_id' => $user->id,
                    'document_id' => $document->id,
                    'slug' => $document->slug,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'nombre_personalizado' => $document->name,
                    'estado' => 'pendiente',
                    'conviviente_index' => null,
                ]);
            }

            DB::table('subsanacion_documents')->where('id', $subsanacionDocId)->update([
                'estado' => 'subido',
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documentos de subsanación subidos correctamente.',
                'contratacion_id' => $subsanacion->contratacion_id,
            ], 200);
        }

        // =============================
        // 📂 3️⃣ CASO SUBIDA SIMPLE
        // =============================
        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $uuid = (string) Str::uuid();
            $ext = $file->getClientOriginalExtension();
            $path = "documentos/usuario_{$user->id}/{$uuid}.{$ext}";
            $gcs->uploadFile($file, $path);

            UserDocument::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'slug' => $document->slug,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'nombre_personalizado' => $document->name,
                'estado' => 'pendiente',
                'conviviente_index' => null,
            ]);

            DB::table('subsanacion_documents')->where('id', $subsanacionDocId)->update([
                'estado' => 'subido',
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento de subsanación subido correctamente.',
                'contratacion_id' => $subsanacion->contratacion_id,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se proporcionó ningún archivo ni firma',
        ], 422);
    }

    public function marcarDocumentosSubsanacion(Request $request, $contratacionId)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'integer|exists:documents,id',
            'razon' => 'nullable|string|max:255',
            'nota_personalizada' => 'nullable|string|max:500',
        ]);

        $contratacion = Contratacion::findOrFail($contratacionId);

        // Cambiar estado a subsanación
        $contratacion->estado = 'subsanacion';
        $contratacion->save();

        foreach ($request->document_ids as $docId) {
            DB::table('subsanacion_documents')->insert([
                'contratacion_id' => $contratacion->id,
                'document_id' => $docId,
                'razon' => $request->input('razon'),
                'nota_personalizada' => $request->input('nota_personalizada'),
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '✅ Documento de subsanación marcado correctamente.',
            'ayuda_id' => $contratacion->id,
        ], 200);
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

        } catch (\Exception $e) {
            Log::error('Error al verificar documentos generales validados', [
                'contratacion_id' => $contratacionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $todosGeneralesValidados;
    }
}
