<?php

namespace App\Http\Controllers;

use App\Enums\QuestionnaireTipo;
use App\Models\Ayuda;
use App\Models\Document;
use App\Models\Onboarder;
use App\Models\Organo;
use App\Models\Question;
use App\Models\Wizard;
use App\Services\WizardAyudaService;
use App\Services\WizardMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WizardController extends Controller
{
    protected $wizardAyudaService;

    protected $wizardMailService;

    public function __construct(WizardAyudaService $wizardAyudaService, WizardMailService $wizardMailService)
    {
        $this->wizardAyudaService = $wizardAyudaService;
        $this->wizardMailService = $wizardMailService;
    }

    public function index()
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para ver este wizard');
        }
        $wizards = Wizard::select([
            'id',
            'type',
            'user_id',
            'current_step',
            'status',
            'title',
            'description',
            'duplicated_from_id',
            'created_at',
            'updated_at',
            'duplicated_at',
            'duplication_reason',
        ])
            ->with('duplicatedFrom:id,title')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.wizards.index', compact('wizards'));
    }

    public function create()
    {
        $organos = Organo::all();
        $sectores = Ayuda::getSectores();

        return view('admin.wizards.create', compact('organos', 'sectores'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:ayuda,collector',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $wizard = Wizard::create([
                'type' => $request->type,
                'user_id' => Auth::id(),
                'data' => $request->data ?? [],
                'title' => $request->title,
                'description' => $request->description,
                'current_step' => 1,
                'status' => Wizard::STATUS_DRAFT,
            ]);

            return response()->json([
                'success' => true,
                'wizard' => $wizard,
                'message' => 'Wizard creado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show(Wizard $wizard)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para ver este wizard');
        }

        // Si el wizard está completado y es de tipo ayuda, cargar los datos de la ayuda existente
        if ($wizard->type === Wizard::TYPE_AYUDA && $wizard->isCompleted()) {
            $ayudaId = $wizard->getDataValue('ayuda_id');
            if ($ayudaId) {
                $ayuda = Ayuda::find($ayudaId);
                if ($ayuda) {
                    // Transformar la ayuda al formato del wizard y actualizar el wizard
                    $wizardData = $this->wizardAyudaService->transformAyudaToWizard($ayuda);
                    $wizard->data = array_merge($wizard->data ?? [], $wizardData);
                    $wizard->save();
                }
            }
        }

        $organos = Organo::all();
        $sectores = Ayuda::getSectores();
        $questionTypes = Question::$types;
        $questionSectores = Question::$sectores;
        $questionCategorias = Question::$categorias;

        // Datos específicos para wizard de mail
        $mailClasses = $this->wizardMailService->getAvailableMailClasses();
        $allQuestions = Question::all();
        $allDocuments = Document::all();

        return view('admin.wizards.show', compact(
            'wizard',
            'organos',
            'sectores',
            'questionTypes',
            'questionSectores',
            'questionCategorias',
            'mailClasses',
            'allQuestions',
            'allDocuments'
        ));
    }

    public function update(Request $request, Wizard $wizard): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'current_step' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|in:draft,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($wizard->type === 'collector' && $request->status === 'completed') {
                Wizard::withoutTimestamps(function () use ($wizard) {
                    Wizard::where('type', 'collector')
                        ->where('id', '!=', $wizard->id)
                        ->where('status', 'completed')
                        ->update(['status' => 'draft']);
                });
            }

            $wizard->update([
                'data' => $request->data,
                'current_step' => $request->current_step ?? $wizard->current_step,
                'status' => $request->status ?? $wizard->status,
            ]);

            return response()->json([
                'success' => true,
                'wizard' => $wizard,
                'message' => 'Wizard actualizado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function saveDraft(Request $request, Wizard $wizard): JsonResponse
    {
        try {
            $wizard->update([
                'data' => $request->data,
                'current_step' => $request->current_step ?? $wizard->current_step,
                'status' => Wizard::STATUS_DRAFT,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Borrador guardado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el borrador: '.$e->getMessage(),
            ], 500);
        }
    }

    public function complete(Request $request, Wizard $wizard): JsonResponse
    {
        Log::info('complete method called', ['wizard_id' => $wizard->id, 'wizard_type' => $wizard->type]);

        try {
            if ($wizard->type === Wizard::TYPE_AYUDA) {
                Log::info('Processing ayuda wizard');
                $result = $this->wizardAyudaService->transformWizardToAyuda($wizard);
            } elseif ($wizard->type === Wizard::TYPE_COLLECTOR) {
                Log::info('Processing mail wizard');
                $result = $this->wizardMailService->transformWizardToMail($wizard, $request->data ?? []);
            } else {
                Log::error('Unknown wizard type', ['type' => $wizard->type]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de wizard no soportado',
                ], 400);
            }

            Log::info('Wizard completed successfully', ['result' => $result]);

            $wizard->update([
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('complete method error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al completar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Wizard $wizard): JsonResponse
    {
        try {
            $wizard->delete();

            return response()->json([
                'success' => true,
                'message' => 'Wizard eliminado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getDataStructure(Request $request): JsonResponse
    {
        $type = $request->get('type');

        switch ($type) {
            case Wizard::TYPE_AYUDA:
                $structure = $this->wizardAyudaService->getExpectedDataStructure();
                break;
            case Wizard::TYPE_COLLECTOR:
                $structure = $this->wizardMailService->getExpectedDataStructure();
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de wizard no soportado',
                ], 400);
        }

        return response()->json([
            'success' => true,
            'structure' => $structure,
        ]);
    }

    /**
     * Obtiene una vista previa de usuarios según los criterios especificados
     */
    public function previewUsers(Request $request): JsonResponse
    {
        Log::info('previewUsers method called', ['request' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'criteria' => 'required|array',
        ]);

        if ($validator->fails()) {
            Log::error('previewUsers validation failed', ['errors' => $validator->errors()]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            Log::info('previewUsers calling getUsersByCriteria');
            $users = $this->wizardMailService->getUsersByCriteria($request->criteria);
            Log::info('previewUsers got users', ['count' => $users->count()]);

            return response()->json([
                'success' => true,
                'users' => $users->take(10)->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            Log::error('previewUsers error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la vista previa: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getFormData(): JsonResponse
    {
        try {
            $organos = Organo::all();
            $sectores = Ayuda::getSectores();
            $questionTypes = Question::$types;

            return response()->json([
                'success' => true,
                'data' => [
                    'organos' => $organos,
                    'sectores' => $sectores,
                    'questionTypes' => $questionTypes,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos: '.$e->getMessage(),
            ], 500);
        }
    }

    public function searchQuestions(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);
        $type = $request->get('type', 'all');
        $categories = $request->get('categories', []);
        $purposes = $request->get('purposes', []);

        $questions = Question::query()
            ->with(['questionnaires.ayuda', 'purposes', 'categories'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('text', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(! empty($categories), function ($q) use ($categories) {
                if (in_array('no_category', $categories)) {
                    $q->where(function ($query) use ($categories) {
                        $query->whereDoesntHave('categories')
                            ->orWhereHas('categories', function ($subQuery) use ($categories) {
                                $subQuery->whereIn('question_categories.id', array_filter($categories, function ($cat) {
                                    return $cat !== 'no_category';
                                }));
                            });
                    });
                } else {
                    $q->whereHas('categories', function ($query) use ($categories) {
                        $query->whereIn('question_categories.id', $categories);
                    });
                }
            })
            ->when($type === 'collector', function ($q) {
                $q->whereIn('id', function ($subQ) {
                    $subQ->select('question_id')
                        ->from('questionnaire_questions')
                        ->whereIn('questionnaire_id', function ($subSubQ) {
                            $subSubQ->select('id')
                                ->from('questionnaires')
                                ->where(function ($qq) {
                                    $qq->where('tipo', QuestionnaireTipo::COLLECTOR)
                                        ->orWhere('tipo', 'collector');
                                });
                        });
                });
            })
            ->when($type === 'non-collector', function ($q) {
                $q->whereNotIn('id', function ($subQ) {
                    $subQ->select('question_id')
                        ->from('questionnaire_questions')
                        ->whereIn('questionnaire_id', function ($subSubQ) {
                            $subSubQ->select('id')
                                ->from('questionnaires')
                                ->where(function ($qq) {
                                    $qq->where('tipo', QuestionnaireTipo::COLLECTOR)
                                        ->orWhere('tipo', 'collector');
                                });
                        });
                });
            })
            ->when(! empty($purposes), function ($q) use ($purposes) {
                if (in_array('no_purpose', $purposes)) {
                    $q->where(function ($query) use ($purposes) {
                        $query->whereDoesntHave('purposes')
                            ->orWhereHas('purposes', function ($subQuery) use ($purposes) {
                                $subQuery->whereIn('question_purposes.id', array_filter($purposes, function ($purpose) {
                                    return $purpose !== 'no_purpose';
                                }));
                            });
                    });
                } else {
                    $q->whereHas('purposes', function ($query) use ($purposes) {
                        $query->whereIn('question_purposes.id', $purposes);
                    });
                }
            })
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'questions' => $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'slug' => $question->slug,
                    'text' => $question->text,
                    'type' => $question->type,
                    'options' => $question->options,
                    'categories' => $question->categories->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'description' => $category->description,
                        ];
                    }),
                    'purposes' => $question->purposes->map(function ($purpose) {
                        return [
                            'id' => $purpose->id,
                            'name' => $purpose->name,
                            'description' => $purpose->description,
                        ];
                    }),
                    'questionnaires' => $question->questionnaires->map(function ($questionnaire) {
                        return [
                            'id' => $questionnaire->id,
                            'name' => $questionnaire->name,
                            'tipo' => $questionnaire->tipo,
                            'active' => $questionnaire->active,
                            'ayuda' => $questionnaire->ayuda ? [
                                'id' => $questionnaire->ayuda->id,
                                'nombre_ayuda' => $questionnaire->ayuda->nombre_ayuda,
                                'sector' => $questionnaire->ayuda->sector,
                            ] : null,
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function searchCollectorQuestions(Request $request): JsonResponse
    {
        return $this->searchQuestions($request->merge(['type' => 'collector']));
    }

    public function searchNonCollectorQuestions(Request $request): JsonResponse
    {
        return $this->searchQuestions($request->merge(['type' => 'non-collector']));
    }

    public function createQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:questions,slug',
            'text' => 'required|string',
            'sub_text' => 'nullable|string',
            'type' => 'required|string|in:'.implode(',', array_keys(Question::$types)),
            'options' => 'nullable|array',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:question_categories,id',
            'purpose_ids' => 'nullable|array',
            'purpose_ids.*' => 'integer|exists:question_purposes,id',
        ]);

        try {
            $question = Question::create([
                'slug' => $request->slug,
                'text' => $request->text,
                'sub_text' => $request->sub_text,
                'type' => $request->type,
                'options' => $request->options,
            ]);

            // Asociar categorías
            if ($request->has('category_ids') && is_array($request->category_ids)) {
                $question->categories()->attach($request->category_ids);
            }

            if ($request->has('purpose_ids') && is_array($request->purpose_ids)) {
                $question->purposes()->attach($request->purpose_ids);
            }

            return response()->json([
                'success' => true,
                'question' => $question->load('categories')->only(['id', 'slug', 'text', 'type', 'options', 'categories']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la pregunta: '.$e->getMessage(),
            ], 500);
        }
    }

    public function saveOnboarderConfig(Request $request, int $wizardId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.name' => 'required|string',
            'sections.*.description' => 'nullable|string',
            'sections.*.order' => 'required|integer',
            'sections.*.skip_condition' => 'nullable|array',
            'sections.*.is_required' => 'boolean',
            'sections.*.is_skippeable' => 'boolean',
            'sections.*.questions' => 'required|array',
            'sections.*.questions.*.question_id' => 'required|exists:questions,id',
            'sections.*.questions.*.order' => 'required|integer',
            'sections.*.questions.*.condition' => 'nullable|array',
            'sections.*.questions.*.screen' => 'nullable|integer|min:0',
            'sections.*.questions.*.required_condition' => 'nullable|array',
            'sections.*.questions.*.optional_condition' => 'nullable|array',
            'sections.*.questions.*.block_if_bankflip_filled' => 'boolean',
            'sections.*.questions.*.hide_if_bankflip_filled' => 'boolean',
            'sections.*.questions.*.show_if_bankflip_filled' => 'nullable',
            'sections.*.questions.*.is_builder' => 'boolean',
            'sections.*.questions.*.conditional_options' => 'nullable|array',
            'sections.*.questions.*.selected_options' => 'nullable|array',
            'conviviente_types' => 'required|array',
            'conviviente_types.*.name' => 'required|string',
            'conviviente_types.*.description' => 'nullable|string',
            'conviviente_types.*.icon' => 'nullable|string',
            'conviviente_types.*.order' => 'required|integer',
            'conviviente_types.*.sections' => 'required|array',
            'conviviente_types.*.sections.*.name' => 'required|string',
            'conviviente_types.*.sections.*.description' => 'nullable|string',
            'conviviente_types.*.sections.*.order' => 'required|integer',
            'conviviente_types.*.sections.*.skip_condition' => 'nullable|array',
            'conviviente_types.*.sections.*.is_required' => 'boolean',
            'conviviente_types.*.sections.*.is_skippeable' => 'boolean',
            'conviviente_types.*.sections.*.questions' => 'required|array',
            'conviviente_types.*.sections.*.questions.*.question_id' => 'required|exists:questions,id',
            'conviviente_types.*.sections.*.questions.*.order' => 'required|integer',
            'conviviente_types.*.sections.*.questions.*.condition' => 'nullable|array',
            'conviviente_types.*.sections.*.questions.*.screen' => 'nullable|integer|min:0',
            'conviviente_types.*.sections.*.questions.*.required_condition' => 'nullable|array',
            'conviviente_types.*.sections.*.questions.*.optional_condition' => 'nullable|array',
            'conviviente_types.*.sections.*.questions.*.block_if_bankflip_filled' => 'boolean',
            'conviviente_types.*.sections.*.questions.*.hide_if_bankflip_filled' => 'boolean',
            'conviviente_types.*.sections.*.questions.*.show_if_bankflip_filled' => 'nullable',
            'conviviente_types.*.sections.*.questions.*.is_builder' => 'boolean',
            'conviviente_types.*.sections.*.questions.*.conditional_options' => 'nullable|array',
            'conviviente_types.*.sections.*.questions.*.selected_options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $wizard = Wizard::findOrFail($wizardId);

            $onboarder = Onboarder::where('wizard_id', $wizardId)->first();
            if (! $onboarder) {
                $onboarder = Onboarder::create([
                    'wizard_id' => $wizardId,
                    'user_id' => Auth::user()->id ?? $wizard->user_id,
                    'status' => 'completed',
                    'data' => [],
                ]);
            }

            $onboarder->sections()->delete();

            foreach ($request->sections as $sectionData) {
                if (! empty($sectionData['skip_condition'])) {
                    $sectionData['skip_condition'] = $this->normalizeConditionValue(
                        $sectionData['skip_condition'],
                        'solicitante',
                        null
                    );
                }
                $section = $onboarder->sections()->create([
                    'name' => $sectionData['name'],
                    'description' => $sectionData['description'] ?? null,
                    'order' => $sectionData['order'],
                    'skip_condition' => $sectionData['skip_condition'] ?? null,
                    'is_required' => $sectionData['is_required'] ?? true,
                    'is_skippeable' => $sectionData['is_skippeable'] ?? false,
                ]);

                foreach ($sectionData['questions'] as $questionData) {
                    if (! empty($questionData['condition'])) {
                        $questionData['condition'] = $this->normalizeConditionValue(
                            $questionData['condition'],
                            'solicitante',
                            null
                        );
                    }
                    if (! empty($questionData['required_condition'])) {
                        $questionData['required_condition'] = $this->normalizeConditionValue(
                            $questionData['required_condition'],
                            'solicitante',
                            null
                        );
                    }
                    if (! empty($questionData['optional_condition'])) {
                        $questionData['optional_condition'] = $this->normalizeConditionValue(
                            $questionData['optional_condition'],
                            'solicitante',
                            null
                        );
                    }
                    $requiredCondition = $questionData['required_condition'] ?? null;
                    if (($questionData['isRequired'] ?? false) && ! $requiredCondition) {
                        $requiredCondition = ['isDefault' => true];
                    }

                    $section->questions()->create([
                        'onboarder_id' => $onboarder->id,
                        'question_id' => $questionData['question_id'],
                        'order' => $questionData['order'],
                        'screen' => $questionData['screen'] ?? 0,
                        'condition' => $questionData['condition'] ?? null,
                        'required_condition' => $requiredCondition,
                        'optional_condition' => $questionData['optional_condition'] ?? null,
                        'block_if_bankflip_filled' => $questionData['block_if_bankflip_filled'] ?? false,
                        'hide_if_bankflip_filled' => $questionData['hide_if_bankflip_filled'] ?? false,
                        'show_if_bankflip_filled' => $questionData['show_if_bankflip_filled'] ?? null,
                        'is_builder' => $questionData['is_builder'] ?? false,
                        'conditional_options' => $questionData['conditional_options'] ?? null,
                        'selected_options' => $questionData['selected_options'] ?? null,
                    ]);
                }
            }

            if ($request->conviviente_types && count($request->conviviente_types) > 0) {
                foreach ($request->conviviente_types as $typeIndex => $typeData) {
                    $convivienteType = $onboarder->convivienteTypes()->updateOrCreate(
                        ['name' => $typeData['name']],
                        [
                            'description' => $typeData['description'] ?? '',
                            'icon' => $typeData['icon'] ?? 'fas fa-user',
                            'order' => $typeData['order'],
                        ]
                    );

                    $convivienteType->sections()->delete();

                    if (isset($typeData['sections'])) {
                        foreach ($typeData['sections'] as $sectionData) {
                            if (! empty($sectionData['skip_condition'])) {
                                $sectionData['skip_condition'] = $this->normalizeConditionValue(
                                    $sectionData['skip_condition'],
                                    'conviviente',
                                    $typeIndex
                                );
                            }
                            $section = $convivienteType->sections()->create([
                                'onboarder_id' => $onboarder->id,
                                'name' => $sectionData['name'],
                                'description' => $sectionData['description'] ?? null,
                                'order' => $sectionData['order'],
                                'skip_condition' => $sectionData['skip_condition'] ?? null,
                                'is_required' => $sectionData['is_required'] ?? true,
                                'is_skippeable' => $sectionData['is_skippeable'] ?? false,
                            ]);

                            if (isset($sectionData['questions'])) {
                                foreach ($sectionData['questions'] as $questionData) {
                                    if (! empty($questionData['condition'])) {
                                        $questionData['condition'] = $this->normalizeConditionValue(
                                            $questionData['condition'],
                                            'conviviente',
                                            $typeIndex
                                        );
                                    }
                                    if (! empty($questionData['required_condition'])) {
                                        $questionData['required_condition'] = $this->normalizeConditionValue(
                                            $questionData['required_condition'],
                                            'conviviente',
                                            $typeIndex
                                        );
                                    }
                                    if (! empty($questionData['optional_condition'])) {
                                        $questionData['optional_condition'] = $this->normalizeConditionValue(
                                            $questionData['optional_condition'],
                                            'conviviente',
                                            $typeIndex
                                        );
                                    }
                                    $requiredCondition = $questionData['required_condition'] ?? null;
                                    if (($questionData['isRequired'] ?? false) && ! $requiredCondition) {
                                        $requiredCondition = ['isDefault' => true];
                                    }

                                    $section->questions()->create([
                                        'onboarder_id' => $onboarder->id,
                                        'question_id' => $questionData['question_id'],
                                        'order' => $questionData['order'],
                                        'screen' => $questionData['screen'] ?? 0,
                                        'condition' => $questionData['condition'] ?? null,
                                        'required_condition' => $requiredCondition,
                                        'optional_condition' => $questionData['optional_condition'] ?? null,
                                        'block_if_bankflip_filled' => $questionData['block_if_bankflip_filled'] ?? false,
                                        'hide_if_bankflip_filled' => $questionData['hide_if_bankflip_filled'] ?? false,
                                        'show_if_bankflip_filled' => $questionData['show_if_bankflip_filled'] ?? null,
                                        'is_builder' => $questionData['is_builder'] ?? false,
                                        'conditional_options' => $questionData['conditional_options'] ?? null,
                                        'selected_options' => $questionData['selected_options'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Actualizar el wizard con la configuración del onboarder
            $wizard->update([
                'data' => [
                    'sections' => $request->sections,
                    'conviviente_types' => $request->conviviente_types,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración de onboarder guardada correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la configuración: '.$e->getMessage(),
            ], 500);
        }
    }

    private function normalizeCondition(array $condition, string $defaultPersonType, $defaultPersonIndex = null): array
    {
        if (! isset($condition['personType'])) {
            $condition['personType'] = $defaultPersonType;
        }
        if ($condition['personType'] === 'conviviente' && ! isset($condition['personIndex'])) {
            $condition['personIndex'] = $defaultPersonIndex;
        }

        return $condition;
    }

    private function normalizeConditionValue($conditionValue, string $defaultPersonType, $defaultPersonIndex = null)
    {
        if (is_string($conditionValue)) {
            try {
                $decoded = json_decode($conditionValue, true);
                if (is_array($decoded)) {
                    $normalized = $this->normalizeCondition($decoded, $defaultPersonType, $defaultPersonIndex);

                    return $normalized;
                }
            } catch (\Throwable $e) {
                return $conditionValue;
            }
        } elseif (is_array($conditionValue)) {
            return $this->normalizeCondition($conditionValue, $defaultPersonType, $defaultPersonIndex);
        }

        return $conditionValue;
    }

    public function getOnboarderConfig(int $wizardId): JsonResponse
    {
        $wizard = Wizard::with([
            'sections.questions.question',
            'convivienteTypes.sections.questions.question',
        ])->findOrFail($wizardId);

        return response()->json([
            'success' => true,
            'data' => $wizard,
        ]);
    }

    public function markAsCompleted(Request $request, int $wizardId): JsonResponse
    {
        try {
            $wizard = Wizard::findOrFail($wizardId);

            $wizard->update([
                'status' => Wizard::STATUS_COMPLETED,
                'current_step' => 4,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wizard completado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function duplicate(Request $request, $id)
    {
        try {
            $originalWizard = Wizard::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'duplication_reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $duplicatedWizard = Wizard::create([
                'type' => $originalWizard->type,
                'user_id' => Auth::user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'data' => $originalWizard->data,
                'current_step' => 1,
                'status' => Wizard::STATUS_DRAFT,
                'duplicated_from_id' => $originalWizard->id,
                'duplication_reason' => $request->duplication_reason,
                'duplicated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wizard duplicado correctamente',
                'wizard' => $duplicatedWizard,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function export(Wizard $wizard): \Symfony\Component\HttpFoundation\Response
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para exportar este wizard');
        }

        try {
            $exportData = [
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'wizard' => [
                    'type' => $wizard->type,
                    'title' => $wizard->title,
                    'description' => $wizard->description,
                    'data' => $wizard->data,
                    'current_step' => $wizard->current_step,
                    'status' => $wizard->status,
                ],
            ];

            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $filename = 'wizard_'.($wizard->title ? Str::slug($wizard->title) : $wizard->id).'_'.now()->format('Y-m-d_His').'.json';

            return response($json)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        } catch (\Exception $e) {
            Log::error('Error exportando wizard', [
                'wizard_id' => $wizard->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al exportar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request): JsonResponse
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para importar wizards');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimetypes:application/json,text/json,text/plain|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            $importData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo JSON no es válido: '.json_last_error_msg(),
                ], 400);
            }

            if (! isset($importData['wizard']) || ! isset($importData['wizard']['type'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no contiene la estructura de wizard válida',
                ], 400);
            }

            $wizard = Wizard::create([
                'type' => $importData['wizard']['type'],
                'user_id' => Auth::id(),
                'title' => $importData['wizard']['title'] ?? 'Wizard importado',
                'description' => $importData['wizard']['description'] ?? null,
                'data' => $importData['wizard']['data'] ?? [],
                'current_step' => $importData['wizard']['current_step'] ?? 1,
                'status' => Wizard::STATUS_DRAFT,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wizard importado correctamente',
                'wizard' => [
                    'id' => $wizard->id,
                    'title' => $wizard->title,
                    'type' => $wizard->type,
                    'status' => $wizard->status,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error importando wizard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al importar el wizard: '.$e->getMessage(),
            ], 500);
        }
    }
}
