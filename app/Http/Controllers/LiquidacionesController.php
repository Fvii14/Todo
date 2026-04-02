<?php

namespace App\Http\Controllers;

use App\Events\EventCobroRealizado;
use App\Events\EventConcesionRegistrada;
use App\Events\EventPagoRegistrado;
use App\Models\Contratacion;
use App\Models\EstadoContratacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
// use GPBMetadata\Google\Api\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LiquidacionesController extends Controller
{
    public function index(Request $request)
    {
        // usamos la funcion del modelo user para obtener el nombre completo

        $tab = $request->get('tab', 'concesiones');

        // Select para el <select> de ayudas
        $ayudas = DB::table('ayudas')
            ->select('id', 'nombre_ayuda')
            ->orderBy('nombre_ayuda')
            ->get();

        $showLiquidadas = $request->boolean('show_liquidadas');
        $showNoLiquidadas = $request->boolean('show_no_liquidadas');
        $filterByLiquidada = $showLiquidadas || $showNoLiquidadas;

        if ($tab === 'concesiones') {
            $ayudaId = $request->integer('ayuda_id');
            $q = trim((string) $request->get('q'));

            // Filtro por OPx:
            // - código concreto (p.ej. OP1-Resolucion)
            // - grupo completo (p.ej. OP4 => cualquier estado OP4)
            $rawEstados = collect((array) $request->input('estado_opx', []))
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->unique();

            $codigosValidos = EstadoContratacion::pluck('codigo')->all();
            $gruposValidos = EstadoContratacion::pluck('grupo')->filter()->unique()->all();

            $selectedCodigos = $rawEstados->intersect($codigosValidos)->values()->all();
            $selectedGrupos = $rawEstados->intersect($gruposValidos)->values()->all();
            $selectedEstadosOPx = $rawEstados->values()->all();

            $contrataciones = Contratacion::query()
                ->with(['user:id,name,email', 'product:id,product_name', 'ayuda:id,nombre_ayuda', 'estadosContratacion:id,codigo,grupo'])
                ->with([
                    'user:id,name,email',
                    'product:id,product_name,commission_pct',
                    'ayuda:id,nombre_ayuda',
                    'estadosContratacion:id,codigo,grupo',
                ])
                ->when(
                    ! empty($selectedCodigos) || ! empty($selectedGrupos),
                    function ($query) use ($selectedCodigos, $selectedGrupos) {
                        $query->where(function ($w) use ($selectedCodigos, $selectedGrupos) {
                            $first = true;

                            if (! empty($selectedCodigos)) {
                                $w->whereHas('estadosContratacion', function ($eq) use ($selectedCodigos) {
                                    $eq->whereIn('codigo', $selectedCodigos);
                                });
                                $first = false;
                            }

                            foreach ($selectedGrupos as $grupo) {
                                if ($first) {
                                    $w->whereHas('estadosContratacion', fn ($eq) => $eq->where('grupo', $grupo));
                                    $first = false;
                                } else {
                                    $w->orWhereHas('estadosContratacion', fn ($eq) => $eq->where('grupo', $grupo));
                                }
                            }
                        });
                    },
                    fn ($query) => $query->where(function ($w) {
                        // Por defecto: mostramos OP1-Resolucion (antigua Tramitación)
                        // y cualquier estado del grupo OP4 (antigua Concedida)
                        $w->whereHas('estadosContratacion', fn ($eq) => $eq->where('codigo', 'OP1-Resolucion'))
                            ->orWhereHas('estadosContratacion', fn ($eq) => $eq->where('grupo', 'OP4'));
                    })
                )
                ->where(fn ($q) => $q->where('liquidada', false)->orWhereNull('liquidada'))
                ->when($ayudaId, fn ($qq) => $qq->where('ayuda_id', $ayudaId))
                ->when($q, fn ($qq) => $qq->whereHas(
                    'user',
                    fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
                ))
                ->orderByDesc('fecha_contratacion')
                ->paginate(25)
                ->withQueryString();

            return view('admin.concesiones', [
                'tab' => $tab,
                'ayudas' => $ayudas,
                'contrataciones' => $contrataciones,
                'selectedAyudaId' => $ayudaId,
                'q' => $q,
                'selectedEstadosOPx' => $selectedEstadosOPx,
            ]);
        }
        // --- TAB PAGOS: estado = 'cierre' + fase = 'resolucion' + montos NOT NULL
        $ayudaIdPagos = $request->integer('ayuda_id_pagos');
        $qPagos = trim((string) $request->get('q_pagos'));

        $withPendiente = $request->boolean('with_pendiente') && $showNoLiquidadas;
        $onlyMorosos = $request->boolean('only_morosos');

        // Subconsulta: cobrado acumulado de comisión (si quieres seguir mostrándolo)
        $cobrosSub = DB::table('cobros_ttf')
            ->select('contratacion_id', DB::raw('SUM(cantidad_comision) as cobrado_acum'))
            ->groupBy('contratacion_id');

        $pagadoImporteSub = DB::table('pagos_administracion')
            ->select('contratacion_id', DB::raw('SUM(importe_pagado) as pagado_acum_admin'))
            ->groupBy('contratacion_id');

        // Subconsulta: nº de pagos de la Administración
        $pagosSub = DB::table('pagos_administracion')
            ->select('contratacion_id', DB::raw('COUNT(*) as pagos_count'))
            ->groupBy('contratacion_id');

        // ✅ Subconsulta: comisión ASIGNADA acumulada (en pagos_administracion.comision)
        $asignadoSub = DB::table('pagos_administracion')
            ->select('contratacion_id', DB::raw('SUM(comision) as asignado_acum'))
            ->groupBy('contratacion_id');

        // Subconsulta: nº de pagos PENDIENTES (para flag de moroso en la tabla)
        $morososSub = DB::table('pagos_administracion')
            ->select(
                'contratacion_id',
                DB::raw("SUM(CASE WHEN estado_cobro = 'pendiente' THEN 1 ELSE 0 END) as pagos_pendientes")
            )
            ->groupBy('contratacion_id');

        $contrataciones = \App\Models\Contratacion::query()
            ->with(['user:id,name,email', 'product:id,product_name,commission_pct', 'ayuda:id,nombre_ayuda'])
            ->leftJoinSub($cobrosSub, 'ctf', 'ctf.contratacion_id', '=', 'contrataciones.id')
            ->leftJoinSub($pagosSub, 'pag', 'pag.contratacion_id', '=', 'contrataciones.id')
            ->leftJoinSub($pagadoImporteSub, 'pimp', 'pimp.contratacion_id', '=', 'contrataciones.id')
            ->leftJoinSub($asignadoSub, 'asig', 'asig.contratacion_id', '=', 'contrataciones.id')
            ->leftJoinSub($morososSub, 'mor', 'mor.contratacion_id', '=', 'contrataciones.id')
            ->addSelect(
                'contrataciones.*',
                DB::raw('COALESCE(ctf.cobrado_acum,0)  as cobrado_acum'),
                DB::raw('COALESCE(asig.asignado_acum,0) as asignado_acum'),
                DB::raw('COALESCE(pag.pagos_count,0)   as pagos_count'),
                DB::raw('COALESCE(pimp.pagado_acum_admin,0) as pagado_acum_admin'),
                DB::raw('COALESCE(mor.pagos_pendientes,0) as pagos_pendientes'),
                DB::raw('
            CASE
              WHEN (COALESCE(contrataciones.monto_comision,0) - COALESCE(ctf.cobrado_acum,0)) > 0
              THEN (COALESCE(contrataciones.monto_comision,0) - COALESCE(ctf.cobrado_acum,0))
              ELSE 0
            END as pendiente_cobro
        ')
            )
            // TAB PAGOS: contrataciones que ya tienen concesión y comisión asignadas
            ->whereNotNull('contrataciones.monto_total_ayuda')
            ->whereNotNull('contrataciones.monto_comision')
            ->when($filterByLiquidada, function ($q) use ($showLiquidadas, $showNoLiquidadas) {
                $q->where(function ($w) use ($showLiquidadas, $showNoLiquidadas) {
                    if ($showLiquidadas) {
                        $w->orWhere('contrataciones.liquidada', 1);
                    }
                    if ($showNoLiquidadas) {
                        $w->orWhere(function ($qq) {
                            $qq->where('contrataciones.liquidada', 0)
                                ->orWhereNull('contrataciones.liquidada'); // por si hay nulos legacy
                        });
                    }
                });
            })
            ->when($ayudaIdPagos, fn ($qq) => $qq->where('contrataciones.ayuda_id', $ayudaIdPagos))
            ->when($qPagos, fn ($qq) => $qq->whereHas(
                'user',
                fn ($u) => $u->where('name', 'like', "%{$qPagos}%")->orWhere('email', 'like', "%{$qPagos}%")
            ))
            // ✅ Filtro: contrataciones que tienen al menos un pago de admin con estado_cobro = 'pendiente'
            ->when($withPendiente, function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('pagos_administracion as pa')
                        ->whereColumn('pa.contratacion_id', 'contrataciones.id')
                        ->where('pa.estado_cobro', 'pendiente');
                });
            })
            // ✅ Filtro: morosos → más de un pago con estado_cobro = 'pendiente'
            ->when($onlyMorosos, function ($q) {
                $q->whereIn('contrataciones.id', function ($sub) {
                    $sub->select('contratacion_id')
                        ->from('pagos_administracion')
                        ->where('estado_cobro', 'pendiente')
                        ->groupBy('contratacion_id')
                        ->havingRaw('COUNT(*) > 1');
                });
            })
            ->orderByDesc('contrataciones.fecha_contratacion')
            ->paginate(25)
            ->withQueryString();

        return view('admin.concesiones', [
            'tab' => $tab,
            'ayudas' => $ayudas,
            'contrataciones' => $contrataciones,
            'selectedAyudaIdPagos' => $ayudaIdPagos,
            'qPagos' => $qPagos,
        ]);
    }

    public function updateConcedida(Contratacion $contratacion, Request $request)
    {
        $data = $request->validate([
            'monto_total_ayuda' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $contratacion->update(['monto_total_ayuda' => $data['monto_total_ayuda']]);

        return back()->with('status', 'Cantidad concedida actualizada.');
    }

    public function updateMontos(Contratacion $contratacion, Request $request)
    {
        $contratacion->loadMissing('product');

        $comisionFijaPorProducto = $contratacion->product
            && $contratacion->product->commission_pct !== null
            && $contratacion->product->commission_pct !== '';

        $rules = [
            'monto_total_ayuda' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'anualidades' => ['array'],
            'anualidades.*.anio' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'anualidades.*.importe' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'reparto_total_conocido' => ['nullable', 'boolean'],
        ];

        if (! $comisionFijaPorProducto) {
            $rules['monto_comision'] = ['required', 'numeric', 'min:0', 'max:99999999.99'];
        }

        $data = $request->validate($rules);
        $anualidades = $request->input('anualidades', []);
        $totalCerrado = $request->boolean('reparto_total_conocido', false);

        if ($totalCerrado && ! empty($anualidades)) {
            $suma = collect($anualidades)->sum(fn ($a) => (float) ($a['importe'] ?? 0));
            if (abs($suma - (float) $data['monto_total_ayuda']) > 0.01) {
                return back()
                    ->withErrors(['anualidades' => 'La suma de anualidades no coincide con el total concedido.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($contratacion, $data, $anualidades, $comisionFijaPorProducto) {
            $nuevoTotal = (float) $data['monto_total_ayuda'];

            if ($comisionFijaPorProducto) {
                $pct = (float) $contratacion->product->commission_pct;
                $nuevaComision = round($nuevoTotal * $pct / 100, 2);
            } else {
                $nuevaComision = (float) $data['monto_comision'];
            }

            // ✅ Un único UPDATE: si 'monto_ayuda_original' es NULL, lo establece al nuevo total; si no, lo deja como está
            DB::table('contrataciones')
                ->where('id', $contratacion->id)
                ->update([
                    'monto_total_ayuda' => $nuevoTotal,
                    'monto_comision' => $nuevaComision,
                    'monto_ayuda_original' => DB::raw('COALESCE(monto_ayuda_original, '.$nuevoTotal.')'),
                    'updated_at' => now(),
                ]);

            // (Opcional) refrescar el modelo si lo necesitas después
            $contratacion->refresh();

            // Anualidades: reemplazar por las nuevas
            $rows = collect($anualidades)
                ->filter(fn ($a) => isset($a['anio'], $a['importe']) && $a['anio'] !== '' && $a['importe'] !== '')
                ->map(fn ($a) => [
                    'contratacion_id' => $contratacion->id,
                    'anio' => (int) $a['anio'],
                    'importe' => (float) $a['importe'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->values()->all();

            DB::table('concesion_anualidades')->where('contratacion_id', $contratacion->id)->delete();
            if (! empty($rows)) {
                DB::table('concesion_anualidades')->insert($rows);
            }

            // Al registrar concesión: quitar OP1-Resolucion y marcar OP1-Cierre + OP4-Cobrando
            if ($nuevoTotal > 0 && $nuevaComision > 0) {
                $contratacion->liquidada = false;
                $contratacion->save();
                app(\App\Services\EstadoContratacionService::class)->syncEstadosByCodigos(
                    $contratacion,
                    ['OP1-Cierre', 'OP4-Cobrando'],
                    true
                );
                event(new EventConcesionRegistrada($contratacion));
            }
        });

        return back()->with('status', 'Concesión, comisión y anualidades guardadas correctamente.');
    }

    /**
     * Propuesta de comisión para un pago: se aplica el porcentaje del producto al importe del pago.
     * Ya no se usa tope del 30% ni límite por comisión pendiente; en todos los pagos se cobra el % del producto.
     */
    protected function calcularPropuestaCobro(float $importePago, Contratacion $contratacion): float
    {
        $contratacion->loadMissing('product');
        if (! $contratacion->product || $contratacion->product->commission_pct === null || $contratacion->product->commission_pct === '') {
            return 0.0;
        }

        $pct = (float) $contratacion->product->commission_pct;

        return (float) round($importePago * $pct / 100, 2);
    }

    /**
     * Registrar un nuevo pago de administración (y su comisión asignada)
     */
    public function storePagoAdmin(Contratacion $contratacion, Request $request)
    {
        $data = $request->validate([
            'importe_pagado' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'fecha_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($contratacion, $data) {

            // Bloqueamos todos los pagos de esta contratación para calcular n_pago
            DB::table('pagos_administracion')
                ->where('contratacion_id', $contratacion->id)
                ->lockForUpdate()
                ->get();

            // n_pago correlativo
            $ultimoNPago = DB::table('pagos_administracion')
                ->where('contratacion_id', $contratacion->id)
                ->orderByDesc('n_pago')
                ->value('n_pago');
            $nPago = is_null($ultimoNPago) ? 1 : ((int) $ultimoNPago + 1);

            // Insert del pago (todavía sin comisión)
            $pagoId = DB::table('pagos_administracion')->insertGetId([
                'contratacion_id' => $contratacion->id,
                'n_pago' => $nPago,
                'importe_pagado' => $data['importe_pagado'],
                'fecha_pago' => $data['fecha_pago'] ?? now()->toDateString(),
                'notas' => $data['notas'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Comisión = porcentaje del producto aplicado al importe del pago, limitada al remanente del contrato
            $importePago = (float) $data['importe_pagado'];
            $comisionPropuesta = $this->calcularPropuestaCobro($importePago, $contratacion);

            $topeContrato = (float) ($contratacion->monto_comision ?? 0);
            $comisionYaAsignada = (float) DB::table('pagos_administracion')
                ->where('contratacion_id', $contratacion->id)
                ->sum(DB::raw('COALESCE(comision, 0)'));
            $remanenteContrato = max($topeContrato - $comisionYaAsignada, 0.0);
            $comisionFinal = round(min($comisionPropuesta, $remanenteContrato), 2);

            // Guardar comisión y estado en el pago
            DB::table('pagos_administracion')->where('id', $pagoId)->update([
                'comision' => $comisionFinal,
                'estado_cobro' => $comisionFinal > 0 ? 'pendiente' : 'no_aplica',
                'updated_at' => now(),
            ]);

            // Estados OPx: quitar OP4-Cobrando y marcar OP4-Pagando
            $contratacion->refresh()->load('estadosContratacion');
            $codigos = $contratacion->estadosContratacion->pluck('codigo')->all();
            $codigos = array_values(array_diff($codigos, ['OP4-Cobrando']));
            if (! in_array('OP4-Pagando', $codigos)) {
                $codigos[] = 'OP4-Pagando';
            }
            app(\App\Services\EstadoContratacionService::class)->syncEstadosByCodigos($contratacion, $codigos, true);

            event(new EventPagoRegistrado($contratacion));

            return back()->with(
                'status',
                'Pago de Administración #'.$nPago.' registrado.'
                    .($comisionFinal > 0 ? ' Comisión guardada.' : ' (sin comisión).')
            );
        });
    }

    /**
     * (Opcional) Generar cobro desde un pago ya guardado
     */
    public function generarCobroDesdePago($pagoId, Request $request)
    {
        $data = $request->validate([
            'monto_cobro' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ]);

        // Datos del pago y contratación
        $pago = DB::table('pagos_administracion')->where('id', $pagoId)->first();
        abort_if(! $pago, 404);

        $contratacion = Contratacion::findOrFail($pago->contratacion_id);

        return DB::transaction(function () use ($data, $pago, $contratacion) {
            // Límite por pago: la comisión asignada a este pago (calculada con el % del producto)
            $topePago = (float) ($pago->comision ?? 0);

            $sumCobrosPago = DB::table('cobros_ttf')->where('pago_admin_id', $pago->id)->sum('cantidad_comision');
            $margenPago = max($topePago - $sumCobrosPago, 0.0);

            $sumCobrosTotal = DB::table('cobros_ttf')->where('contratacion_id', $contratacion->id)->sum('cantidad_comision');
            $pendiente = max((float) $contratacion->monto_comision - $sumCobrosTotal, 0.0);

            $aCobrar = min((float) $data['monto_cobro'], $margenPago, $pendiente);
            if ($aCobrar <= 0) {
                return back()->withErrors(['monto_cobro' => 'No se puede generar el cobro: excede la comisión del pago o comisión pendiente.']);
            }

            DB::table('cobros_ttf')->insert([
                'pago_admin_id' => $pago->id,
                'contratacion_id' => $contratacion->id,
                'factura_id' => null,
                'cantidad_comision' => $aCobrar,
                'estado' => 'pendiente',
                'notas' => 'Cobro generado manualmente desde pago.',
                'fecha_prevista_cobro' => now()->toDateString(),
                'fecha_cobro' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('status', 'Cobro generado.');
        });
    }

    public function listPagosAdmin(Contratacion $contratacion, Request $request)
    {
        $pagos = DB::table('pagos_administracion')
            ->where('contratacion_id', $contratacion->id)
            ->orderByDesc('fecha_pago')
            ->get();

        $totales = [
            'importe_pagado_total' => (float) $pagos->sum('importe_pagado'),
            'comision_total' => (float) $pagos->sum(fn ($p) => (float) ($p->comision ?? 0)),
        ];

        return response()->json([
            'pagos' => $pagos->map(function ($p) {
                $tiene = ! empty($p->factura_pdf_gcs_path);

                return [
                    'id' => $p->id,
                    'n_pago' => $p->n_pago ?? null,
                    'fecha_pago' => $p->fecha_pago,
                    'importe_pagado' => (float) $p->importe_pagado,
                    'comision' => (float) ($p->comision ?? 0),
                    'estado_cobro' => $p->estado_cobro ?? 'no_aplica',
                    'notas' => $p->notas,
                    'created_at' => $p->created_at,

                    // Enlace a tu endpoint que firma al vuelo el PDF privado en GCS
                    'factura_view_url' => $tiene ? route('facturas.ver', ['pago' => $p->id]) : null,
                    'factura_numero' => $p->factura_numero,
                    'factura_emitida' => $p->factura_emitida_at,
                ];
            }),
            'totales' => $totales,
        ]);
    }

    public function marcarPagoComisionCobrada($pagoId, Request $request)
    {
        // Devuelve JSON (para usar cómodamente desde el modal)
        return DB::transaction(function () use ($pagoId) {

            // 1) Cargamos pago y contratación con bloqueo
            $pago = DB::table('pagos_administracion')->lockForUpdate()->where('id', $pagoId)->first();
            abort_if(! $pago, 404, 'Pago no encontrado');

            $contratacion = Contratacion::findOrFail($pago->contratacion_id);

            // 2) Validaciones básicas
            $comision = (float) ($pago->comision ?? 0);
            if ($comision <= 0) {
                return response()->json(['ok' => false, 'message' => 'Este pago no tiene comisión asignada.'], 422);
            }
            if ($pago->estado_cobro === 'cobrada') {
                return response()->json(['ok' => true, 'message' => 'La comisión de este pago ya está marcada como cobrada.']);
            }

            // 3) Límite por pago: la comisión asignada a este pago (% del producto)
            $topePago = (float) ($pago->comision ?? 0);

            $sumCobrosPago = DB::table('cobros_ttf')
                ->where('pago_admin_id', $pago->id)
                ->sum('cantidad_comision');
            $margenPago = max($topePago - $sumCobrosPago, 0.0);

            $sumCobrosTotal = DB::table('cobros_ttf')
                ->where('contratacion_id', $contratacion->id)
                ->sum('cantidad_comision');
            $pendiente = max((float) ($contratacion->monto_comision ?? 0) - $sumCobrosTotal, 0.0);

            $aCobrar = min($comision, $margenPago, $pendiente);

            if ($aCobrar <= 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se puede registrar cobro: excede la comisión del pago o no hay comisión pendiente.',
                ], 422);
            }

            // 4) Registrar cobro como COBRADA
            DB::table('cobros_ttf')->insert([
                'pago_admin_id' => $pago->id,
                'contratacion_id' => $contratacion->id,
                'factura_id' => null,
                'cantidad_comision' => $aCobrar,
                'notas' => 'Cobro marcado como cobrado desde el pago de la Administración.',
                'fecha_prevista_cobro' => null,
                'fecha_cobro' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->actualizarLiquidadaSiCompleta($pago->contratacion_id);

            // 5) Marcar pago como COBRADA
            DB::table('pagos_administracion')
                ->where('id', $pago->id)
                ->update([
                    'estado_cobro' => 'cobrada',
                    'updated_at' => now(),
                ]);

            // 6) Estados OPx: OP4-Pagando → OP4-Cobrando; si todo el monto_total_ayuda está pagado → OP4-Cobrado
            $contratacion->refresh()->load('estadosContratacion');
            $codigos = $contratacion->estadosContratacion->pluck('codigo')->all();
            $codigos = array_values(array_diff($codigos, ['OP4-Pagando', 'OP4-Cobrando', 'OP4-Cobrado']));
            $sumPagado = (float) DB::table('pagos_administracion')
                ->where('contratacion_id', $contratacion->id)
                ->sum('importe_pagado');
            $totalAyuda = (float) ($contratacion->monto_total_ayuda ?? 0);
            if ($totalAyuda > 0 && $sumPagado >= $totalAyuda - 0.01) {
                $codigos[] = 'OP4-Cobrado';
            } else {
                $codigos[] = 'OP4-Cobrando';
            }
            app(\App\Services\EstadoContratacionService::class)->syncEstadosByCodigos($contratacion, $codigos, true);

            $contratacion->refresh()->load('estadosContratacion');
            event(new EventCobroRealizado($contratacion));

            return response()->json([
                'ok' => true,
                'message' => 'Comisión cobrada registrada.',
                'cobrado' => $aCobrar,
            ]);
        });
    }

    private function getOrCreateHoldedContactId(User $user): string
    {
        Log::info('Holded contacto: buscando/creando para user id '.$user->id);

        $key = config('services.holded.key');
        $base = 'https://api.holded.com/api/invoicing/v1/contacts';

        $normVat = function (?string $v) {
            return $v ? strtoupper(preg_replace('/[^A-Z0-9]/', '', $v)) : null;
        };
        $normMail = function (?string $v) {
            return $v ? strtolower(trim($v)) : null;
        };

        $dni = $normVat($user->dni ?? null);
        $email = $normMail($user->email ?? null);

        // 0) Validar el id guardado
        if ($user->holded_contact_id) {
            $chk = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
                ->get("$base/{$user->holded_contact_id}");
            if ($chk->successful()) {
                $c = $chk->json();
                $ok = true;

                $apiVat = $normVat($c['vatnumber'] ?? null);
                $apiEmail = $normMail($c['email'] ?? null);

                if ($dni && $apiVat) {
                    $ok = $ok && ($dni === $apiVat);
                }
                if ($email && $apiEmail) {
                    $ok = $ok && (strcasecmp($email, $apiEmail) === 0);
                }

                // Evita proveedores puros
                $notSupplier = (($c['clientRecord'] ?? 1) == 1) || (($c['type'] ?? '') !== 'supplier');

                if ($ok && $notSupplier) {
                    Log::debug('HOLD:contact_ok', [
                        'id' => $user->holded_contact_id,
                        'name' => $c['name'] ?? null,
                        'email' => $apiEmail,
                        'vat' => $apiVat,
                    ]);

                    return $user->holded_contact_id;
                }

                Log::warning('HOLD:stored_id_mismatch', ['id' => $user->holded_contact_id, 'api' => $c]);
            } else {
                Log::warning('HOLD:stored_id_404', ['id' => $user->holded_contact_id, 'status' => $chk->status()]);
            }
            $user->update(['holded_contact_id' => null]);
        }

        // Helper para elegir mejor candidato
        $pick = function ($arr) use ($dni, $email, $normVat, $normMail) {
            $col = collect($arr);

            // 1) Coincidencia exacta por VAT + no proveedor
            if ($dni) {
                $m = $col->first(function ($c) use ($dni, $normVat) {
                    $vat = $normVat($c['vatnumber'] ?? null);
                    $notSupplier = (($c['clientRecord'] ?? 1) == 1) || (($c['type'] ?? '') !== 'supplier');

                    return $vat && $vat === $dni && $notSupplier;
                });
                if ($m) {
                    return $m;
                }
            }

            // 2) Coincidencia exacta por email + no proveedor
            if ($email) {
                $m = $col->first(function ($c) use ($email, $normMail) {
                    $em = $normMail($c['email'] ?? null);
                    $notSupplier = (($c['clientRecord'] ?? 1) == 1) || (($c['type'] ?? '') !== 'supplier');

                    return $em && (strcasecmp($em, $email) === 0) && $notSupplier;
                });
                if ($m) {
                    return $m;
                }
            }

            // 3) Como último recurso, cualquier coincidencia exacta (vat o email)
            if ($dni) {
                $m = $col->first(function ($c) use ($dni, $normVat) {
                    return $normVat($c['vatnumber'] ?? null) === $dni;
                });
                if ($m) {
                    return $m;
                }
            }
            if ($email) {
                $m = $col->first(function ($c) use ($email, $normMail) {
                    return strcasecmp($normMail($c['email'] ?? null), $email) === 0;
                });
                if ($m) {
                    return $m;
                }
            }

            return null;
        };

        // 1) Buscar por NIF
        if ($dni) {
            $r = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
                ->get($base, ['vatnumber' => $dni, 'limit' => 50]);
            if ($r->successful()) {
                if ($found = $pick($r->json())) {
                    $user->update(['holded_contact_id' => $found['id']]);

                    return $found['id'];
                }
            }
        }

        // 2) Buscar por email (coincidencia exacta; evita proveedores)
        if ($email) {
            $r = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
                ->get($base, ['email' => $email, 'limit' => 50]);
            if ($r->successful()) {
                if ($found = $pick($r->json())) {
                    $user->update(['holded_contact_id' => $found['id']]);

                    return $found['id'];
                }
            }
        }

        // 3) Crear contacto como cliente
        $payload = array_filter([
            'name' => $user->nombre_completo ?? $user->name ?? 'Cliente sin nombre',
            'email' => $email,
            'type' => 'person',           // o 'company' si aplica
            'vatnumber' => $dni,
            'clientRecord' => 1,                  // ← importante para facturación
            'phones' => $user->telefono ? [$user->telefono] : null,
            'addresses' => $user->holded_address ? [$user->holded_address] : null,
        ], fn ($v) => $v !== null);

        $create = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
            ->post($base, $payload);

        if ($create->successful()) {
            $id = data_get($create->json(), 'id');
            if ($id) {
                $user->update(['holded_contact_id' => $id]);

                return $id;
            }
        } else {
            Log::warning('Holded contacto: fallo al crear', [
                'status' => $create->status(),
                'body' => $create->body(),
                'payload' => $payload,
            ]);
        }

        // 4) Duplicado/validación → re-búsqueda amplia y pick estricto
        if (in_array($create->status(), [409, 422], true)) {
            $r = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
                ->get($base, array_filter(['email' => $email, 'vatnumber' => $dni, 'limit' => 50]));
            if ($r->successful()) {
                if ($found = $pick($r->json())) {
                    $user->update(['holded_contact_id' => $found['id']]);

                    return $found['id'];
                }
            }
        }

        throw new \RuntimeException('No se pudo obtener/crear el contacto en Holded para este cliente.');
    }

    public function generarFacturaDesdePago($pagoId)
    {
        $reqId = (string) Str::uuid();
        Log::info('FACTURAR:start', ['pagoId' => $pagoId, 'req' => $reqId]);

        try {
            return DB::transaction(function () use ($pagoId, $reqId) {
                // 1) Cargar pago + contratación
                $pago = DB::table('pagos_administracion')
                    ->lockForUpdate()
                    ->where('id', $pagoId)
                    ->first();

                if (! $pago) {
                    Log::warning('FACTURAR:no_pago', ['pagoId' => $pagoId, 'req' => $reqId]);

                    return response()->json(['ok' => false, 'message' => 'Pago no encontrado.'], 404);
                }

                $contratacion = Contratacion::with('user')->find($pago->contratacion_id);
                if (! $contratacion || ! $contratacion->user) {
                    Log::warning('FACTURAR:no_contratacion_user', ['pagoId' => $pagoId, 'req' => $reqId]);

                    return response()->json(['ok' => false, 'message' => 'Contratación/usuario no encontrado.'], 404);
                }

                // 2) Reglas
                $comision = (float) ($pago->comision ?? 0);
                Log::debug('FACTURAR:checkpoint B', ['req' => $reqId, 'comision' => $comision]);

                if ($comision <= 0) {
                    return response()->json(['ok' => false, 'message' => 'Este pago no tiene comisión.'], 422);
                }

                // Ya emitida → firmar al vuelo
                if (! empty($pago->factura_holded_id)) {
                    $signedUrl = null;
                    if (! empty($pago->factura_pdf_gcs_path)) {
                        /** @var \App\Services\GcsUploaderService $uploader */
                        $uploader = app(\App\Services\GcsUploaderService::class);
                        $signedUrl = $uploader->getTemporaryUrl($pago->factura_pdf_gcs_path, 60 * 24 * 7);
                    }

                    return response()->json([
                        'ok' => true,
                        'message' => 'La factura ya está emitida.',
                        'factura_numero' => $pago->factura_numero,
                        'factura_holded_id' => $pago->factura_holded_id,
                        'factura_view_url' => $signedUrl ? route('facturas.ver', ['pago' => $pago->id]) : null,
                    ]);
                }

                // 3) Contacto Holded
                try {
                    $contactId = $this->getOrCreateHoldedContactId($contratacion->user);
                    Log::debug('FACTURAR:checkpoint C', ['req' => $reqId, 'contactId' => $contactId]);
                } catch (\Throwable $e) {
                    Log::error('FACTURAR:contact_error', [
                        'pagoId' => $pagoId,
                        'req' => $reqId,
                        'ex' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'ok' => false,
                        'message' => 'Error creando/obteniendo contacto en Holded: '.$e->getMessage(),
                    ], 422);
                }
                if (! $contactId) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'No hay contactId de Holded para el cliente.',
                    ], 422);
                }

                // 4) Crear + APROBAR factura en Holded
                $key = config('services.holded.key');

                $ivaPct = 21;                    // %
                $ivaKey = (string) config('services.holded.taxes.iva_21', 's_iva_21');
                $gross = round((float) $comision, 2);           // tu comisión con IVA
                $net = round($gross / (1 + $ivaPct / 100), 2);  // base imponible
                $concepto = trim('Comisión TTF · Contratación '.$contratacion->id.
                    ($contratacion->nombre_ayuda ? ' · '.$contratacion->nombre_ayuda : ''));

                $invoicePayload = [
                    'contactId' => $contactId,
                    'date' => now()->toDateString(),
                    'currency' => 'EUR',
                    'language' => 'es',
                    'numberseries' => config('services.holded.series.default', 'TEST'),
                    'approveDoc' => false,
                    'pricesWithTax' => false,
                    'items' => [[
                        'name' => $concepto,
                        'units' => 1,
                        'subtotal' => $net,
                        'discount' => 0,
                        'taxes' => [$ivaKey],
                    ]],
                ];

                Log::debug('FACTURAR:checkpoint D', ['req' => $reqId, 'payload' => $invoicePayload]);

                // $create = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
                //     ->post('https://api.holded.com/api/invoicing/v1/documents/invoice', $invoicePayload);
                $create = Http::withHeaders([
                    'key' => $key,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post('https://api.holded.com/api/invoicing/v1/documents/invoice', $invoicePayload);

                if (! $create->successful()) {
                    Log::error('FACTURAR:holded_create_fail', [
                        'pagoId' => $pagoId,
                        'req' => $reqId,
                        'status' => $create->status(),
                        'body' => $create->body(),
                        'payload' => $invoicePayload,
                    ]);

                    return response()->json([
                        'ok' => false,
                        'message' => 'Holded rechazó la creación de la factura.',
                        'holded_status' => $create->status(),
                        'holded_body' => $create->json() ?? $create->body(),
                    ], 422);
                }

                $data = $create->json() ?: [];
                $holdedId = $data['id'] ?? null;
                $numero = $data['number'] ?? null;

                Log::debug('FACTURAR:checkpoint E', ['req' => $reqId, 'holdedId' => $holdedId, 'numero' => $numero]);

                if (! $holdedId) {
                    Log::error('FACTURAR:holded_no_id', ['pagoId' => $pagoId, 'req' => $reqId, 'data' => $data]);

                    return response()->json(['ok' => false, 'message' => 'Holded no devolvió ID de la factura.'], 500);
                }

                // Si por timing aún no hay número, poll muy corto
                if (empty($numero)) {
                    for ($i = 0; $i < 4 && empty($numero); $i++) {
                        usleep(300_000 + 150_000 * $i);
                        $show = Http::withHeaders(['key' => $key, 'Accept' => 'application/json'])
                            ->get("https://api.holded.com/api/invoicing/v1/documents/invoice/{$holdedId}");
                        Log::debug('HOLD:doc_lines', [
                            'lines' => data_get($show->json(), 'lines'),
                        ]);

                        if ($show->successful()) {
                            $numero = data_get($show->json(), 'number') ?: $numero;

                            $doc = $show->json();
                            Log::debug('HOLD:doc_items', [
                                // en algunas cuentas viene como "items", en otras “lines”
                                'items' => data_get($doc, 'items'),
                                'lines' => data_get($doc, 'lines'),
                                // totales para verificar que ya no son 0
                                'net' => data_get($doc, 'net'),
                                'gross' => data_get($doc, 'gross'),
                            ]);
                        }
                    }
                }

                // 5) Descargar PDF (poll tolerante)
                $pdfBin = $this->fetchHoldedPdf('invoice', $holdedId, $key);
                if (! $pdfBin) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Holded aún no ha generado el PDF. Intenta de nuevo en unos segundos.',
                    ], 409)->header('Retry-After', '2');
                }
                if (substr($pdfBin, 0, 4) !== '%PDF') {
                    Log::error('PDF no válido tras recorte', ['head' => substr($pdfBin, 0, 16)]);

                    return response()->json(['ok' => false, 'message' => 'El PDF de la factura no es válido.'], 500);
                }

                // 6) Subir a GCS (privado) y guardar path
                $gcsPath = $this->buildInvoicePath($holdedId, $invoicePayload['date'] ?? null);
                try {
                    /** @var \App\Services\GcsUploaderService $uploader */
                    $uploader = app(\App\Services\GcsUploaderService::class);
                    $uploader->uploadString($pdfBin, $gcsPath, 'application/pdf');
                    Log::debug('FACTURAR:gcs_ok', ['req' => $reqId, 'path' => $gcsPath]);
                } catch (\Throwable $e) {
                    Log::warning('FACTURAR:gcs_fail_fallback_local', ['req' => $reqId, 'ex' => $e->getMessage()]);
                    Storage::disk('public')->put($gcsPath, $pdfBin);
                }

                // 7) Persistir metadatos
                DB::table('pagos_administracion')->where('id', $pago->id)->update([
                    'factura_holded_id' => $holdedId,
                    'factura_numero' => $numero,
                    'factura_pdf_gcs_path' => $gcsPath,
                    'factura_emitida_at' => now(),
                    'updated_at' => now(),
                ]);

                if (empty($contratacion->user->holded_contact_id)) {
                    $contratacion->user->update(['holded_contact_id' => $contactId]);
                }

                Log::info('FACTURAR:done', ['pagoId' => $pagoId, 'req' => $reqId, 'holdedId' => $holdedId]);

                return response()->json([
                    'ok' => true,
                    'message' => 'Factura emitida',
                    'factura_holded_id' => $holdedId,
                    'factura_numero' => $numero,
                    'factura_view_url' => route('facturas.ver', ['pago' => $pago->id]),
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('FACTURAR:unhandled', ['pagoId' => $pagoId, 'req' => $reqId, 'ex' => $e]);

            return response()->json([
                'ok' => false,
                'message' => 'Error inesperado: '.$e->getMessage(),
            ], 500);
        }
    }

    private function actualizarLiquidadaSiCompleta(int $contratacionId): void
    {
        Log::info("Verificando si la contratación #{$contratacionId} está completamente liquidada...");
        // Bloqueo pesimista para evitar carreras si se marcan varios cobros a la vez
        $contratacion = Contratacion::lockForUpdate()->find($contratacionId);
        if (! $contratacion) {
            return;
        }
        Log::info("Contratación encontrada. Monto comisión: {$contratacion->monto_comision}, Liquidada: ".($contratacion->liquidada ? 'sí' : 'no'));
        // Total de comisión de la contratación
        $totalComision = (float) ($contratacion->monto_comision ?? 0);
        if ($totalComision <= 0) {
            return;
        }

        // Suma de comisiones cobradas (cobros_ttf)
        $sumCobros = (float) DB::table('cobros_ttf')
            ->where('contratacion_id', $contratacionId)
            ->sum('cantidad_comision');

        // Tolerancia de 1 céntimo para redondeos
        $falta = $totalComision - $sumCobros;

        if ($falta <= 0.01) {
            Log::info("La contratación #{$contratacionId} está completamente liquidada. Marcando como liquidada.");
            // Ya está completamente liquidada
            if (! $contratacion->liquidada) {
                Log::info("Actualizando el campo 'liquidada' a true.");
                $contratacion->update(['liquidada' => 1]);
            }
        }
    }

    private function buildInvoicePath(string $holdedId, ?string $date = null): string
    {
        $d = $date ? \Carbon\Carbon::parse($date) : now();
        $year = $d->year;
        $quarter = 'Q'.$d->quarter;

        return "facturas/{$year}/{$quarter}/{$holdedId}.pdf";
    }

    private function fetchHoldedPdf(string $docType, string $holdedId, string $apiKey, int $tries = 8, array $waitUs = [400_000, 800_000, 1_200_000, 2_000_000, 4_000_000, 6_000_000, 8_000_000, 10_000_000]): ?string
    {
        for ($i = 0; $i < $tries; $i++) {
            $resp = Http::withHeaders([
                'key' => $apiKey,
                'Accept' => 'application/json', // forzamos JSON con base64 si no está listo el binario
            ])->get("https://api.holded.com/api/invoicing/v1/documents/{$docType}/{$holdedId}/pdf");

            $ct = strtolower($resp->header('content-type') ?? '');
            $body = $resp->body();

            // A) PDF directo
            if (str_starts_with($ct, 'application/pdf') || substr($body, 0, 4) === '%PDF') {
                return $body;
            }

            // B) JSON (a veces lo sirven como text/html) con base64
            $json = json_decode($body, true);
            if (is_array($json) && isset($json['data'])) {
                $bin = base64_decode($json['data'], true);
                if ($bin !== false) {
                    // ← IMPORTANTE: el base64 puede incluir cabeceras HTTP; buscamos el inicio real del PDF
                    $pos = strpos($bin, '%PDF');
                    if ($pos !== false) {
                        return substr($bin, $pos);
                    }
                }
            }

            // No listo aún → espera incremental y reintenta
            if ($i < $tries - 1) {
                usleep($waitUs[$i] ?? end($waitUs));
            }
        }

        return null;
    }

    /**
     * Devuelve el ID del impuesto cuyo % coincida (por ejemplo 21.0).
     * Hace una pequeña caché en runtime para evitar pedirlo en cada llamada.
     */
    private static array $holdedTaxCache = [];

    private function holdedGetTaxIdByPercent(float $percent, string $apiKey): ?string
    {
        $k = 'p'.(string) $percent;
        if (isset(self::$holdedTaxCache[$k])) {
            return self::$holdedTaxCache[$k]; // puede ser null si no se encontró
        }

        try {
            $r = Http::withHeaders(['key' => $apiKey, 'Accept' => 'application/json'])
                ->get('https://api.holded.com/api/invoicing/v1/taxes');

            if (! $r->successful()) {
                Log::warning('HOLDED:taxes_fail', ['status' => $r->status(), 'body' => substr($r->body(), 300)]);

                return self::$holdedTaxCache[$k] = null;
            }

            $list = $r->json() ?? [];
            // Algunos tenants devuelven el porcentaje en 'value', otros en 'rate'
            $found = collect($list)->first(function ($t) use ($percent) {
                $val = (float) ($t['value'] ?? $t['rate'] ?? -999);

                return abs($val - $percent) < 0.0001;
            });

            if (! $found && ! empty($list)) {
                // fallback por nombre: "IVA 21" / "21%"
                $found = collect($list)->first(function ($t) use ($percent) {
                    $name = strtolower((string) ($t['name'] ?? ''));

                    return str_contains($name, (string) (int) $percent);
                });
            }

            return self::$holdedTaxCache[$k] = $found['id'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('HOLDED:taxes_exception', ['ex' => $e->getMessage()]);

            return self::$holdedTaxCache[$k] = null;
        }
    }

    /**
     * Construye la(s) línea(s) de factura en el formato que mejor se imprime en plantillas:
     * - description: texto del concepto
     * - quantity: unidades
     * - price: neto unitario (sin IVA)
     * - taxes: IDs de impuestos (array)
     * También setea 'name' por compatibilidad con cuentas que lo muestran.
     */
    private function buildInvoiceLines(string $concept, float $units, float $unitNet, ?string $taxId): array
    {
        $line = [
            // En muchas plantillas, este es el que sale como “Concepto”
            'description' => $concept,
            // Compatibilidad: algunas cuentas muestran 'name'
            'name' => $concept,
            'quantity' => max($units, 1),
            // Holded interpreta 'price' como base unitaria (neto)
            'price' => round($unitNet, 2),
        ];

        if ($taxId) {
            $line['taxes'] = [$taxId];
        }

        return [$line];
    }
}
