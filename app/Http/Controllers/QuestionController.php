<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function index()
    {
        try {
            $query = Question::with(['categories', 'purposes', 'questionnaires.ayuda']);

            if (request()->has('search') && ! empty(request('search'))) {
                $search = request('search');
                $query->where(function ($q) use ($search) {
                    $q->where('text', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if (request()->has('categories') && is_array(request('categories')) && count(request('categories')) > 0) {
                $categories = request('categories');

                if (in_array('no_category', $categories)) {
                    $categoryIds = array_filter($categories, function ($cat) {
                        return $cat !== 'no_category';
                    });

                    if (count($categoryIds) > 0) {
                        $query->where(function ($q) use ($categoryIds) {
                            $q->whereDoesntHave('categories')
                                ->orWhereHas('categories', function ($subQ) use ($categoryIds) {
                                    $subQ->whereIn('question_categories.id', $categoryIds);
                                });
                        });
                    } else {
                        $query->whereDoesntHave('categories');
                    }
                } else {
                    $query->whereHas('categories', function ($q) use ($categories) {
                        $q->whereIn('question_categories.id', $categories);
                    });
                }
            }

            if (request()->has('type') && ! empty(request('type'))) {
                $query->where('type', request('type'));
            }

            if (request()->has('purposes') && is_array(request('purposes')) && count(request('purposes')) > 0) {
                $purposes = request('purposes');

                if (in_array('no_purpose', $purposes)) {
                    $purposeIds = array_filter($purposes, function ($purpose) {
                        return $purpose !== 'no_purpose';
                    });

                    if (count($purposeIds) > 0) {
                        $query->where(function ($q) use ($purposeIds) {
                            $q->whereDoesntHave('purposes')
                                ->orWhereHas('purposes', function ($subQ) use ($purposeIds) {
                                    $subQ->whereIn('question_purposes.id', $purposeIds);
                                });
                        });
                    } else {
                        $query->whereDoesntHave('purposes');
                    }
                } else {
                    $query->whereHas('purposes', function ($q) use ($purposes) {
                        $q->whereIn('question_purposes.id', $purposes);
                    });
                }
            }

            $questions = $query->paginate(15);

            if (request()->expectsJson()) {
                // 1) Trabaja sobre la colección interna del paginador
                $items = $questions->getCollection()->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'slug' => $question->slug,
                        'text' => $question->text,
                        'sub_text' => $question->sub_text,
                        // 👇 añade los nuevos campos
                        'text_conviviente' => $question->text_conviviente,
                        'sub_text_conviviente' => $question->sub_text_conviviente,
                        'type' => $question->type,
                        'options' => $question->options, // si lo guardas como JSON en BD, mejor castear a array en el modelo
                        'categories' => $question->categories,
                        'purposes' => $question->purposes,
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
                })->values();

                return response()->json([
                    'success' => true,
                    'questions' => $items,
                    'pagination' => [
                        'current_page' => $questions->currentPage(),
                        'last_page' => $questions->lastPage(),
                        'per_page' => $questions->perPage(),
                        'total' => $questions->total(),
                        'from' => $questions->firstItem(),
                        'to' => $questions->lastItem(),
                        'has_more_pages' => $questions->hasMorePages(),
                    ],
                ]);
            }

            $categories = QuestionCategory::getOrdered();
            $types = $this->getQuestionTypes();

            return view('admin.questions', compact('questions', 'categories', 'types'));
        } catch (\Exception $e) {
            Log::error('Error en QuestionController@index: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar las preguntas: '.$e->getMessage(),
                ], 500);
            }

            return view('admin.questions', [
                'questions' => collect(),
                'categories' => collect(),
                'types' => $this->getQuestionTypes(),
            ])->with('error', 'Error al cargar las preguntas');
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['questions' => []]);
        }

        $questions = Question::where('text', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(20)
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'text' => $q->text,
                    'type' => $q->type,
                    'options' => $q->options,
                    'slug' => $q->slug,
                ];
            });

        return response()->json(['questions' => $questions]);
    }

    public function create()
    {
        return view('admin.questions.create');
    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'slug' => 'required|string|max:255|unique:questions',
                'text' => 'required|string|max:1000',
                'type' => 'required|string|max:255',
                'sub_text' => 'nullable|string',
                'text_conviviente' => 'nullable|string|max:1000',
                'sub_text_conviviente' => 'nullable|string',
                'options' => 'nullable|array',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:question_categories,id',
                'purpose_ids' => 'nullable|array',
                'purpose_ids.*' => 'integer|exists:question_purposes,id',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return redirect()->route('admin.questions.index')->with('error', 'El slug está repetido y DEBE ser único');
            }

            $question = Question::create([
                'slug' => $request->slug,
                'text' => $request->text,
                'sub_text' => $request->sub_text,
                'text_conviviente' => $request->text_conviviente,
                'sub_text_conviviente' => $request->sub_text_conviviente,
                'type' => $request->type,
                'options' => $request->options,
            ]);

            // Asociar categorías si se proporcionan
            if ($request->has('category_ids') && is_array($request->category_ids)) {
                try {
                    $question->categories()->attach($request->category_ids);
                } catch (\Exception $e) {
                    Log::error('Error attaching categories: '.$e->getMessage());
                }
            }

            if ($request->has('purpose_ids') && is_array($request->purpose_ids)) {
                try {
                    $question->purposes()->attach($request->purpose_ids);
                } catch (\Exception $e) {
                    Log::error('Error attaching purposes: '.$e->getMessage());
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pregunta creada correctamente',
                    'question' => [
                        'id' => $question->id,
                        'slug' => $question->slug,
                        'text' => $question->text,
                        'sub_text' => $question->sub_text,
                        'text_conviviente' => $question->text_conviviente,
                        'sub_text_conviviente' => $question->sub_text_conviviente,
                        'type' => $question->type,
                        'options' => $question->options,
                    ],
                ]);
            }

            return redirect()->route('admin.questions.index')->with('success', 'Pregunta creada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error in store method: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la pregunta: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.questions.index')->with('error', 'Error al crear la pregunta.');
        }
    }

    public function edit($id)
    {
        $question = Question::findOrFail($id);

        return view('admin.questions.edit', compact('question'));
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Update request received', [
                'id' => $id,
                'data' => $request->all(),
            ]);

            $validator = Validator::make($request->all(), [
                'slug' => 'required|string|max:255|unique:questions,slug,'.$id,
                'text' => 'required|string|max:1000',
                'type' => 'required|string|max:255',
                'sub_text' => 'nullable|string',
                'text_conviviente' => 'nullable|string|max:1000',
                'sub_text_conviviente' => 'nullable|string',
                'options' => 'nullable|array',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:question_categories,id',
                'purpose_ids' => 'nullable|array',
                'purpose_ids.*' => 'integer|exists:question_purposes,id',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed in update', $validator->errors()->toArray());
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return redirect()->route('admin.questions.index')->with('error', 'Error de validación');
            }

            $question = Question::findOrFail($id);
            Log::info('Updating question', ['question_id' => $question->id]);

            $question->update([
                'slug' => $request->slug,
                'text' => $request->text,
                'sub_text' => $request->sub_text,
                'text_conviviente' => $request->text_conviviente,
                'sub_text_conviviente' => $request->sub_text_conviviente,
                'type' => $request->type,
                'options' => $request->options,
            ]);
            Log::info('Question updated successfully');

            // Actualizar categorías
            if ($request->has('category_ids') && is_array($request->category_ids)) {
                Log::info('Syncing categories', ['category_ids' => $request->category_ids]);
                $question->categories()->sync($request->category_ids);
                Log::info('Categories synced successfully');
            } else {
                Log::info('Detaching all categories');
                $question->categories()->detach();
            }

            if ($request->has('purpose_ids') && is_array($request->purpose_ids)) {
                Log::info('Syncing purposes', ['purpose_ids' => $request->purpose_ids]);
                $question->purposes()->sync($request->purpose_ids);
                Log::info('Purposes synced successfully');
            } else {
                Log::info('Detaching all purposes');
                $question->purposes()->detach();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pregunta actualizada correctamente',
                    'question' => [
                        'id' => $question->id,
                        'slug' => $question->slug,
                        'text' => $question->text,
                        'sub_text' => $question->sub_text,
                        'text_conviviente' => $question->text_conviviente,
                        'sub_text_conviviente' => $question->sub_text_conviviente,
                        'type' => $question->type,
                        'options' => $question->options,
                    ],
                ]);
            }

            return redirect()->route('admin.questions.index')->with('success', 'Pregunta actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error in update method: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la pregunta: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.questions.index')->with('error', 'Error al actualizar la pregunta.');
        }
    }

    public function destroy($id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pregunta eliminada correctamente',
                ]);
            }

            return redirect()->route('admin.questions.index')->with('success', 'Pregunta eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error in destroy method: '.$e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la pregunta: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('admin.questions.index')->with('error', 'Error al eliminar la pregunta.');
        }
    }

    /**
     * Obtener tipos de preguntas disponibles
     */
    private function getQuestionTypes(): array
    {
        return [
            'string' => 'Texto',
            'integer' => 'Número',
            'boolean' => 'Sí / No',
            'select' => 'Selección',
            'multiple' => 'Selección múltiple',
            'date' => 'Fecha',
            'info' => 'Informativa',
        ];
    }

    public function page()
    {
        $categories = QuestionCategory::getOrdered();
        $types = $this->getQuestionTypes();

        return view('admin.questions', compact('categories', 'types'));
    }
}
