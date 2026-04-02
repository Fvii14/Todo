<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\PosibleBeneficiario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PosiblesBeneficiariosController extends Controller
{
    /**
     * Muestra el formulario de selección de ayuda y el listado de posibles beneficiarios
     */
    public function index(Request $request)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        $ayudaId = $request->get('ayuda_id');
        $ayudas = Ayuda::orderBy('nombre_ayuda')->get();

        $posiblesBeneficiarios = collect();
        $ayudaSeleccionada = null;

        if ($ayudaId) {
            $ayudaSeleccionada = Ayuda::find($ayudaId);
            if ($ayudaSeleccionada) {
                $posiblesBeneficiarios = PosibleBeneficiario::where('ayuda_id', $ayudaId)
                    ->orderBy('nombre_completo')
                    ->paginate(50)
                    ->appends($request->query());
            }
        }

        return view('admin.marketing.posibles-beneficiarios', compact(
            'ayudas',
            'ayudaSeleccionada',
            'posiblesBeneficiarios',
            'ayudaId'
        ));
    }

    /**
     * Genera el reporte ejecutando el comando Artisan
     */
    public function generar(Request $request)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        $request->validate([
            'ayuda_id' => 'required|exists:ayudas,id',
        ]);

        $ayudaId = $request->input('ayuda_id');

        try {
            // Aumentar el tiempo máximo de ejecución para procesos largos
            set_time_limit(600); // 10 minutos
            ini_set('max_execution_time', 600);

            // Ejecutar comando (se ejecuta de forma síncrona pero con timeout aumentado)
            Artisan::call('marketing:evaluar-beneficiarios', [
                'ayuda_id' => $ayudaId,
                '--chunk' => 100,
            ]);

            $totalEncontrados = PosibleBeneficiario::where('ayuda_id', $ayudaId)->count();

            return redirect()
                ->route('posibles-beneficiarios.index', ['ayuda_id' => $ayudaId])
                ->with('success', "Reporte generado correctamente. Se encontraron {$totalEncontrados} posibles beneficiarios.");
        } catch (\Exception $e) {
            Log::error('Error al generar reporte de posibles beneficiarios: '.$e->getMessage(), [
                'ayuda_id' => $ayudaId,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('posibles-beneficiarios.index', ['ayuda_id' => $ayudaId])
                ->with('error', 'Error al generar el reporte: '.$e->getMessage());
        }
    }

    /**
     * Descarga el CSV con los posibles beneficiarios
     */
    public function descargarCsv(Request $request)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        $request->validate([
            'ayuda_id' => 'required|exists:ayudas,id',
        ]);

        $ayudaId = $request->input('ayuda_id');
        $ayuda = Ayuda::find($ayudaId);

        if (! $ayuda) {
            return redirect()
                ->route('posibles-beneficiarios.index')
                ->with('error', 'Ayuda no encontrada');
        }

        // Verificar que hay registros antes de generar el CSV
        $totalRegistros = PosibleBeneficiario::where('ayuda_id', $ayudaId)->count();

        if ($totalRegistros === 0) {
            return redirect()
                ->route('posibles-beneficiarios.index', ['ayuda_id' => $ayudaId])
                ->with('error', 'No hay posibles beneficiarios para descargar');
        }

        // Crear CSV
        $filename = 'posibles_beneficiarios_'.$ayuda->slug.'_'.date('Y-m-d_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($ayudaId) {
            $file = fopen('php://output', 'w');

            // Agregar BOM UTF-8 para Excel (debe ser lo primero que se escribe)
            fwrite($file, "\xEF\xBB\xBF");

            // Encabezados
            fputcsv($file, ['Nombre y Apellidos', 'Email', 'Teléfono', 'CCAA'], ';');

            // Usar cursor() para procesar registros uno por uno sin cargar todo en memoria
            foreach (PosibleBeneficiario::where('ayuda_id', $ayudaId)
                ->orderBy('nombre_completo')
                ->cursor() as $beneficiario) {
                fputcsv($file, [
                    $beneficiario->nombre_completo ?? '',
                    $beneficiario->email ?? '',
                    $beneficiario->telefono ?? '',
                    $beneficiario->ccaa ?? '',
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
