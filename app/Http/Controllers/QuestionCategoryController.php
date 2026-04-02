<?php

namespace App\Http\Controllers;

use App\Models\QuestionCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionCategoryController extends Controller
{
    /**
     * Obtener todas las categorías
     */
    public function index(Request $request): JsonResponse
    {
        $hierarchical = $request->get('hierarchical', false);

        if ($hierarchical) {
            $categories = QuestionCategory::getHierarchical();
        } else {
            $categories = QuestionCategory::getOrdered();
        }

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Crear una nueva categoría
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:question_categories,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:question_categories,id',
        ]);

        $category = QuestionCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->get('is_active', true),
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada correctamente',
            'category' => $category->load('parent'),
        ], 201);
    }

    /**
     * Actualizar una categoría
     */
    public function update(Request $request, QuestionCategory $questionCategory): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:question_categories,name,'.$questionCategory->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:question_categories,id',
        ]);

        if ($request->parent_id === $questionCategory->id) {
            return response()->json([
                'success' => false,
                'message' => 'Una categoría no puede ser padre de sí misma',
            ], 422);
        }

        $questionCategory->update($request->only(['name', 'description', 'is_active', 'parent_id']));

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada correctamente',
            'category' => $questionCategory->load('parent'),
        ]);
    }

    /**
     * Eliminar una categoría
     */
    public function destroy(QuestionCategory $questionCategory): JsonResponse
    {
        // Verificar si hay preguntas usando esta categoría
        if ($questionCategory->questions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene preguntas asociadas',
            ], 422);
        }

        if ($questionCategory->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene subcategorías. Elimine primero las subcategorías.',
            ], 422);
        }

        $questionCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
}
