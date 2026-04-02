<?php

use App\Helpers\EndpointsHelper;
use App\Http\Controllers\AyudasSolicitadasController;
use App\Http\Controllers\OnboarderController;
use App\Http\Controllers\OnboarderMetricController;
use App\Http\Controllers\TaskTechController;
use App\Http\Controllers\WizardController;
use App\Models\Answer;
use App\Models\AyudaSolicitada;
use App\Models\Contratacion;
use App\Models\Document;
use App\Models\Question;
use App\Models\User;
use App\Models\UserDocument;
use App\Services\DocumentosAyudaService;
use App\Services\GcsUploaderService;
use App\Services\MailTrackerService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

Route::post('/internal/mail-scheduler', function (Request $request, MailTrackerService $scheduler) {
    abort_unless(
        $request->header('X-Internal-Secret') === env('SCHEDULER_SECRET'),
        403
    );

    $scheduler->WelcomeMail();
    $scheduler->FirstVisitMail();
    $scheduler->ContratacionMail();
    $scheduler->BonoCulturalJoven();
    $scheduler->BonoCulturalDocumentacionMail();
    $scheduler->SeguimientoReferidosBonoCultural();
    $scheduler->RecordatorioPostAvisoReferido();

    return response()->json(['status' => 'ok']);
});

Route::post('/internal/dashboard-rocket', function (Request $request) {
    abort_unless(
        $request->header('X-Internal-Secret') === env('DASHBOARD_SECRET'),
        403
    );

    $usuarios = User::where('is_admin', 0)->count();
    $usuariosConBankflip = Answer::where('question_id', Question::where('slug', 'fecha_collector')->value('id'))->distinct('user_id')->count('user_id');
    $usuariosSinBankflip = $usuarios - $usuariosConBankflip;
    $ayudasSolicitidas = AyudaSolicitada::count();
    $contrataciones = Contratacion::count();

    return response()->json(['status' => 'ok', 'usuarios' => $usuarios, 'usuariosConBankflip' => $usuariosConBankflip, 'usuariosSinBankflip' => $usuariosSinBankflip, 'ayudasSolicitidas' => $ayudasSolicitidas, 'contrataciones' => $contrataciones]);
});

Route::post('/internal/usuarios-autorizacion-pav', function (Request $request) {

    abort_unless(
        $request->header('X-Internal-Secret') === env('SCRIPT_SECRET'),
        403
    );

    try {
        $usuarios = DB::table('users as u')
            ->select(['u.id as user_id', 'u.name as user_name', 'u.email'])
            ->orderBy('u.name')
            ->get();

        if ($usuarios->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'usuarios' => [],
                'debug' => ['total_usuarios' => 0],
            ]);
        }

        $userIds = $usuarios->pluck('user_id')->unique()->values()->toArray();

        $answersSolicitante = [];
        $chunkSize = 1000;

        foreach (array_chunk($userIds, $chunkSize) as $chunk) {
            $chunkAnswers = DB::table('answers')
                ->whereIn('user_id', $chunk)
                ->whereNull('conviviente_id')
                ->whereIn('question_id', [177, 170, 171, 34, 40, 104, 105, 106, 110, 111, 39, 37, 36, 45])
                ->select('user_id', 'question_id', 'answer')
                ->get();

            foreach ($chunkAnswers as $answer) {
                if (! isset($answersSolicitante[$answer->user_id])) {
                    $answersSolicitante[$answer->user_id] = [];
                }
                $answersSolicitante[$answer->user_id][$answer->question_id] = $answer->answer;
            }
        }

        $convivientes = [];
        $convivientesPorUsuario = [];

        foreach (array_chunk($userIds, $chunkSize) as $chunk) {
            $chunkConvivientes = DB::table('convivientes')
                ->whereIn('user_id', $chunk)
                ->orderBy('user_id')
                ->orderBy('index')
                ->get();

            foreach ($chunkConvivientes as $conviviente) {
                $convivientes[$conviviente->id] = $conviviente;
                if (! isset($convivientesPorUsuario[$conviviente->user_id])) {
                    $convivientesPorUsuario[$conviviente->user_id] = [];
                }
                $convivientesPorUsuario[$conviviente->user_id][] = $conviviente;
            }
        }

        $convivienteIds = array_keys($convivientes);
        $answersConvivientes = [];

        if (! empty($convivienteIds)) {
            foreach (array_chunk($convivienteIds, $chunkSize) as $chunk) {
                $chunkAnswers = DB::table('answers')
                    ->whereIn('conviviente_id', $chunk)
                    ->whereIn('question_id', [34, 177, 170, 171, 40])
                    ->select('conviviente_id', 'question_id', 'answer')
                    ->get();

                foreach ($chunkAnswers as $answer) {
                    if (! isset($answersConvivientes[$answer->conviviente_id])) {
                        $answersConvivientes[$answer->conviviente_id] = [];
                    }
                    $answersConvivientes[$answer->conviviente_id][$answer->question_id] = $answer->answer;
                }
            }
        }

        $usuarios = $usuarios->map(function ($usuario) use ($answersSolicitante, $convivientesPorUsuario, $answersConvivientes) {
            $answers = $answersSolicitante[$usuario->user_id] ?? [];
            $convivientesUsuario = $convivientesPorUsuario[$usuario->user_id] ?? [];

            $convivientesData = array_map(function ($conviviente) use ($answersConvivientes) {
                $convAnswers = $answersConvivientes[$conviviente->id] ?? [];

                return [
                    'conviviente_id' => $conviviente->id,
                    'index' => $conviviente->index,
                    'dni_repre' => $convAnswers[34] ?? '',
                    'nombre_repre' => $convAnswers[177] ?? '',
                    'apellido1_repre' => $convAnswers[170] ?? '',
                    'apellido2_repre' => $convAnswers[171] ?? '',
                    'fecha_nacimiento_repre' => $convAnswers[40] ?? '',
                ];
            }, $convivientesUsuario);

            return [
                'user_id' => $usuario->user_id,
                'user_name' => $usuario->user_name,
                'email' => $usuario->email,
                'solo_nombre' => $answers[177] ?? '',
                'fecha_nacimiento' => $answers[40] ?? '',
                'primer_apellido' => $answers[170] ?? '',
                'segundo_apellido' => $answers[171] ?? '',
                'dni' => $answers[34] ?? '',
                'tipo_via' => $answers[104] ?? '',
                'nombre_via' => $answers[105] ?? '',
                'numero_via' => $answers[106] ?? '',
                'piso' => $answers[110] ?? '',
                'puerta' => $answers[111] ?? '',
                'codigo_postal' => $answers[39] ?? '',
                'municipio' => $answers[37] ?? '',
                'provincia' => $answers[36] ?? 'Madrid',
                'correo' => $usuario->email ?? '',
                'telefono' => $answers[45] ?? '',
                'convivientes' => array_values($convivientesData),
            ];
        });

        return response()->json([
            'status' => 'ok',
            'usuarios' => $usuarios,
            'debug' => [
                'total_usuarios' => $usuarios->count(),
                'total_convivientes' => count($convivientes),
                'question_ids' => [
                    'solo_nombre' => 177,
                    'fecha_nacimiento' => 40,
                    'primer_apellido' => 170,
                    'segundo_apellido' => 171,
                    'dni' => 34,
                    'nombre_via' => 105,
                    'numero_via' => 106,
                    'piso' => 110,
                    'puerta' => 111,
                    'codigo_postal' => 39,
                    'municipio' => 37,
                    'provincia' => 36,
                    'telefono' => 45,
                ],
                'question_ids_convivientes' => [
                    'dni_repre' => 34,
                    'nombre_repre' => 177,
                    'apellido1_repre' => 170,
                    'apellido2_repre' => 171,
                    'fecha_nacimiento_repre' => 40,
                ],
            ],
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener usuarios: '.$e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
        ], 500);
    }
});
Route::post('/internal/actualizar-documento-pav', function (Request $request) {

    abort_unless(
        $request->header('X-Internal-Secret') === env('SCRIPT_SECRET'),
        403
    );

    try {
        $request->validate([
            'user_id' => 'required|integer',
            'url_pdf' => 'required|string',
            'document_id' => 'required|integer',
        ]);

        $user_id = $request->input('user_id');
        $url_pdf = $request->input('url_pdf');
        $document_id = $request->input('document_id');
        $randomnumber = rand(100000, 999999);
        $document = DB::table('documents')->where('id', $document_id)->first();

        if (! $document) {
            return response()->json([
                'status' => 'error',
                'message' => 'Documento no encontrado',
            ], 404);
        }

        $existing_record = DB::table('user_documents')
            ->where('user_id', $user_id)
            ->where('document_id', $document_id)
            ->first();

        if ($existing_record) {

            DB::table('user_documents')
                ->where('user_id', $user_id)
                ->where('document_id', $document_id)
                ->update([
                    'file_path' => $url_pdf,
                    'file_name' => basename($url_pdf),
                    'file_type' => 'application/pdf',
                    'size' => 0,
                    'slug' => $document->slug,
                    'nombre_personalizado' => $document->slug.'_'.$randomnumber,
                    'estado' => 'validado',
                    'updated_at' => now(),
                ]);
        } else {

            DB::table('user_documents')->insert([
                'user_id' => $user_id,
                'document_id' => $document_id,
                'file_path' => $url_pdf,
                'file_name' => basename($url_pdf),
                'file_type' => 'application/pdf',
                'size' => 0,
                'slug' => $document->slug,
                'nombre_personalizado' => $document->slug.'_'.$randomnumber,
                'estado' => 'validado',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Documento actualizado correctamente',
            'data' => [
                'user_id' => $user_id,
                'document_id' => $document_id,
                'slug' => $document->slug,
                'file_path' => $url_pdf,
                'file_name' => basename($url_pdf),
                'file_type' => 'application/pdf',
                'size' => 0,
                'nombre_personalizado' => $document->slug.'_'.$randomnumber,
                'estado' => 'validado',
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('Error en endpoint actualizar-documento-pav: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al actualizar documento: '.$e->getMessage(),
        ], 500);
    }
});
Route::post('/internal/obtener-firma-usuario', function (Request $request) {

    abort_unless(
        $request->header('X-Internal-Secret') === env('SCRIPT_SECRET'),
        403
    );

    try {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user_id = $request->input('user_id');

        $firma_document = DB::table('user_documents')
            ->where('user_id', $user_id)
            ->where('document_id', 3) // ID del documento de firma
            ->where('estado', 'validado')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $firma_document) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró documento de firma para este usuario',
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Información de firma obtenida correctamente',
            'data' => [
                'user_id' => $user_id,
                'file_path' => trim($firma_document->file_path), // Limpiar espacios y saltos de línea
                'file_name' => $firma_document->file_name,
                'file_type' => $firma_document->file_type,
                'slug' => $firma_document->slug,
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('Error en endpoint obtener-firma-usuario: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener información de firma: '.$e->getMessage(),
        ], 500);
    }
});
Route::post('/internal/obtener-firma-conviviente', function (Request $request) {

    abort_unless(
        $request->header('X-Internal-Secret') === env('SCRIPT_SECRET'),
        403
    );

    try {
        $request->validate([
            'user_id' => 'required|integer',
            'conviviente_index' => 'required|integer',
        ]);

        $user_id = $request->input('user_id');
        $conviviente_index = $request->input('conviviente_index');

        // Verificar que existe el conviviente
        $conviviente = DB::table('convivientes')
            ->where('user_id', $user_id)
            ->where('index', $conviviente_index)
            ->first();

        if (! $conviviente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Conviviente no encontrado',
            ], 404);
        }

        // Buscar el documento de firma del conviviente
        $firma_document = DB::table('user_documents')
            ->where('user_id', $user_id)
            ->where('conviviente_index', $conviviente_index)
            ->where('document_id', 3) // ID del documento de firma
            ->where('estado', 'validado')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $firma_document) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró documento de firma para este conviviente',
            ], 404);
        }

        // Extraer la extensión del file_name para el file_type
        $file_type = pathinfo($firma_document->file_name, PATHINFO_EXTENSION);

        return response()->json([
            'status' => 'ok',
            'message' => 'Información de firma del conviviente obtenida correctamente',
            'data' => [
                'user_id' => $user_id,
                'conviviente_index' => $conviviente_index,
                'file_path' => trim($firma_document->file_path), // Limpiar espacios y saltos de línea
                'file_name' => $firma_document->file_name,
                'file_type' => $file_type,
                'slug' => $firma_document->slug,
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('Error en endpoint obtener-firma-conviviente: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener información de firma del conviviente: '.$e->getMessage(),
        ], 500);
    }
});
Route::post('/internal/usuarios-domiciliacion-pav', function (Request $request) {

    abort_unless(
        $request->header('X-Internal-Secret') === env('SCRIPT_SECRET'),
        403
    );

    try {
        $script_secret = env('SCRIPT_SECRET');
        Log::info('SCRIPT_SECRET configurado: '.($script_secret ? 'Sí' : 'No'));

        $query = DB::table('users as u')
            ->select([
                'u.id as user_id',
                'u.name as user_name',
                'u.email',
                'nombre.answer as nombre',
                'primer_apellido.answer as primer_apellido',
                'segundo_apellido.answer as segundo_apellido',
                'dni.answer as dni',
                'tipo_via.answer as tipo_via',
                'nombre_via.answer as nombre_via',
                'numero_via.answer as numero_via',
                'bloque.answer as bloque',
                'portal.answer as portal',
                'escalera.answer as escalera',
                'piso.answer as piso',
                'puerta.answer as puerta',
                'municipio.answer as municipio',
                'codigo_postal.answer as codigo_postal',
                'provincia.answer as provincia',
                'telefono.answer as telefono',
                'entidad_financiera.answer as entidad_financiera',
                'numero_cuenta.answer as numero_cuenta',
                'tipo_documento_119.answer as tipo_documento_119',
            ])
            ->leftJoin('answers as nombre', function ($join) {
                $join->on('u.id', '=', 'nombre.user_id')
                    ->where('nombre.question_id', '=', 177);
            })
            ->leftJoin('answers as primer_apellido', function ($join) {
                $join->on('u.id', '=', 'primer_apellido.user_id')
                    ->where('primer_apellido.question_id', '=', 170);
            })
            ->leftJoin('answers as segundo_apellido', function ($join) {
                $join->on('u.id', '=', 'segundo_apellido.user_id')
                    ->where('segundo_apellido.question_id', '=', 171);
            })
            ->leftJoin('answers as dni', function ($join) {
                $join->on('u.id', '=', 'dni.user_id')
                    ->where('dni.question_id', '=', 34);
            })
            ->leftJoin('answers as tipo_via', function ($join) {
                $join->on('u.id', '=', 'tipo_via.user_id')
                    ->where('tipo_via.question_id', '=', 104);
            })
            ->leftJoin('answers as nombre_via', function ($join) {
                $join->on('u.id', '=', 'nombre_via.user_id')
                    ->where('nombre_via.question_id', '=', 105);
            })
            ->leftJoin('answers as numero_via', function ($join) {
                $join->on('u.id', '=', 'numero_via.user_id')
                    ->where('numero_via.question_id', '=', 106);
            })
            ->leftJoin('answers as bloque', function ($join) {
                $join->on('u.id', '=', 'bloque.user_id')
                    ->where('bloque.question_id', '=', 107);
            })
            ->leftJoin('answers as portal', function ($join) {
                $join->on('u.id', '=', 'portal.user_id')
                    ->where('portal.question_id', '=', 108);
            })
            ->leftJoin('answers as escalera', function ($join) {
                $join->on('u.id', '=', 'escalera.user_id')
                    ->where('escalera.question_id', '=', 109);
            })
            ->leftJoin('answers as piso', function ($join) {
                $join->on('u.id', '=', 'piso.user_id')
                    ->where('piso.question_id', '=', 110);
            })
            ->leftJoin('answers as puerta', function ($join) {
                $join->on('u.id', '=', 'puerta.user_id')
                    ->where('puerta.question_id', '=', 111);
            })
            ->leftJoin('answers as municipio', function ($join) {
                $join->on('u.id', '=', 'municipio.user_id')
                    ->where('municipio.question_id', '=', 37);
            })
            ->leftJoin('answers as codigo_postal', function ($join) {
                $join->on('u.id', '=', 'codigo_postal.user_id')
                    ->where('codigo_postal.question_id', '=', 39);
            })
            ->leftJoin('answers as provincia', function ($join) {
                $join->on('u.id', '=', 'provincia.user_id')
                    ->where('provincia.question_id', '=', 36);
            })
            ->leftJoin('answers as telefono', function ($join) {
                $join->on('u.id', '=', 'telefono.user_id')
                    ->where('telefono.question_id', '=', 45);
            })
            ->leftJoin('answers as entidad_financiera', function ($join) {
                $join->on('u.id', '=', 'entidad_financiera.user_id')
                    ->where('entidad_financiera.question_id', '=', 215);
            })
            ->leftJoin('answers as numero_cuenta', function ($join) {
                $join->on('u.id', '=', 'numero_cuenta.user_id')
                    ->where('numero_cuenta.question_id', '=', 88);
            })
            ->leftJoin('answers as tipo_documento_119', function ($join) {
                $join->on('u.id', '=', 'tipo_documento_119.user_id')
                    ->where('tipo_documento_119.question_id', '=', 119);
            })
            ->whereNotNull('nombre.answer')
            ->whereNotNull('primer_apellido.answer')
            ->whereNotNull('dni.answer')
            ->orderBy('u.name');

        $usuarios = $query->get();

        if ($usuarios->count() > 0) {
        }

        return response()->json([
            'status' => 'ok',
            'usuarios' => $usuarios,
            'debug' => [
                'total_usuarios' => $usuarios->count(),
                'question_ids' => [
                    'nombre' => 170,
                    'primer_apellido' => 171,
                    'segundo_apellido' => 177,
                    'dni' => 34,
                    'tipo_via' => 104,
                    'nombre_via' => 105,
                    'numero_via' => 106,
                    'bloque' => 107,
                    'portal' => 108,
                    'escalera' => 109,
                    'piso' => 110,
                    'puerta' => 111,
                    'municipio' => 37,
                    'codigo_postal' => 39,
                    'provincia' => 36,
                    'telefono' => 45,
                    'entidad_financiera' => 39,
                    'numero_cuenta' => 88,
                    'tipo_documento_119' => 119,
                ],
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('Error en endpoint usuarios-domiciliacion-pav: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener usuarios: '.$e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
        ], 500);
    }
});

Route::post('/internal/actualizar-documento-domiciliacion-pav', function (Request $request) {

    abort_unless(
        $request->header('X-Internal-Secret') === env('SCRIPT_SECRET'),
        403
    );

    try {
        $request->validate([
            'user_id' => 'required|integer',
            'url_pdf' => 'required|string',
            'document_id' => 'required|integer',
        ]);

        $user_id = $request->input('user_id');
        $url_pdf = $request->input('url_pdf');
        $document_id = $request->input('document_id');

        Log::info("Procesando: user_id={$user_id}, url_pdf={$url_pdf}, document_id={$document_id}");

        $document = DB::table('documents')->where('id', $document_id)->first();

        if (! $document) {
            Log::error("Documento no encontrado con ID: {$document_id}");

            return response()->json([
                'status' => 'error',
                'message' => 'Documento no encontrado',
            ], 404);
        }

        $existing_record = DB::table('user_documents')
            ->where('user_id', $user_id)
            ->where('document_id', $document_id)
            ->first();

        if ($existing_record) {

            DB::table('user_documents')
                ->where('user_id', $user_id)
                ->where('document_id', $document_id)
                ->update([
                    'file_path' => $url_pdf,
                    'file_name' => basename($url_pdf),
                    'file_type' => 'pdf',
                    'size' => 0,
                    'slug' => $document->slug,
                    'estado' => 'validado',
                    'updated_at' => now(),
                ]);
        } else {

            DB::table('user_documents')->insert([
                'user_id' => $user_id,
                'document_id' => $document_id,
                'file_path' => $url_pdf,
                'file_name' => basename($url_pdf),
                'file_type' => 'pdf',
                'size' => 0,
                'slug' => $document->slug,
                'estado' => 'validado',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Documento actualizado correctamente',
            'data' => [
                'user_id' => $user_id,
                'document_id' => $document_id,
                'slug' => $document->slug,
                'file_path' => $url_pdf,
                'file_name' => basename($url_pdf),
                'file_type' => 'pdf',
                'size' => 0,
                'estado' => 'validado',
            ],
        ]);

    } catch (\Exception $e) {
        Log::error('Error en endpoint actualizar-documento-domiciliacion-pav: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al actualizar documento: '.$e->getMessage(),
        ], 500);
    }
});

Route::post('/internal/bajadas-inactividad', function (Request $request) {
    abort_unless(
        $request->header('X-Internal-Secret') === env('CRONJOB_SECRET'),
        403
    );

    try {
        $service = new \App\Services\UserAyudaInactividadService;

        // Ejecutar el proceso de bajadas por inactividad
        $resultados = $service->ejecutarBajadasPorInactividad();

        // Obtener estadísticas adicionales
        $estadisticas = $service->obtenerEstadisticas();

        return response()->json([
            'status' => 'ok',
            'message' => 'Proceso de bajadas por inactividad ejecutado correctamente',
            'resultados' => $resultados,
            'estadisticas' => $estadisticas,
            'timestamp' => now()->toDateTimeString(),
        ]);

    } catch (\Exception $e) {
        Log::error('Error en endpoint bajadas-inactividad: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al ejecutar bajadas por inactividad: '.$e->getMessage(),
        ], 500);
    }
});

// Rutas para el sistema de scoring de user_ayudas
Route::prefix('admin/scoring')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ScoringController::class, 'index']);

    Route::get('/usuario/{userId}', [\App\Http\Controllers\Admin\ScoringController::class, 'porUsuario']);

    Route::get('/{userAyudaId}', [\App\Http\Controllers\Admin\ScoringController::class, 'show']);

    Route::get('/estadisticas/estadisticas', [\App\Http\Controllers\Admin\ScoringController::class, 'estadisticas']);

    Route::get('/prioridad/prioridad', [\App\Http\Controllers\Admin\ScoringController::class, 'prioridad']);

    Route::get('/filtrar/score', [\App\Http\Controllers\Admin\ScoringController::class, 'filtrarPorScore']);

    Route::get('/filtrar/estado', [\App\Http\Controllers\Admin\ScoringController::class, 'filtrarPorEstado']);

    Route::post('/crear-tareas-crm', [\App\Http\Controllers\Admin\ScoringController::class, 'crearTareasCRM']);

    Route::get('/estadisticas-tareas-crm/estadisticas', [\App\Http\Controllers\Admin\ScoringController::class, 'estadisticasTareasCRM']);
});
// Cronjob: filtro por estados OPx (OP1-Documentacion)
Route::prefix('cronjob')->group(function () {
    // Actualizar dealstage a Tramitación: contrataciones con estado OP1-Documentacion, completas y período abierto
    Route::post('/actualizar-dealstage-tramitacion', function (Request $request) {
        abort_unless(
            $request->header('X-Internal-Secret') === env('CRONJOB_SECRET'),
            403
        );

        try {
            Log::info('Google Cloud Scheduler iniciando actualización de dealstage a Tramitación (OPx)', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Ejecutar el comando de Artisan
            Artisan::call('hubspot:actualizar-dealstage-tramitacion');
            $output = Artisan::output();

            Log::info('Google Cloud Scheduler - Actualización de dealstage ejecutada exitosamente', [
                'output' => $output,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Actualización de dealstage ejecutada exitosamente',
                'output' => $output,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Google Cloud Scheduler - Error en actualizar-dealstage-tramitacion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    });

});

Route::post('/task-tech', [TaskTechController::class, 'store']);
Route::prefix('onboarders')->group(function () {
    Route::get('/completed', [OnboarderController::class, 'getCompleted']);
    Route::get('/wizard-config/{wizardId}', [OnboarderController::class, 'getWizardConfig']);
    Route::post('/save-answer', [OnboarderController::class, 'saveAnswer']);
    Route::post('/complete-section', [OnboarderController::class, 'completeSection']);
    Route::post('/add-conviviente', [OnboarderController::class, 'addConviviente']);
    Route::post('/complete', [OnboarderController::class, 'complete']);
    Route::get('/{onboarderId}/metrics', [OnboarderController::class, 'getMetrics']);
    Route::post('/metrics', [OnboarderController::class, 'trackMetric']);

    Route::get('/onboarder-metrics', [OnboarderMetricController::class, 'index']);
    Route::get('/onboarder-metrics/section-stats', [OnboarderMetricController::class, 'getSectionStats']);
    Route::get('/onboarder-metrics/conviviente-type-stats', [OnboarderMetricController::class, 'getConvivienteTypeStats']);
    Route::get('/onboarder-metrics/abandonment-stats', [OnboarderMetricController::class, 'getAbandonmentStats']);
    Route::get('/onboarder-metrics/active-progress', [OnboarderMetricController::class, 'getActiveProgress']);
    Route::get('/onboarder-metrics/performance', [OnboarderMetricController::class, 'getPerformanceMetrics']);
    Route::post('/onboarder-metrics', [OnboarderMetricController::class, 'create']);

    Route::get('/wizards/{wizardId}/onboarder-config', [WizardController::class, 'getOnboarderConfig']);
    Route::post('/wizards/{wizardId}/mark-completed', [WizardController::class, 'markAsCompleted']);
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get(
        '/conviviente-builder-form/{questionnaireId}/{index}',
        [AyudasSolicitadasController::class, 'getConvivienteBuilderForm']
    )->name('api.conviviente-builder-form');

    Route::get(
        '/conviviente-crear-form/{ayudaId}',
        [AyudasSolicitadasController::class, 'getConvivienteCrearForm']
    )->name('api.conviviente-crear-form');

    Route::post(
        '/conviviente-crear',
        [AyudasSolicitadasController::class, 'storeConvivienteCrear']
    )->name('api.conviviente-crear');
});

Route::post('/whatsapp/identify-user', function (Request $request) {
    try {
        $validated = $request->validate([
            'phone' => 'required|string',
            'email' => 'required|email',
        ]);

        $phone = trim($validated['phone']);
        $email = trim($validated['email']);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ninguna cuenta con este correo electrónico',
            ], 404);
        }

        $telefonoQuestionId = Question::where('slug', 'telefono')->value('id');

        if (! $telefonoQuestionId) {
            return response()->json([
                'success' => false,
                'message' => 'Error de configuración del sistema',
            ], 500);
        }

        $userPhone = Answer::where('user_id', $user->id)
            ->where('question_id', $telefonoQuestionId)
            ->whereNull('conviviente_id')
            ->value('answer');

        $normalizedUserPhone = preg_replace('/[^0-9+]/', '', $userPhone ?? '');
        $normalizedInputPhone = preg_replace('/[^0-9+]/', '', $phone);

        if ($normalizedUserPhone !== $normalizedInputPhone) {
            return response()->json([
                'success' => false,
                'message' => 'El teléfono no coincide con el correo electrónico proporcionado',
            ], 404);
        }

        $answers = Answer::getColectionAnswersQuestions($user->id);
        $userDocuments = UserDocument::where('user_id', $user->id)->get();
        $documentosSubidos = $userDocuments->pluck('slug')->toArray();

        $documentosService = new DocumentosAyudaService;

        $contrataciones = Contratacion::with(['ayuda'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($contratacion) use ($documentosService, $answers, $documentosSubidos) {
                $documentosFaltantes = [];
                try {
                    $sectorAyuda = $contratacion->ayuda->sector ?? null;
                    $documentosFaltantes = $documentosService->obtenerDocumentosFaltantes(
                        $contratacion,
                        $answers,
                        $documentosSubidos,
                        $sectorAyuda
                    );
                } catch (Exception $e) {
                    Log::error("Error getting missing documents for contract {$contratacion->id}: ".$e->getMessage());
                }

                return [
                    'id' => $contratacion->id,
                    'ayuda_nombre' => $contratacion->ayuda->nombre_ayuda ?? 'Sin nombre',
                    'descripcion' => $contratacion->ayuda->description ?? null,
                    'fecha_contratacion' => $contratacion->created_at ? $contratacion->created_at->format('d/m/Y') : null,
                    'documentos_faltantes' => $documentosFaltantes,
                ];
            });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'contrataciones' => $contrataciones,
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Datos de entrada inválidos',
            'errors' => $e->errors(),
        ], 422);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al identificar el usuario',
        ], 500);
    }
});

Route::post('/whatsapp/upload-document', function (Request $request, GcsUploaderService $gcs) {
    try {
        $body = $request->json()->all();

        $contractId = $body['contract_id'] ?? null;
        $documentSlug = $body['document_slug'] ?? null;
        $files = $body['files'] ?? [];

        if (! $contractId || ! $documentSlug || empty($files)) {
            return response()->json([
                'success' => false,
                'message' => 'Datos requeridos faltantes',
            ], 422);
        }

        if (! is_numeric($contractId)) {
            return response()->json([
                'success' => false,
                'message' => 'contract_id debe ser un número',
            ], 422);
        }

        $contractId = (int) $contractId;

        $contratacion = Contratacion::find($contractId);
        $userId = $contratacion->user_id;

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'La contratación no tiene un usuario asociado',
            ], 404);
        }

        if (Str::startsWith($documentSlug, 'recibo_')) {
            $lookupSlug = 'justificantes-pago-alquiler';
        } else {
            $lookupSlug = $documentSlug;
        }
        $document = Document::where('slug', $lookupSlug)->first();

        if (! $document) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado',
            ], 404);
        }

        $uploadedFiles = [];

        foreach ($files as $index => $fileData) {
            try {
                $cdnUrl = $fileData['cdn_url'] ?? null;
                $fileName = $fileData['file_name'] ?? 'archivo_'.time();
                $encryptionMetadata = $fileData['encryption_metadata'] ?? null;

                if (! $cdnUrl) {
                    throw new Exception('URL de CDN no proporcionada para archivo '.($index + 1));
                }

                if (! $encryptionMetadata) {
                    throw new Exception('Metadatos de encriptación no proporcionados para archivo '.($index + 1).'. Los archivos de WhatsApp siempre requieren metadatos de encriptación.');
                }

                $requiredFields = ['encryption_key', 'hmac_key', 'iv', 'plaintext_hash', 'encrypted_hash'];
                foreach ($requiredFields as $field) {
                    if (! isset($encryptionMetadata[$field])) {
                        throw new Exception("Campo requerido '{$field}' faltante en metadatos de encriptación para archivo ".($index + 1));
                    }
                }

                $cdnFile = EndpointsHelper::downloadFileFromCdn($cdnUrl, $fileName);
                if (! $cdnFile) {
                    throw new Exception('No se pudo descargar el archivo: '.$fileName);
                }

                $decryptedContent = EndpointsHelper::decryptWhatsAppFile(
                    $cdnFile,
                    $encryptionMetadata
                );

                if (! $decryptedContent) {
                    throw new Exception('Error al desencriptar el archivo: '.$fileName);
                }

                $mimeType = EndpointsHelper::detectMimeType($decryptedContent, $fileName);
                EndpointsHelper::validateFileContent($decryptedContent, $mimeType, $fileName);

                $uuid = (string) Str::uuid();
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $path = "documentos/usuario_{$userId}/{$uuid}.{$ext}";

                $tempFile = tmpfile();
                $tempPath = stream_get_meta_data($tempFile)['uri'];
                fwrite($tempFile, $decryptedContent);
                fflush($tempFile);

                try {
                    $gcs->uploadFile(new UploadedFile(
                        $tempPath,
                        $fileName,
                        $mimeType,
                        null,
                        true
                    ), $path);

                    fclose($tempFile);
                } catch (Exception $e) {
                    if (is_resource($tempFile)) {
                        fclose($tempFile);
                    }
                    Log::error('Error al subir archivo a GCS: '.$e->getMessage());
                    throw $e;
                }

                $userDocument = UserDocument::create([
                    'user_id' => $userId,
                    'document_id' => $document->id,
                    'slug' => $documentSlug,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'file_type' => $mimeType,
                    'size' => strlen($decryptedContent),
                    'estado' => 'pendiente',
                ]);

                $uploadedFiles[] = [
                    'file_name' => $fileName,
                    'user_document_id' => $userDocument->id,
                ];
            } catch (Exception $e) {
                Log::error('Error procesando archivo '.($fileData['file_name'] ?? 'desconocido').': '.$e->getMessage());
                throw $e;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedFiles).' documento(s) subido(s) correctamente',
            'data' => [
                'contract_id' => $contractId,
                'document_slug' => $documentSlug,
                'files' => $uploadedFiles,
            ],
        ]);

    } catch (Exception $e) {
        Log::error('Error en endpoint upload-document: '.$e->getMessage());
        Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Error al procesar el documento: '.$e->getMessage(),
        ], 500);
    }
});
