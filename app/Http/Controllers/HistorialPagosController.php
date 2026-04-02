<?php

namespace App\Http\Controllers;

use App\Models\ContratacionPagos;
use App\Models\Pago;
use Carbon\Carbon; // Añadido
use Illuminate\Support\Facades\Auth;

class HistorialPagosController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Pagos existentes desde Pago
        $pagos = Pago::whereHas('contratacion', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['contratacion.producto'])
            ->get()
            ->map(function ($pago) {
                return [
                    'monto' => $pago->monto ?? null,
                    'estado' => $pago->estado ?? 'desconocido',
                    'fecha_pago' => $pago->fecha_pago
                        ? Carbon::parse($pago->fecha_pago)->toDateString()
                        : null,
                    'producto' => optional(optional($pago->contratacion)->producto)->product_name ?? 'Sin producto',
                ];
            });

        // Pagos desde ContratacionPagos
        $contratacionPagos = ContratacionPagos::whereHas('contratacion', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['contratacion.producto', 'payment'])
            ->get()
            ->map(function ($contratacionPago) {
                return [
                    'monto' => optional($contratacionPago->payment)->amount ?? null,
                    'estado' => optional($contratacionPago->payment)->status ?? 'desconocido',
                    'fecha_pago' => optional($contratacionPago->payment)->created_at
                        ? Carbon::parse($contratacionPago->payment->created_at)->toDateString()
                        : null,
                    'producto' => optional(optional($contratacionPago->contratacion)->producto)->product_name ?? 'Sin producto',
                ];
            });

        // Combina ambas colecciones de forma segura
        $historialPagos = collect()
            ->merge($pagos ?? [])
            ->merge($contratacionPagos ?? [])
            ->sortByDesc('fecha_pago')
            ->values();

        return view('user.historial-pagos', compact('historialPagos'));
    }
}
