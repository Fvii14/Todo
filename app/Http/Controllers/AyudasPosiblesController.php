<?php

namespace App\Http\Controllers;

use App\Events\EventUserUpdated;
use App\Helpers\SimulationHelper;
use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\AyudaRequisito;
use App\Models\AyudaSolicitada;
use App\Models\Ccaa;
use App\Models\Contratacion;
use App\Models\Conviviente;
use App\Models\Provincia;
use App\Models\Question;
use App\Services\BrevoService;
use App\Services\EvaluadorAyudaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AyudasPosiblesController extends Controller
{
    public function validateCondition($userAnswer, $expected)
    {
        if (is_null($expected)) {
            return true;
        }

        if (preg_match('/([<>]=?)(\d+)/', $expected, $matches)) {
            $operator = $matches[1];
            $threshold = (int) $matches[2];

            switch ($operator) {
                case '<':
                    return $userAnswer < $threshold;
                case '>':
                    return $userAnswer > $threshold;
                case '<=':
                    return $userAnswer <= $threshold;
                case '>=':
                    return $userAnswer >= $threshold;
                default:
                    return false;
            }
        }

        if ($expected === '1' || $expected === '0') {
            return (bool) $userAnswer === (bool) $expected;
        }

        return false;
    }

    public function index(Request $request)
    {
        $user = SimulationHelper::getCurrentUser();
        if (! $user->onboarding_done && ! SimulationHelper::isSimulating()) {
            return redirect()->route('user.onboarding');
        }
        $ref_code = $request->cookie('ref_code');
        $ref_code_user = $user->ref_code;
        $user_ccaa = SimulationHelper::getCurrentUserAnswer(38)->answer ?? null;

        $today = Carbon::today();

        $isComodinUser = $user->email === 'comodin@tutramitefacil.es';
        $isNotProduction = config('app.env') !== 'production';

        $ayudas = Ayuda::with([
            'cuestionarioPrincipal.questions',
        ])
            ->where('activo', 1)
            ->when($user_ccaa && ! ($isComodinUser && $isNotProduction), function ($query) use ($user_ccaa) {
                $query->where(function ($q) use ($user_ccaa) {
                    $q->where('ccaa_id', $user_ccaa)
                        ->orWhereNull('ccaa_id');
                });
            })
            ->orderByRaw('(fecha_inicio_periodo <= ? AND fecha_fin_periodo >= ?) DESC', [$today->toDateString(), $today->toDateString()])
            ->orderByRaw('CASE WHEN fecha_fin_periodo IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_fin_periodo', 'asc')
            ->get();

        $ayudasSolicitadas = Contratacion::with(['ayuda.organo', 'ayuda.enlaces', 'estadosContratacion'])
            ->where('user_id', $user->id)
            ->orderBy('fecha_contratacion', 'desc')
            ->get();

        $documentacionCount = $ayudasSolicitadas
            ->filter(fn ($c) => $c->estadosContratacion->contains('codigo', 'OP1-Documentacion'))
            ->count();

        $userAnswers = SimulationHelper::getCurrentUserAnswers()->keyBy('question_id');

        $ayudaRequisitos = AyudaRequisito::all()->keyBy('question_id');
        $contratacionesIds = Contratacion::where('user_id', SimulationHelper::getCurrentUserId())
            ->pluck('ayuda_id')
            ->toArray();

        $ayudasSolicitadasData = AyudaSolicitada::where('user_id', SimulationHelper::getCurrentUserId())
            ->get(['ayuda_id', 'estado'])
            ->toArray();
        $ayudasSolicitadasIds = array_column($ayudasSolicitadasData, 'ayuda_id');

        // Si no tiene DNI/NIE (pregunta ID 60 == 0), no se le muestra ninguna ayuda
        if (isset($userAnswers[60]) && $userAnswers[60]->answer == '0' && ! ($isComodinUser && $isNotProduction)) {
            $ayudasFiltradas = collect();
            $cuantia_total = 0;
            $motivo_sin_ayudas = 'sin_dni';

            return view('user.home', [
                'user' => $user,
                'ayudas' => $ayudasFiltradas,
                'cuantia_total' => $cuantia_total,
                'ref_code' => $ref_code,
                'ref_code_user' => $ref_code_user,
                'motivo_sin_ayudas' => $motivo_sin_ayudas,
            ]);
        }

        $evaluator = app(EvaluadorAyudaService::class);
        $currentUserId = SimulationHelper::getCurrentUserId();

        if ($isComodinUser && $isNotProduction) {
            $ayudasFiltradas = $ayudas->filter(function ($ayuda) use ($contratacionesIds) {
                return ! in_array($ayuda->id, $contratacionesIds);
            })->filter(function ($ayuda) use ($ayudasSolicitadasData) {
                foreach ($ayudasSolicitadasData as $solicitada) {
                    if ($solicitada['ayuda_id'] == $ayuda->id && $solicitada['estado'] === 'Rechazado') {
                        return false;
                    }
                }

                return true;
            })->map(function ($ayuda) use ($ayudasSolicitadasIds) {
                $ayuda->estado_plazo = $this->obtenerEstadoPlazo($ayuda);
                $ayuda->yaComenzada = in_array($ayuda->id, $ayudasSolicitadasIds);

                return $ayuda;
            });
        } else {
            $ayudaIds = $ayudas->pluck('id')->toArray();
            $posiblesPorAyuda = $evaluator->posiblesAyudasBatch($ayudaIds, $currentUserId);
            $ayudasFiltradas = $ayudas->filter(function ($ayuda) use ($posiblesPorAyuda, $contratacionesIds) {
                $evaluatorResult = $posiblesPorAyuda[$ayuda->id] ?? true;
                $notContracted = ! in_array($ayuda->id, $contratacionesIds);

                return $evaluatorResult && $notContracted;
            })->filter(function ($ayuda) use ($ayudasSolicitadasData) {
                foreach ($ayudasSolicitadasData as $solicitada) {
                    if ($solicitada['ayuda_id'] == $ayuda->id && $solicitada['estado'] === 'Rechazado') {
                        return false;
                    }
                }

                return true;
            })->map(function ($ayuda) use ($ayudasSolicitadasIds) {
                $ayuda->estado_plazo = $this->obtenerEstadoPlazo($ayuda);
                $ayuda->yaComenzada = in_array($ayuda->id, $ayudasSolicitadasIds);

                return $ayuda;
            });
        }

        $cuantia_total = $ayudasFiltradas->sum('cuantia_usuario');

        $provinciaAnswer = Answer::where('user_id', $user->id)
            ->where('question_id', 36)
            ->whereNull('conviviente_id')
            ->first();

        $esMadrid = false;
        if ($provinciaAnswer) {
            $provincia = Provincia::where('nombre_provincia', $provinciaAnswer->answer)->first();
            if (! $provincia) {
                $provincia = Provincia::where('nombre_provincia', 'like', $provinciaAnswer->answer)->first();
            }
            if ($provincia && $provincia->id_ccaa == 3) {
                $esMadrid = true;
            }
        }

        if ($esMadrid && $user->email) {
            try {
                $brevoService = app(BrevoService::class);

                $telefono = Answer::where('user_id', $user->id)
                    ->where('question_id', 45)
                    ->whereNull('conviviente_id')
                    ->value('answer');

                if ($telefono) {
                    $telefonoSinSignos = preg_replace('/[^0-9]/', '', $telefono);
                    $telefonoFormateado = '34'.$telefonoSinSignos;

                    $brevoService->updateContact([
                        'email' => $user->email,
                        'TELEFONO' => $telefono,
                        'WHATSAPP' => $telefonoFormateado,
                    ]);
                }

                $brevoService->addEmailsToList(178, [$user->email]);

            } catch (\Throwable $e) {
                Log::warning('Error al añadir usuario de Madrid a lista Brevo 178: '.$e->getMessage(), [
                    'email' => $user->email,
                    'provincia_id' => $provinciaAnswer->answer ?? null,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $contrato_bono_cultural = Contratacion::where('user_id', SimulationHelper::getCurrentUserId())
            ->whereHas('ayuda', function ($query) {
                $query->where('slug', 'bono_cultural_joven_2025');
            })
            ->exists();

        return view('user.home', [
            'user' => $user,
            'ayudas' => $ayudasFiltradas,
            'cuantia_total' => $cuantia_total,
            'ref_code' => $ref_code,
            'ref_code_user' => $ref_code_user,
            'documentacionCount' => $documentacionCount,
            'contrato_bono_cultural' => $contrato_bono_cultural,
        ]);
    }

    public function indexOnboarding(Request $request)
    {
        $user = SimulationHelper::getCurrentUser();
        if (! $user->onboarding_done) {
            $user->onboarding_done = true;
            $user->save();
        } elseif (! SimulationHelper::isSimulating()) {
            return redirect()->route('user.home');
        }
        $ref_code = $request->cookie('ref_code');
        $ref_code_user = $user->ref_code;
        $user_ccaa = SimulationHelper::getCurrentUserAnswer(38)->answer ?? null;

        $today = Carbon::today();

        $isComodinUser = $user->email === 'comodin@tutramitefacil.es';
        $isNotProduction = config('app.env') !== 'production';

        $ayudas = Ayuda::with([
            'cuestionarioPrincipal.questions',
            'preRequisitos',
        ])
            ->where('activo', 1)
            ->when($user_ccaa && ! ($isComodinUser && $isNotProduction), function ($query) use ($user_ccaa) {
                $query->where(function ($q) use ($user_ccaa) {
                    $q->where('ccaa_id', $user_ccaa)
                        ->orWhereNull('ccaa_id');
                });
            })
            ->orderByRaw('(fecha_inicio_periodo <= ? AND fecha_fin_periodo >= ?) DESC', [$today->toDateString(), $today->toDateString()])
            ->orderByRaw('CASE WHEN fecha_fin_periodo IS NULL THEN 1 ELSE 0 END')
            ->orderBy('fecha_fin_periodo', 'asc')
            ->get();

        $userAnswers = SimulationHelper::getCurrentUserAnswers()->keyBy('question_id');

        $ayudaRequisitos = AyudaRequisito::all()->keyBy('question_id');
        $contratacionesIds = Contratacion::where('user_id', SimulationHelper::getCurrentUserId())
            ->pluck('ayuda_id')
            ->toArray();

        // Si no tiene DNI/NIE (pregunta ID 60 == 0), no se le muestra ninguna ayuda
        if (isset($userAnswers[60]) && $userAnswers[60]->answer == '0' && ! ($isComodinUser && $isNotProduction)) {
            $ayudasFiltradas = collect();
            $cuantia_total = 0;
            $motivo_sin_ayudas = 'sin_dni';

            /*UserAyuda::updateOrCreate([
                'user_id' => $user->id,
                'ayuda_id' => null,
            ], [
                'estado_comercial' => 'frio',
                'pipeline' => 'No cualificado',
            ]);

            CrmStateHistory::create([
                'user_id' => $user->id,
                'ayuda_id' => null,
                'from_stage' => 'Test hecho',
                'to_stage' => 'No cualificado',
                'from_temp' => 'tibio',
                'to_temp' => 'frio',
                'event' => 'no_helps_detected',
                'created_at' => now(),
                'updated_at' => now(),
            ]);*/

            return view('user.home', [
                'user' => $user,
                'ayudas' => $ayudasFiltradas,
                'cuantia_total' => $cuantia_total,
                'ref_code' => $ref_code,
                'ref_code_user' => $ref_code_user,
                'motivo_sin_ayudas' => $motivo_sin_ayudas,
            ]);
        }

        $evaluator = app(EvaluadorAyudaService::class);
        $currentUserId = SimulationHelper::getCurrentUserId();

        if ($isComodinUser && $isNotProduction) {
            $ayudasFiltradas = $ayudas->filter(function ($ayuda) use ($contratacionesIds) {
                return ! in_array($ayuda->id, $contratacionesIds);
            })->map(function ($ayuda) use ($user) {
                $ayuda->estado_plazo = $this->obtenerEstadoPlazo($ayuda);
                $ayuda->hasPrerequisites = $ayuda->preRequisitos->isNotEmpty();
                if ($ayuda->hasPrerequisites) {
                    $ayuda->prerequisitesInfo = $this->checkPrerequisitesForUser($ayuda, $user);
                }

                return $ayuda;
            });
        } else {
            $ayudaIds = $ayudas->pluck('id')->toArray();
            $posiblesMap = $evaluator->posiblesAyudasBatch($ayudaIds, $currentUserId);

            $ayudasFiltradas = $ayudas->filter(function ($ayuda) use ($posiblesMap) {
                return $posiblesMap[$ayuda->id] ?? false;
            })->filter(function ($ayuda) use ($user) {
                if ($ayuda->preRequisitos->isNotEmpty()) {
                    foreach ($ayuda->preRequisitos as $preReq) {
                        $userMeets = $this->checkPreRequisito($preReq, $user);
                        if ($userMeets === false) {
                            return false;
                        }
                    }
                }

                return true;
            })->map(function ($ayuda) use ($user) {
                $ayuda->estado_plazo = $this->obtenerEstadoPlazo($ayuda);
                $ayuda->hasPrerequisites = $ayuda->preRequisitos->isNotEmpty();
                if ($ayuda->hasPrerequisites) {
                    $ayuda->prerequisitesInfo = $this->checkPrerequisitesForUser($ayuda, $user);
                }

                return $ayuda;
            });
        }

        $bonoCultural = $ayudasFiltradas->firstWhere('slug', 'bono_cultural_joven_2025');

        $cuantia_total = $ayudasFiltradas->sum('cuantia_usuario');

        $numero_ayudas = $ayudasFiltradas->count();

        if ($numero_ayudas == 0) {
            /*UserAyuda::updateOrCreate([
                'user_id' => $user->id,
                'ayuda_id' => null,
            ], [
                'estado_comercial' => 'frio',
                'pipeline' => 'No cualificado',
            ]);*/
        } else {
            /*UserAyuda::where('user_id', $user->id)
                ->whereNull('ayuda_id')
                ->delete();*/

            $ayudaIds = $ayudasFiltradas->pluck('id')->toArray();

            foreach ($ayudaIds as $ayudaId) {
                /*UserAyuda::updateOrCreate([
                    'user_id' => $user->id,
                    'ayuda_id' => $ayudaId,
                ], [
                    'estado_comercial' => 'tibio',
                    'pipeline' => 'Cualificado',
                ]);
                CrmStateHistory::create([
                    'user_id' => $user->id,
                    'ayuda_id' => $ayudaId,
                    'from_stage' => 'Test hecho',
                    'to_stage' => 'Cualificado',
                    'from_temp' => 'frio',
                    'to_temp' => 'tibio',
                    'event' => 'helps_detected',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);*/
            }
        }

        $ayudasResumen = "Hemos detectado $numero_ayudas ayudas públicas que puedes pedir, por un máximo de ".number_format($cuantia_total, 0, ',', '.').' €';

        if (empty($ayudasResumen)) {
            $ayudasResumen = 'No hay ayudas disponibles en este momento.';
        }

        // Disparar evento para actualizar contacto en Brevo
        if ($user) {
            // Obtener datos del usuario desde las respuestas
            $telefono = $user->telefono; // question_id 45
            $nombre = $user->nombrePila(); // question_id 177 o nombre completo
            $apellido1 = $user->apellido1; // question_id 170
            $apellido2 = $user->apellido2; // question_id 171
            $provincia = $user->provincia; // question_id 36
            $municipio = $user->municipio; // question_id 37

            $ccaa = null;
            $provinciaAnswer = $user->answers->firstWhere('question_id', 36);
            if ($provinciaAnswer) {
                $provinciaValue = $provinciaAnswer->answer;
                $provinciaModel = null;

                if (is_numeric($provinciaValue)) {
                    $provinciaModel = Provincia::find($provinciaValue);
                } else {
                    $provinciaModel = Provincia::where('nombre_provincia', $provinciaValue)->first();
                    if (! $provinciaModel) {
                        $provinciaModel = Provincia::where('nombre_provincia', 'like', $provinciaValue)->first();
                    }
                }

                if ($provinciaModel && $provinciaModel->id_ccaa) {
                    $ccaaModel = Ccaa::find($provinciaModel->id_ccaa);
                    $ccaa = $ccaaModel ? $ccaaModel->nombre_ccaa : null;
                }
            }

            $whatsapp = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;
            if ($whatsapp && ! str_starts_with($whatsapp, '34')) {
                $whatsapp = '34'.$whatsapp;
            }

            event(new EventUserUpdated($user, data: [
                'id' => $user->brevo_id,
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $telefono,
                'firstname' => $nombre,
                'lastname' => trim(collect([$apellido1, $apellido2])->filter()->implode(' ')),
                'provincia' => $provincia,
                'ccaa' => $ccaa,
            ]));
        }

        /*
        try {
            // Detectar si hay solo 1 ayuda y es bono cultural
            $soloBonoCultural = $ayudasFiltradas->count() === 1 && $bonoCultural;
            // Si hay bono cultural, enviar su email
            if ($bonoCultural && $user->email) {
                $nombre = $user->nombrePila() ?? '';
                Mail::to($user->email)->send(new \App\Mail\BonoCulturalJovenMail($user, 1, $nombre));
                MailTracking::track($user, \App\Mail\BonoCulturalJovenMail::class, 1);
            }

            // Si hay otras ayudas además del bono cultural, enviar FirstVisitMail
            if (! $soloBonoCultural && $user->email) {
                Mail::to($user->email)->send(new FirstVisitMail($user, 1, $ayudasFiltradas));
                MailTracking::track($user, FirstVisitMail::class, 1);
            }
            $telefono = Answer::where('user_id', $user->id)
                ->where('question_id', 45)
                ->whereNull('conviviente_id')
                ->value('answer');

            if ($telefono) {
                $brevoService = app(\App\Services\BrevoService::class);
                $telefonoSinSignos = preg_replace('/[^0-9]/', '', $telefono);
                $telefonoFormateado = '34'.$telefonoSinSignos;

                $brevoService->updateContact([
                    'email' => $user->email,
                    'TELEFONO' => $telefono,
                    'WHATSAPP' => $telefonoFormateado,
                    'AYUDAS_POSIBLES' => $ayudasResumen,
                ]);

                $brevoService->sendSimpleWhatsAppMessage($telefonoFormateado, 304);
            } else {
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo: '.$e->getMessage());
        }
        MetricaController::automaticalTrack(routeName: 'onboarding');*/

        return view('user.onboarding', [
            'user' => $user,
            'ayudas' => $ayudasFiltradas,
            'cuantia_total' => $cuantia_total,
            'ref_code' => $ref_code,
            'ref_code_user' => $ref_code_user,
        ]);
    }

    private function checkPrerequisitesForUser($ayuda, $user)
    {
        $prerequisitesInfo = [];

        foreach ($ayuda->preRequisitos as $preReq) {
            $userMeets = $this->checkPreRequisito($preReq, $user);
            $userAnswer = $this->getUserAnswer($preReq, $user);
            $targetInfo = $this->getTargetDisplayInfo($preReq, $user);

            $prerequisitesInfo[] = [
                'id' => $preReq->id,
                'name' => $preReq->name,
                'description' => $preReq->description,
                'target_type' => $preReq->target_type,
                'conviviente_type' => $preReq->conviviente_type,
                'question_id' => $preReq->question_id,
                'fallback_question_id' => $this->getFallbackQuestionIdForPreReq($preReq),
                'operator' => $preReq->operator,
                'value' => $preReq->value,
                'value2' => $preReq->value2,
                'value_type' => $preReq->value_type,
                'age_unit' => $preReq->age_unit,
                'userMeets' => $userMeets,
                'userAnswer' => $userAnswer,
                'target_info' => $targetInfo,
            ];
        }

        return $prerequisitesInfo;
    }

    private function checkPreRequisito($preReq, $user)
    {
        if (isset($preReq->type) && $preReq->type === 'group') {
            return $this->evaluateGroupRules($preReq->groupRules ?? [], $user, $preReq->group_logic ?? 'AND');
        }

        switch ($preReq->target_type) {
            case 'solicitante':
                return $this->checkSolicitantePreRequisito($preReq, $user);
            case 'conviviente':
                return $this->checkConvivientePreRequisito($preReq, $user);
            case 'unidad_convivencia_completa':
            case 'unidad_convivencia_sin_solicitante':
            case 'unidad_familiar_completa':
            case 'unidad_familiar_sin_solicitante':
            case 'any_conviviente':
            case 'any_familiar':
            case 'any_persona_unidad':
                return $this->checkUnidadPreRequisito($preReq, $user);
            default:
                return false;
        }
    }

    private function evaluateGroupRules($rules, $user, $logic = 'AND')
    {
        if (empty($rules)) {
            return true;
        }

        $sawNull = false;

        foreach ($rules as $rule) {
            $tempPreReq = (object) [
                'target_type' => $rule->target_type ?? 'solicitante',
                'conviviente_type' => $rule->conviviente_type ?? null,
                'question_id' => $rule->question_id ?? null,
                'operator' => $rule->operator ?? null,
                'value' => $rule->value ?? null,
                'value2' => $rule->value2 ?? null,
                'value_type' => $rule->value_type ?? 'exact',
                'age_unit' => $rule->age_unit ?? 'years',
            ];

            $result = $this->evaluateRule($tempPreReq, $user);

            if ($logic === 'OR') {
                if ($result === true) {
                    return true;
                }
                if ($result === null) {
                    $sawNull = true;
                }
            } else {
                if ($result === false) {
                    return false;
                }
                if ($result === null) {
                    $sawNull = true;
                }
            }
        }

        if ($logic === 'OR') {
            return $sawNull ? null : false;
        }

        return $sawNull ? null : true;
    }

    private function evaluateRule($preReq, $user)
    {
        switch ($preReq->target_type) {
            case 'solicitante':
                return $this->checkSolicitantePreRequisito($preReq, $user);
            case 'conviviente':
                return $this->checkConvivientePreRequisito($preReq, $user);
            case 'unidad_convivencia_completa':
            case 'unidad_convivencia_sin_solicitante':
            case 'unidad_familiar_completa':
            case 'unidad_familiar_sin_solicitante':
            case 'any_conviviente':
            case 'any_familiar':
            case 'any_persona_unidad':
                return $this->checkUnidadPreRequisito($preReq, $user);
            default:
                return false;
        }
    }

    private function checkSolicitantePreRequisito($preReq, $user)
    {
        $answer = Answer::where('user_id', $user->id)
            ->where('question_id', $preReq->question_id)
            ->whereNull('conviviente_id')
            ->first();

        if (! $answer) {
            return null;
        }

        return $this->evaluateAnswer($answer, $preReq);
    }

    private function checkConvivientePreRequisito($preReq, $user)
    {
        $convivientes = $this->getConvivientesForTargetType($preReq->target_type, $preReq->conviviente_type, $user);

        if ($convivientes->isEmpty()) {
            return null;
        }

        $hasAnyAnswer = false;
        foreach ($convivientes as $conviviente) {
            $answer = Answer::where('conviviente_id', $conviviente->id)
                ->where('question_id', $preReq->question_id)
                ->first();

            if ($answer) {
                $hasAnyAnswer = true;
                if ($this->evaluateAnswer($answer, $preReq)) {
                    return true;
                }
            }
        }

        if (! $hasAnyAnswer) {
            return null;
        }

        return false;
    }

    private function checkUnidadPreRequisito($preReq, $user)
    {
        $convivientes = $this->getConvivientesForTargetType($preReq->target_type, $preReq->conviviente_type, $user);

        if ($convivientes->isEmpty()) {
            $userAnswer = Answer::where('user_id', $user->id)
                ->where('question_id', $preReq->question_id)
                ->whereNull('conviviente_id')
                ->first();
            if ($userAnswer) {
                return $this->evaluateAnswer($userAnswer, $preReq);
            }

            return null;
        }

        $hasAnyAnswer = false;
        foreach ($convivientes as $conviviente) {
            $answer = Answer::where('conviviente_id', $conviviente->id)
                ->where('question_id', $preReq->question_id)
                ->first();

            if ($answer) {
                $hasAnyAnswer = true;
                if ($this->evaluateAnswer($answer, $preReq)) {
                    return true;
                }
            }
        }

        $userAnswer = Answer::where('user_id', $user->id)
            ->where('question_id', $preReq->question_id)
            ->whereNull('conviviente_id')
            ->first();
        if ($userAnswer) {
            return $this->evaluateAnswer($userAnswer, $preReq);
        }

        if (! $hasAnyAnswer) {
            return null;
        }

        return false;
    }

    private function getUserAnswer($preReq, $user)
    {
        if (isset($preReq->type) && $preReq->type === 'group') {
            return null;
        }

        if ($preReq->target_type === 'solicitante') {
            $answer = Answer::where('user_id', $user->id)
                ->where('question_id', $preReq->question_id)
                ->whereNull('conviviente_id')
                ->first();
        } else {
            $convivientes = $this->getConvivientesForTargetType($preReq->target_type, $preReq->conviviente_type, $user);
            $answer = null;

            foreach ($convivientes as $conviviente) {
                $answer = Answer::where('conviviente_id', $conviviente->id)
                    ->where('question_id', $preReq->question_id)
                    ->first();
                if ($answer) {
                    break;
                }
            }
            if (! $answer) {
                $answer = Answer::where('user_id', $user->id)
                    ->where('question_id', $preReq->question_id)
                    ->whereNull('conviviente_id')
                    ->first();
            }
        }

        return $answer ? $answer->answer : null;
    }

    private function getFallbackQuestionIdForPreReq($preReq)
    {
        if (! empty($preReq->question_id)) {
            return $preReq->question_id;
        }

        if (isset($preReq->type) && $preReq->type === 'group') {
            $rules = $preReq->groupRules ?? [];
            if ($rules instanceof \Illuminate\Support\Collection) {
                $rule = $rules->first(function ($r) {
                    return ! empty($r->question_id);
                });
                if ($rule) {
                    return $rule->question_id;
                }
            } elseif (is_array($rules)) {
                foreach ($rules as $r) {
                    if (! empty($r['question_id'])) {
                        return $r['question_id'];
                    }
                }
            }
        }

        return null;
    }

    private function getTargetDisplayInfo($preReq, $user)
    {
        switch ($preReq->target_type) {
            case 'solicitante':
                return [
                    'type' => 'solicitante',
                    'display_name' => 'Tú',
                    'description' => 'Necesitamos información sobre ti',
                ];

            case 'conviviente':
                $convivientes = $this->getConvivientesForTargetType($preReq->target_type, $preReq->conviviente_type, $user);
                if ($convivientes->isNotEmpty()) {
                    $conviviente = $convivientes->first();
                    $convivienteInfo = $this->getConvivienteDisplayInfo($conviviente);

                    return [
                        'type' => 'conviviente',
                        'conviviente_type' => $preReq->conviviente_type,
                        'display_name' => $convivienteInfo['nombre'],
                        'description' => "Necesitamos información sobre {$convivienteInfo['nombre']}",
                        'conviviente_info' => $convivienteInfo,
                    ];
                } else {
                    $fallbackName = $this->getConvivienteTypeFromPreReq($preReq);

                    return [
                        'type' => 'conviviente',
                        'conviviente_type' => $preReq->conviviente_type,
                        'display_name' => $fallbackName,
                        'description' => "Necesitamos información sobre tu {$fallbackName}",
                    ];
                }

            case 'unidad_convivencia_completa':
                return [
                    'type' => 'unidad_convivencia_completa',
                    'display_name' => 'Tu hogar',
                    'description' => 'Necesitamos información sobre tu hogar (incluyéndote a ti)',
                ];

            case 'unidad_convivencia_sin_solicitante':
                return [
                    'type' => 'unidad_convivencia_sin_solicitante',
                    'display_name' => 'Tu hogar',
                    'description' => 'Necesitamos información sobre tu hogar (sin incluirte a ti)',
                ];

            case 'unidad_familiar_completa':
                return [
                    'type' => 'unidad_familiar_completa',
                    'display_name' => 'Tu familia',
                    'description' => 'Necesitamos información sobre tu familia (incluyéndote a ti)',
                ];

            case 'unidad_familiar_sin_solicitante':
                return [
                    'type' => 'unidad_familiar_sin_solicitante',
                    'display_name' => 'Tu familia',
                    'description' => 'Necesitamos información sobre tu familia (sin incluirte a ti)',
                ];

            case 'any_conviviente':
                return [
                    'type' => 'any_conviviente',
                    'display_name' => 'Alguien de tu hogar',
                    'description' => 'Necesitamos información sobre alguien de tu hogar',
                ];

            case 'any_familiar':
                return [
                    'type' => 'any_familiar',
                    'display_name' => 'Alguien de tu familia',
                    'description' => 'Necesitamos información sobre alguien de tu familia',
                ];

            case 'any_persona_unidad':
                return [
                    'type' => 'any_persona_unidad',
                    'display_name' => 'Alguien de tu hogar',
                    'description' => 'Necesitamos información sobre alguien de tu hogar',
                ];

            default:
                return [
                    'type' => $preReq->target_type,
                    'display_name' => 'Información requerida',
                    'description' => 'Necesitamos información adicional para continuar',
                ];
        }
    }

    private function getConvivientesForTargetType($targetType, $convivienteType, $user)
    {
        $convivientes = Conviviente::where('user_id', $user->id);

        if ($convivienteType) {
            $mappedType = $this->getConvivienteTypeFromString($convivienteType);
            $convivientes->where('tipo', $mappedType);
        }

        return $convivientes->get();
    }

    private function getConvivienteDisplayInfo($conviviente)
    {
        $nombreQuestion = Question::where('slug', 'nombre')
            ->orWhere('slug', 'solo_nombre')
            ->orWhere('slug', 'nombre_completo')
            ->first();

        $nombre = null;
        if ($nombreQuestion) {
            $nombreAnswer = Answer::where('conviviente_id', $conviviente->id)
                ->where('question_id', $nombreQuestion->id)
                ->first();
            $nombre = $nombreAnswer ? $nombreAnswer->answer : null;
        }

        if (! $nombre || empty($nombre)) {
            $nombre = $conviviente->tipo.' '.$conviviente->index;
        }

        return [
            'id' => $conviviente->id,
            'tipo' => $conviviente->tipo,
            'index' => $conviviente->index,
            'nombre' => $nombre,
        ];
    }

    private function getConvivienteTypeFromString($convivienteType)
    {
        $mapping = [
            'conyuge' => 'Cónyuge',
            'hijo' => 'Hijo/a',
            'padre' => 'Padre/Madre',
            'otro' => 'Otro',
            'no_familiar' => 'No familiar',
        ];

        return $mapping[$convivienteType] ?? $convivienteType;
    }

    private function getConvivienteTypeFromPreReq($preReq)
    {
        return $this->getConvivienteTypeFromString($preReq->conviviente_type);
    }

    private function evaluateAnswer($answer, $preReq)
    {
        if ($preReq->value_type === 'age_minimum' || $preReq->value_type === 'age_maximum' || $preReq->value_type === 'age_range') {
            return $this->evaluateAgeAnswer($answer, $preReq);
        }

        $value = $preReq->value;
        if (is_array($value)) {
            $value = $value[0] ?? $value;
        }

        $answerValue = is_object($answer) ? $answer->answer : $answer;

        switch ($preReq->operator) {
            case '==':
                return $answerValue == $value;
            case '!=':
                return $answerValue != $value;
            case '>':
                return $answerValue > $value;
            case '>=':
                return $answerValue >= $value;
            case '<':
                return $answerValue < $value;
            case '<=':
                return $answerValue <= $value;
            case 'contains':
                return strpos($answerValue, $value) !== false;
            case 'not_contains':
                return strpos($answerValue, $value) === false;
            case 'in':
                return in_array($answerValue, (array) $value);
            case 'not_in':
                return ! in_array($answerValue, (array) $value);
            case 'between':
                return $answerValue >= $value && $answerValue <= ($preReq->value2 ?? $value);
            default:
                return false;
        }
    }

    private function evaluateAgeAnswer($answer, $preReq)
    {
        if (is_object($answer)) {
            $question = Question::find($answer->question_id);

            if ($question && $this->isFechaNacimientoQuestion($question)) {
                $fechaNacimiento = Carbon::parse($answer->answer);
            } else {
                $fechaNacimiento = $this->getFechaNacimientoFromAnswer($answer->answer);
            }
        } else {
            $fechaNacimiento = $this->getFechaNacimientoFromAnswer($answer);
        }

        if (! $fechaNacimiento) {
            return false;
        }

        $age = $this->calculateAge($fechaNacimiento, $preReq->age_unit);

        switch ($preReq->value_type) {
            case 'age_minimum':
                return $age >= $preReq->value;
            case 'age_maximum':
                return $age <= $preReq->value;
            case 'age_range':
                return $age >= $preReq->value && $age <= ($preReq->value2 ?? $preReq->value);
            default:
                return false;
        }
    }

    private function isFechaNacimientoQuestion($question)
    {
        return $question->slug === 'fecha_nacimiento' ||
            $question->slug === 'fecha_de_nacimiento' ||
            str_contains(strtolower($question->text), 'fecha') && str_contains(strtolower($question->text), 'nacimiento');
    }

    private function getFechaNacimientoFromAnswer($answer)
    {
        if (is_string($answer) && preg_match('/^\d{4}-\d{2}-\d{2}/', $answer)) {
            return Carbon::parse($answer);
        }

        return null;
    }

    private function calculateAge($fechaNacimiento, $unit)
    {
        $now = Carbon::now();

        if ($fechaNacimiento->isFuture()) {
            return 0;
        }

        switch ($unit) {
            case 'years':
                return $fechaNacimiento->diffInYears($now);
            case 'months':
                return $fechaNacimiento->diffInMonths($now);
            case 'days':
                return $fechaNacimiento->diffInDays($now);
            default:
                return $fechaNacimiento->diffInYears($now);
        }
    }

    public function obtenerEstadoPlazo($ayuda)
    {
        $hoy = Carbon::now()->startOfDay();
        $inicio = Carbon::parse($ayuda->fecha_inicio_periodo)->startOfDay();
        $fin = $ayuda->fecha_fin_periodo ? Carbon::parse($ayuda->fecha_fin_periodo)->endOfDay() : null;

        // Si el plazo está actualmente abierto
        if ($inicio->lte($hoy) && ($fin === null || $hoy->lte($fin))) {
            return 'plazo-abierto';
        }

        // Si el plazo aún no ha empezado
        if ($inicio->gt($hoy)) {
            $diasParaAbrir = $hoy->diffInDays($inicio);

            if ($diasParaAbrir <= 30) {
                return 'plazo-abierto'; // Abrirá pronto (en menos de 30 días)
            } elseif ($diasParaAbrir <= 45) {
                return 'plazo-pronto'; // Abrirá entre 30 y 45 días
            } else {
                return 'plazo-cerrado'; // Más de 45 días para que abra → sin clase especial
            }
        }

        // Si ya ha pasado la fecha de fin del periodo
        if ($fin !== null && $hoy->gt($fin)) {
            return 'plazo-cerrado'; // Consideramos como no visible con estilos
        }

        return 'plazo-cerrado'; // Cualquier otro caso
    }
}
