<?php

namespace App\Http\Controllers;

use App\Models\Ccaa;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        // 1) Lista de todas las CCAA para el filtro
        $ccaas = Ccaa::orderBy('nombre_ccaa')->pluck('nombre_ccaa');

        // 2) Query base: solo usuarios con contrataciones
        $query = User::query()
            ->whereHas('contrataciones');

        // 3) Uniones para provincia/CCAA
        $query->leftJoin('answers as a', function ($join) {
            $join->on('a.user_id', '=', 'users.id')
                ->where('a.question_id', 36);
        })
            ->leftJoin('provincia as p', 'p.nombre_provincia', '=', 'a.answer')
            ->leftJoin('ccaa as c', 'c.id', '=', 'p.id_ccaa')
            ->select('users.*');

        // 4) Filtro por Comunidad Autónoma
        if ($request->filled('ccaa')) {
            $query->where('c.nombre_ccaa', $request->ccaa);
        }

        // 5) Filtro por nombre o email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // 6) Agregados y orden
        $clientes = $query
            ->withCount('contrataciones')
            ->withSum('contrataciones', 'monto_total_ayuda')
            ->withCount([
                'contrataciones as concedidas_count' => function ($q) {
                    // Aproximación: contrataciones con resolución (OP1-Resolucion) y monto_total_ayuda informado
                    $q->whereHas('estadosContratacion', fn ($eq) => $eq->where('codigo', 'OP1-Resolucion'))
                        ->whereNotNull('monto_total_ayuda');
                },
            ])
            ->orderByDesc('contrataciones_count')
            ->paginate(10)
            ->withQueryString();

        return view('admin.clientes', compact('clientes', 'ccaas'));
    }
}
