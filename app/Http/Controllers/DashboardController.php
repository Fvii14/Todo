<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Contratacion;
use App\Models\MotivoSubsanacionContratacion;
use App\Models\User;
use App\Models\UserDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $proximasAyudas = Ayuda::where('fecha_inicio', '>=', now())
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        return view('admin.dashboardv2', [
            'totalExpedientes' => Contratacion::count(),
            'pctTotalExpedientes' => 12,
            'clientesActivos' => User::has('contrataciones')->count(),
            'pctClientesActivos' => 8,
            'docsPendientes' => UserDocument::where('estado', 'pendiente')->count(),
            'completadosMes' => Contratacion::count(),
            'metaCompletados' => 100,
            'tiempoMedio' => 12.5,
            'tiempoMedioDiff' => -2.1,
            'tasaExito' => 87.3,
            'ingresosMes' => 45720,
            'pctIngresosMes' => 15,
            // Contrataciones con al menos una subsanación pendiente
            'subsanacionesPendientes' => MotivoSubsanacionContratacion::pendientes()
                ->distinct('contratacion_id')
                ->count('contratacion_id'),
            'proximasAyudas' => $proximasAyudas,
        ]);
    }

    public function workTray()
    {
        $userId = Auth::id();
        $umbral = 7;
        $hoy = Carbon::today();

        // 1) Expedientes con subsanaciones pendientes
        $subQuery = Contratacion::with('user', 'ayuda')
            ->whereHas('motivosSubsanacionContrataciones', fn ($q) => $q->pendientes());
        $expedientesSubsanacion = $subQuery
            ->leftJoin('ayudas', 'contrataciones.ayuda_id', '=', 'ayudas.id')
            ->orderByRaw("COALESCE(ayudas.fecha_fin, '2099-12-31') ASC")
            ->select('contrataciones.*')
            ->paginate(10, ['*'], 'subPage');

        // 2) Expedientes en tramitación (OP1-Tramitacion)
        $tramQuery = Contratacion::with('user', 'ayuda')
            ->whereHas('estadosContratacion', fn ($q) => $q->where('codigo', 'OP1-Tramitacion'));
        $expedientesTramitando = $tramQuery
            ->leftJoin('ayudas', 'contrataciones.ayuda_id', '=', 'ayudas.id')
            ->orderByRaw("COALESCE(ayudas.fecha_fin, '2099-12-31') ASC")
            ->select('contrataciones.*')
            ->paginate(10, ['*'], 'tramPage');

        // 3) Documentos pendientes
        $docsQuery = UserDocument::with(['user', 'document'])
            ->where('user_documents.estado', 'pendiente'); // ← aquí el prefijo

        $docsPendientes = $docsQuery
            ->join('contrataciones as c', 'user_documents.user_id', '=', 'c.user_id')
            ->orderBy('c.fecha_contratacion', 'desc')
            ->select('user_documents.*')
            ->paginate(10, ['*'], 'docsPage');

        $subsanUrgentes = Contratacion::whereHas('motivosSubsanacionContrataciones', fn ($q) => $q->pendientes())
            ->whereHas('ayuda', fn ($q) => $q->whereBetween('fecha_fin', [$hoy, $hoy->copy()->addDays($umbral)])
            )->count();

        $tramUrgentes = Contratacion::whereHas('estadosContratacion', fn ($q) => $q->where('codigo', 'OP1-Tramitacion'))
            ->whereHas('ayuda', fn ($q) => $q->whereBetween('fecha_fin', [$hoy, $hoy->copy()->addDays($umbral)])
            )->count();

        $docsUrgentes = UserDocument::where('user_documents.estado', 'pendiente')
            ->whereHas('user.contrataciones.ayuda', fn ($q) => $q->whereBetween('fecha_fin', [$hoy, $hoy->copy()->addDays($umbral)])
            )->count();

        $urgentes = $subsanUrgentes + $tramUrgentes + $docsUrgentes;

        return view('admin.work-tray', compact(
            'urgentes',
            'umbral',
        ));
    }
}
