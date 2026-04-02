<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ayuda;
use App\Models\AyudaPreRequisito;
use App\Models\Question;
use App\Services\PreRequisiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AyudaPreRequisitoController extends Controller
{
    protected $preRequisiteService;

    public function __construct(PreRequisiteService $preRequisiteService)
    {
        $this->preRequisiteService = $preRequisiteService;
    }

    public function index(Request $request, int $ayudaId): JsonResponse
    {
        try {
            $preRequisitos = AyudaPreRequisito::where('ayuda_id', $ayudaId)
                ->with(['question', 'groupRules.question'])
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'pre_requisitos' => $preRequisitos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los pre-requisitos: '.$e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, int $ayudaId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => ['required', Rule::in(['simple', 'group', 'complex'])],
                'target_type' => ['required', Rule::in(['solicitante', 'conviviente', 'unidad_convivencia', 'any_conviviente'])],
                'target_conviviente_type' => 'nullable|string',
                'question_id' => 'nullable|exists:questions,id',
                'operator' => 'nullable|string',
                'value' => 'nullable',
                'value2' => 'nullable',
                'group_logic' => 'nullable|in:AND,OR',
                'is_required' => 'boolean',
                'error_message' => 'nullable|string',
                'active' => 'boolean',
                'rules' => 'nullable|array',
                'rules.*.question_id' => 'required_with:rules|exists:questions,id',
                'rules.*.operator' => 'required_with:rules|string',
                'rules.*.value' => 'required_with:rules',
                'rules.*.value2' => 'nullable',
            ]);

            $ayuda = Ayuda::findOrFail($ayudaId);

            $preRequisitoData = array_merge($validated, [
                'ayuda_id' => $ayudaId,
                'order' => AyudaPreRequisito::where('ayuda_id', $ayudaId)->max('order') + 1,
            ]);

            if ($validated['type'] === 'simple') {
                $preRequisito = $this->preRequisiteService->createSimplePreRequisite($preRequisitoData);
            } elseif ($validated['type'] === 'group') {
                $rules = $validated['rules'] ?? [];
                $preRequisito = $this->preRequisiteService->createGroupPreRequisite($preRequisitoData, $rules);
            } else {
                $preRequisito = $this->preRequisiteService->createSimplePreRequisite($preRequisitoData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pre-requisito creado correctamente',
                'pre_requisito' => $preRequisito->load(['question', 'groupRules.question']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pre-requisito: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show(int $ayudaId, int $id): JsonResponse
    {
        try {
            $preRequisito = AyudaPreRequisito::where('ayuda_id', $ayudaId)
                ->where('id', $id)
                ->with(['question', 'groupRules.question'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'pre_requisito' => $preRequisito,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pre-requisito no encontrado',
            ], 404);
        }
    }

    public function update(Request $request, int $ayudaId, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'type' => ['sometimes', Rule::in(['simple', 'group', 'complex'])],
                'target_type' => ['sometimes', Rule::in(['solicitante', 'conviviente', 'unidad_convivencia', 'any_conviviente'])],
                'target_conviviente_type' => 'nullable|string',
                'question_id' => 'nullable|exists:questions,id',
                'operator' => 'nullable|string',
                'value' => 'nullable',
                'value2' => 'nullable',
                'group_logic' => 'nullable|in:AND,OR',
                'is_required' => 'sometimes|boolean',
                'error_message' => 'nullable|string',
                'active' => 'sometimes|boolean',
                'rules' => 'nullable|array',
                'rules.*.question_id' => 'required_with:rules|exists:questions,id',
                'rules.*.operator' => 'required_with:rules|string',
                'rules.*.value' => 'required_with:rules',
                'rules.*.value2' => 'nullable',
            ]);

            $preRequisito = AyudaPreRequisito::where('ayuda_id', $ayudaId)
                ->where('id', $id)
                ->firstOrFail();

            $preRequisito->update($validated);

            if ($preRequisito->type === 'group' && isset($validated['rules'])) {
                $preRequisito->groupRules()->delete();
                foreach ($validated['rules'] as $index => $rule) {
                    $preRequisito->groupRules()->create(array_merge($rule, [
                        'order' => $index,
                    ]));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pre-requisito actualizado correctamente',
                'pre_requisito' => $preRequisito->load(['question', 'groupRules.question']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pre-requisito: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $ayudaId, int $id): JsonResponse
    {
        try {
            $preRequisito = AyudaPreRequisito::where('ayuda_id', $ayudaId)
                ->where('id', $id)
                ->firstOrFail();

            $preRequisito->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pre-requisito eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pre-requisito: '.$e->getMessage(),
            ], 500);
        }
    }

    public function checkEligibility(Request $request, int $ayudaId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $user = \App\Models\User::findOrFail($validated['user_id']);
            $result = $this->preRequisiteService->checkUserEligibility($user, $ayudaId);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar elegibilidad: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableQuestions(): JsonResponse
    {
        try {
            $questions = Question::with(['categories', 'purposes'])
                ->where('active', true)
                ->orderBy('text')
                ->get();

            return response()->json([
                'success' => true,
                'questions' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las preguntas: '.$e->getMessage(),
            ], 500);
        }
    }

    public function reorder(Request $request, int $ayudaId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pre_requisitos' => 'required|array',
                'pre_requisitos.*.id' => 'required|exists:ayuda_pre_requisitos,id',
                'pre_requisitos.*.order' => 'required|integer|min:0',
            ]);

            foreach ($validated['pre_requisitos'] as $item) {
                AyudaPreRequisito::where('id', $item['id'])
                    ->where('ayuda_id', $ayudaId)
                    ->update(['order' => $item['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado correctamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el orden: '.$e->getMessage(),
            ], 500);
        }
    }
}
