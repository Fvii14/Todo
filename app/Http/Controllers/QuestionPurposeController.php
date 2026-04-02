<?php

namespace App\Http\Controllers;

use App\Models\QuestionPurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionPurposeController extends Controller
{
    /**
     * Obtener todas las finalidades
     */
    public function index(Request $request)
    {
        try {
            $purposes = QuestionPurpose::orderBy('name')->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'purposes' => $purposes,
                ]);
            }

            return response()->json([
                'success' => true,
                'purposes' => $purposes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en QuestionPurposeController@index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las finalidades: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear una nueva finalidad
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:question_purposes,name',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $purpose = QuestionPurpose::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Finalidad creada correctamente',
                'purpose' => $purpose,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en QuestionPurposeController@store: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la finalidad: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar una finalidad
     */
    public function update(Request $request, QuestionPurpose $questionPurpose)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:question_purposes,name,'.$questionPurpose->id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $questionPurpose->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Finalidad actualizada correctamente',
                'purpose' => $questionPurpose,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en QuestionPurposeController@update: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la finalidad: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una finalidad
     */
    public function destroy(QuestionPurpose $questionPurpose)
    {
        try {
            // Verificar si hay preguntas asociadas
            if ($questionPurpose->questions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la finalidad porque tiene preguntas asociadas',
                ], 422);
            }

            $questionPurpose->delete();

            return response()->json([
                'success' => true,
                'message' => 'Finalidad eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en QuestionPurposeController@destroy: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la finalidad: '.$e->getMessage(),
            ], 500);
        }
    }
}
