<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ayuda;
use App\Models\AyudaRequisitoJson;
use App\Models\AyudaRequisitoVersion;
use App\Models\QuestionCondition;
use App\Models\QuestionnaireConditionVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VersionController extends Controller
{
    public function getRequisitosVersions($ayudaId)
    {
        try {
            $ayuda = Ayuda::findOrFail($ayudaId);
            $versions = AyudaRequisitoVersion::where('ayuda_id', $ayudaId)
                ->with('createdBy')
                ->orderBy('version_number', 'desc')
                ->get();

            $activeVersion = AyudaRequisitoVersion::getActiveVersion($ayudaId);
            $currentDraft = AyudaRequisitoVersion::getCurrentDraft($ayudaId);

            return response()->json([
                'success' => true,
                'versions' => $versions,
                'active_version' => $activeVersion,
                'current_draft' => $currentDraft,
                'ayuda' => $ayuda,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo versiones de requisitos: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getConditionsVersions($questionnaireId)
    {
        try {
            $versions = QuestionnaireConditionVersion::where('questionnaire_id', $questionnaireId)
                ->with('createdBy')
                ->orderBy('version_number', 'desc')
                ->get();

            $activeVersion = QuestionnaireConditionVersion::getActiveVersion($questionnaireId);
            $currentDraft = QuestionnaireConditionVersion::getCurrentDraft($questionnaireId);

            return response()->json([
                'success' => true,
                'versions' => $versions,
                'active_version' => $activeVersion,
                'current_draft' => $currentDraft,
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo versiones de condiciones: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createRequisitosDraft(Request $request, $ayudaId)
    {
        try {
            $request->validate([
                'description' => 'nullable|string|max:500',
                'json_regla' => 'nullable|array', // Permitir datos del frontend
            ]);

            // Si se envían datos del frontend, usarlos; si no, usar datos actuales
            $jsonRegla = $request->input('json_regla');

            if ($jsonRegla === null) {
                // Si no se envían datos, usar los datos actuales de la base de datos
                $requisitos = AyudaRequisitoJson::where('ayuda_id', $ayudaId)->first();

                if (! $requisitos) {
                    return response()->json(['error' => 'No se encontraron requisitos para esta ayuda'], 404);
                }

                $draft = $requisitos->createDraft($request->description);
            } else {
                // Si se envían datos, crear el draft con esos datos
                $draft = AyudaRequisitoVersion::create([
                    'ayuda_id' => $ayudaId,
                    'version_number' => AyudaRequisitoVersion::getNextVersionNumber($ayudaId),
                    'json_regla' => $jsonRegla,
                    'is_active' => false,
                    'is_draft' => true,
                    'created_by' => Auth::user()->id ?? 1,
                    'version_description' => $request->description ?? 'Nuevo draft',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Draft creado correctamente',
                'draft' => $draft,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando draft de requisitos: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createConditionsDraft(Request $request, $questionnaireId)
    {
        try {
            Log::info('Creando draft de condiciones', [
                'questionnaireId' => $questionnaireId,
                'requestData' => $request->all(),
                'conditionsData' => $request->input('conditions_data'),
                'conditionsDataType' => gettype($request->input('conditions_data')),
                'conditionsDataIsArray' => is_array($request->input('conditions_data')),
                'conditionsDataCount' => is_array($request->input('conditions_data')) ? count($request->input('conditions_data')) : 'N/A',
            ]);

            $request->validate([
                'description' => 'nullable|string|max:500',
                'conditions_data' => 'nullable|array', // Permitir datos del frontend
            ]);

            // Si se envían datos del frontend, usarlos; si no, usar datos actuales
            $conditionsData = $request->input('conditions_data');

            if ($conditionsData === null) {
                Log::info('Usando datos actuales de la base de datos');
                // Si no se envían datos, usar los datos actuales de la base de datos
                $draft = QuestionCondition::createDraft($questionnaireId, $request->description);
            } else {
                Log::info('Usando datos enviados del frontend', [
                    'conditionsData' => $conditionsData,
                ]);
                // Si se envían datos, crear el draft con esos datos
                $draft = QuestionnaireConditionVersion::create([
                    'questionnaire_id' => $questionnaireId,
                    'version_number' => QuestionnaireConditionVersion::getNextVersionNumber($questionnaireId),
                    'conditions_data' => $conditionsData,
                    'is_active' => false,
                    'is_draft' => true,
                    'created_by' => Auth::user()->id ?? 1,
                    'version_description' => $request->description ?? 'Nuevo draft',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Draft creado correctamente',
                'draft' => $draft,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando draft de condiciones: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function publishRequisitosVersion($versionId)
    {
        try {
            $version = AyudaRequisitoVersion::findOrFail($versionId);

            if (! $version->is_draft) {
                return response()->json(['error' => 'Solo se pueden publicar drafts'], 400);
            }

            $version->publish();

            AyudaRequisitoJson::updateOrCreate(
                ['ayuda_id' => $version->ayuda_id],
                [
                    'json_regla' => $version->json_regla,
                    'descripcion' => $version->descripcion,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Versión publicada correctamente',
                'version' => $version->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error publicando versión de requisitos: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function publishConditionsVersion($versionId)
    {
        try {
            Log::info('Publicando versión de condiciones', [
                'versionId' => $versionId,
            ]);

            $version = QuestionnaireConditionVersion::findOrFail($versionId);

            Log::info('Versión encontrada', [
                'version' => $version->toArray(),
                'is_draft' => $version->is_draft,
                'conditions_data_count' => is_array($version->conditions_data) ? count($version->conditions_data) : 'No es array',
            ]);

            if (! $version->is_draft) {
                return response()->json(['error' => 'Solo se pueden publicar drafts'], 400);
            }

            $version->publish();

            Log::info('Eliminando condiciones existentes', [
                'questionnaire_id' => $version->questionnaire_id,
            ]);

            QuestionCondition::where('questionnaire_id', $version->questionnaire_id)->delete();

            Log::info('Creando nuevas condiciones', [
                'conditions_data' => $version->conditions_data,
            ]);

            foreach ($version->conditions_data as $conditionData) {
                Log::info('Creando condición', $conditionData);

                // Asegurar que questionnaire_id esté presente
                $questionnaireId = $conditionData['questionnaire_id'] ?? $version->questionnaire_id;

                QuestionCondition::create([
                    'question_id' => $conditionData['question_id'],
                    'operator' => $conditionData['operator'],
                    'value' => $conditionData['value'],
                    'next_question_id' => $conditionData['next_question_id'],
                    'questionnaire_id' => $questionnaireId,
                    'order' => $conditionData['order'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Versión publicada correctamente',
                'version' => $version->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error publicando versión de condiciones: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateRequisitosDraft(Request $request, $versionId)
    {
        try {
            $request->validate([
                'json_regla' => 'required|array',
                'descripcion' => 'nullable|string',
            ]);

            $version = AyudaRequisitoVersion::findOrFail($versionId);

            if (! $version->is_draft) {
                return response()->json(['error' => 'Solo se pueden actualizar drafts'], 400);
            }

            $version->update([
                'json_regla' => $request->json_regla,
                'descripcion' => $request->descripcion,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft actualizado correctamente',
                'version' => $version->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateConditionsDraft(Request $request, $versionId)
    {
        try {
            Log::info('Actualizando draft de condiciones', [
                'versionId' => $versionId,
                'requestData' => $request->all(),
                'conditionsData' => $request->input('conditions_data'),
                'conditionsDataType' => gettype($request->input('conditions_data')),
                'conditionsDataIsArray' => is_array($request->input('conditions_data')),
                'conditionsDataCount' => is_array($request->input('conditions_data')) ? count($request->input('conditions_data')) : 'N/A',
            ]);

            // Validación manual para conditions_data
            if (! $request->has('conditions_data')) {
                return response()->json(['error' => 'The conditions data field is required.'], 422);
            }

            if (! is_array($request->conditions_data)) {
                return response()->json(['error' => 'The conditions data must be an array.'], 422);
            }

            $request->validate([
                'descripcion' => 'nullable|string',
            ]);

            $version = QuestionnaireConditionVersion::findOrFail($versionId);

            if (! $version->is_draft) {
                return response()->json(['error' => 'Solo se pueden actualizar drafts'], 400);
            }

            $updateData = [
                'conditions_data' => $request->conditions_data,
            ];

            // Solo actualizar descripción si se proporciona
            if ($request->has('descripcion')) {
                $updateData['version_description'] = $request->descripcion;
            }

            Log::info('Datos a actualizar', $updateData);

            $version->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Draft actualizado correctamente',
                'version' => $version->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function editRequisitosVersion(Request $request, $ayudaId, $versionId)
    {
        try {
            $version = AyudaRequisitoVersion::where('ayuda_id', $ayudaId)
                ->where('id', $versionId)
                ->firstOrFail();

            if ($version->is_draft) {
                return response()->json(['error' => 'No se puede editar un draft desde aquí'], 400);
            }

            // Crear un nuevo draft basado en esta versión
            $newDraft = AyudaRequisitoVersion::create([
                'ayuda_id' => $ayudaId,
                'version_number' => AyudaRequisitoVersion::getNextVersionNumber($ayudaId),
                'json_regla' => $version->json_regla,
                'descripcion' => $version->descripcion,
                'is_active' => false,
                'is_draft' => true,
                'created_by' => auth()->id() ?? 1,
                'version_description' => 'Draft basado en versión '.$version->version_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft creado basado en la versión seleccionada',
                'draft' => $newDraft,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando draft desde versión: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function editConditionsVersion(Request $request, $questionnaireId, $versionId)
    {
        try {
            $version = QuestionnaireConditionVersion::where('questionnaire_id', $questionnaireId)
                ->where('id', $versionId)
                ->firstOrFail();

            if ($version->is_draft) {
                return response()->json(['error' => 'No se puede editar un draft desde aquí'], 400);
            }

            // Crear un nuevo draft basado en esta versión
            $newDraft = QuestionnaireConditionVersion::create([
                'questionnaire_id' => $questionnaireId,
                'version_number' => QuestionnaireConditionVersion::getNextVersionNumber($questionnaireId),
                'conditions_data' => $version->conditions_data,
                'is_active' => false,
                'is_draft' => true,
                'created_by' => auth()->id() ?? 1,
                'version_description' => 'Draft basado en versión '.$version->version_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft creado basado en la versión seleccionada',
                'draft' => $newDraft,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando draft desde versión: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteVersion($type, $versionId)
    {
        try {
            if ($type === 'requisitos') {
                $version = AyudaRequisitoVersion::findOrFail($versionId);
            } elseif ($type === 'condiciones') {
                $version = QuestionnaireConditionVersion::findOrFail($versionId);
            } else {
                return response()->json(['error' => 'Tipo de versión inválido'], 400);
            }

            if ($version->is_active) {
                return response()->json(['error' => 'No se puede eliminar una versión activa'], 400);
            }

            $version->delete();

            return response()->json([
                'success' => true,
                'message' => 'Versión eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
