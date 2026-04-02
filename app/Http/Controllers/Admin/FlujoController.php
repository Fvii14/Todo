<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ayuda;
use App\Models\Estado;
use App\Models\Fase;
use App\Models\Transicion;
use Illuminate\Http\Request;

class FlujoController extends Controller
{
    /**
     * Mostrar la vista principal de gestión de flujos
     */
    public function index()
    {
        $ayudas = Ayuda::select('id', 'nombre_ayuda as nombre')
            ->whereNotNull('id')
            ->get()
            ->unique('id')
            ->values();
        $estados = Estado::all();
        $fases = Fase::all();

        return view('admin.flujos-tramitacion', compact('ayudas', 'estados', 'fases'));
    }

    /**
     * Obtener flujos por ayuda
     */
    public function getFlujosPorAyuda(Request $request)
    {
        $ayudaId = $request->input('ayuda_id');

        if (! $ayudaId) {
            return response()->json(['error' => 'Ayuda no especificada'], 400);
        }

        $flujos = Transicion::porAyuda($ayudaId)
            ->with(['estadoOrigen', 'estadoDestino', 'faseOrigen', 'faseDestino', 'ayuda'])
            ->orderBy('tipo')
            ->orderBy('estado_origen')
            ->orderBy('fase_origen')
            ->get();

        return response()->json([
            'success' => true,
            'flujos' => $flujos,
        ]);
    }

    /**
     * Crear un nuevo flujo
     */
    public function store(Request $request)
    {
        $request->validate([
            'ayuda_id' => 'required|integer|exists:ayudas,id',
            'tipo' => 'required|in:ambos',
            'estado_origen' => 'required|string|exists:estados,slug',
            'estado_destino' => 'required|string|exists:estados,slug',
            'fase_origen' => 'nullable|string|exists:fase,slug',
            'fase_destino' => 'nullable|string|exists:fase,slug',
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Los estados son obligatorios, las fases son opcionales
        // La validación de campos requeridos se maneja en las reglas de validación

        // Verificar que no exista ya este flujo
        $flujoExistente = Transicion::where('ayuda_id', $request->ayuda_id)
            ->where('tipo', $request->tipo)
            ->where('estado_origen', $request->estado_origen)
            ->where('estado_destino', $request->estado_destino)
            ->where('fase_origen', $request->fase_origen)
            ->where('fase_destino', $request->fase_destino)
            ->first();

        if ($flujoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Este flujo ya existe para esta ayuda',
            ], 422);
        }

        $flujo = Transicion::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Flujo creado correctamente',
            'flujo' => $flujo->load(['estadoOrigen', 'estadoDestino', 'faseOrigen', 'faseDestino', 'ayuda']),
        ]);
    }

    /**
     * Actualizar un flujo existente
     */
    public function update(Request $request, Transicion $flujo)
    {
        $request->validate([
            'tipo' => 'required|in:ambos',
            'estado_origen' => 'required|string|exists:estados,slug',
            'estado_destino' => 'required|string|exists:estados,slug',
            'fase_origen' => 'nullable|string|exists:fase,slug',
            'fase_destino' => 'nullable|string|exists:fase,slug',
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Los estados son obligatorios, las fases son opcionales
        // La validación de campos requeridos se maneja en las reglas de validación

        $flujo->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Flujo actualizado correctamente',
            'flujo' => $flujo->load(['estadoOrigen', 'estadoDestino', 'faseOrigen', 'faseDestino', 'ayuda']),
        ]);
    }

    /**
     * Eliminar un flujo
     */
    public function destroy(Transicion $flujo)
    {
        $flujo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flujo eliminado correctamente',
        ]);
    }

    /**
     * Obtener estados disponibles para una ayuda específica
     */
    public function getEstadosDisponibles(Request $request)
    {
        $ayudaId = $request->input('ayuda_id');

        if (! $ayudaId) {
            return response()->json(['error' => 'Ayuda no especificada'], 400);
        }

        // Obtener estados que ya tienen transiciones definidas para esta ayuda
        $estadosConFlujos = Transicion::porAyuda($ayudaId)
            ->whereNotNull('estado_origen')
            ->pluck('estado_origen')
            ->unique();

        $estados = Estado::whereNotIn('slug', $estadosConFlujos)->get();

        return response()->json([
            'success' => true,
            'estados' => $estados,
        ]);
    }

    /**
     * Obtener fases disponibles para una ayuda específica
     */
    public function getFasesDisponibles(Request $request)
    {
        $ayudaId = $request->input('ayuda_id');

        if (! $ayudaId) {
            return response()->json(['error' => 'Ayuda no especificada'], 400);
        }

        // Obtener fases que ya tienen transiciones definidas para esta ayuda
        $fasesConFlujos = Transicion::porAyuda($ayudaId)
            ->whereNotNull('fase_origen')
            ->pluck('fase_origen')
            ->unique();

        $fases = Fase::whereNotIn('slug', $fasesConFlujos)->get();

        return response()->json([
            'success' => true,
            'fases' => $fases,
        ]);
    }

    /**
     * Copiar flujos de una ayuda a otras
     */
    public function copiarFlujos(Request $request)
    {
        $request->validate([
            'ayuda_origen_id' => 'required|exists:ayudas,id',
            'ayudas_destino_ids' => 'required|array|min:1',
            'ayudas_destino_ids.*' => 'exists:ayudas,id',
            'sobrescribir' => 'boolean',
        ]);

        $ayudaOrigenId = $request->ayuda_origen_id;
        $ayudasDestinoIds = $request->ayudas_destino_ids;
        $sobrescribir = $request->boolean('sobrescribir', false);

        $flujosOrigen = Transicion::where('ayuda_id', $ayudaOrigenId)->get();

        if ($flujosOrigen->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'La ayuda origen no tiene flujos configurados',
            ], 400);
        }

        $resultados = [];
        $errores = [];

        foreach ($ayudasDestinoIds as $ayudaDestinoId) {
            $flujosCreados = 0;
            $flujosExistentes = 0;
            $erroresAyuda = [];

            foreach ($flujosOrigen as $flujo) {
                try {
                    $existente = Transicion::where('ayuda_id', $ayudaDestinoId)
                        ->where('tipo', $flujo->tipo)
                        ->where('estado_origen', $flujo->estado_origen)
                        ->where('estado_destino', $flujo->estado_destino)
                        ->where('fase_origen', $flujo->fase_origen)
                        ->where('fase_destino', $flujo->fase_destino)
                        ->first();

                    if ($existente) {
                        if ($sobrescribir) {
                            $existente->delete();
                        } else {
                            $flujosExistentes++;

                            continue;
                        }
                    }

                    Transicion::create([
                        'ayuda_id' => $ayudaDestinoId,
                        'tipo' => $flujo->tipo,
                        'estado_origen' => $flujo->estado_origen,
                        'estado_destino' => $flujo->estado_destino,
                        'fase_origen' => $flujo->fase_origen,
                        'fase_destino' => $flujo->fase_destino,
                        'descripcion' => $flujo->descripcion,
                    ]);

                    $flujosCreados++;

                } catch (\Exception $e) {
                    $erroresAyuda[] = "Error al copiar flujo de '{$flujo->estado_origen}' a '{$flujo->estado_destino}': ".$e->getMessage();
                }
            }

            $resultados[] = [
                'ayuda_id' => $ayudaDestinoId,
                'flujos_creados' => $flujosCreados,
                'flujos_existentes' => $flujosExistentes,
                'errores' => $erroresAyuda,
            ];

            if (! empty($erroresAyuda)) {
                $errores = array_merge($errores, $erroresAyuda);
            }
        }

        $totalCreados = array_sum(array_column($resultados, 'flujos_creados'));
        $totalExistentes = array_sum(array_column($resultados, 'flujos_existentes'));

        return response()->json([
            'success' => true,
            'message' => "Copia completada. {$totalCreados} flujos creados, {$totalExistentes} ya existían.",
            'resultados' => $resultados,
            'errores' => $errores,
        ]);
    }

    /**
     * Obtener todas las ayudas para copiar
     */
    public function getAyudasParaCopiar()
    {
        $ayudas = Ayuda::select('id', 'nombre_ayuda as nombre')
            ->whereNotNull('id')
            ->get()
            ->unique('id')
            ->values();

        return response()->json([
            'success' => true,
            'ayudas' => $ayudas,
        ]);
    }

    /**
     * Obtener flujos para vista previa
     */
    public function getFlujosParaVistaPrevia(Request $request)
    {
        $ayudaId = $request->input('ayuda_id');

        if (! $ayudaId) {
            return response()->json([
                'success' => false,
                'message' => 'Ayuda no especificada',
            ], 400);
        }

        $flujos = Transicion::where('ayuda_id', $ayudaId)
            ->with(['estadoOrigen', 'estadoDestino', 'faseOrigen', 'faseDestino'])
            ->get();

        return response()->json([
            'success' => true,
            'flujos' => $flujos,
        ]);
    }
}
