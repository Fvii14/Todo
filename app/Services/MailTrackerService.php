<?php

namespace App\Services;

use App\Mail\AvisoContratacionReferidoMail;
use App\Mail\ContratacionMail;
use App\Mail\FirstVisitMail;
use App\Mail\RecordatorioPostAvisoReferidoMail;
use App\Mail\WelcomeMail;
use App\Models\Contratacion;
use App\Models\MailTracking;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailTrackerService
{
    public function WelcomeMail()
    {
        $steps = [
            2 => Carbon::now()->subHours(24),
            3 => Carbon::now()->subHours(72),
            4 => Carbon::now()->subWeek(),
        ];

        foreach ($steps as $step => $timeThreshold) {
            User::where('created_at', '<=', $timeThreshold)->where('is_admin', 0)->whereDoesntHave('answers')
                ->whereDoesntHave('mailTracking', function ($query) use ($step) {
                    $query->where('mail_class', WelcomeMail::class)
                        ->where('step', $step);
                })
                ->chunkById(200, function ($usersWithoutAnswers) use ($step) {
                    foreach ($usersWithoutAnswers as $user) {
                        try {
                            Mail::to($user->email)->send(new WelcomeMail($user->name, $step));
                            MailTracking::track($user, WelcomeMail::class, $step);
                        } catch (\Throwable $e) {
                            Log::warning('No se pudo enviar el correo: '.$e->getMessage());
                        }
                    }
                });
        }
    }

    public function FirstVisitMail()
    {
        $steps = [
            2 => Carbon::now()->subHours(24),
            3 => Carbon::now()->subHours(48),
            4 => Carbon::now()->subHours(72),
        ];

        // TemplateIds de WhatsApp según step
        $whatsappTemplates = [
            2 => 305,
            3 => 306,
            4 => 307,
        ];

        $brevoService = new BrevoService;

        foreach ($steps as $step => $timeThreshold) {
            User::where('created_at', '<=', $timeThreshold)->where('is_admin', 0)->whereHas('answers')->whereDoesntHave('solicitudes')
                ->whereDoesntHave('mailTracking', function ($query) use ($step) {
                    $query->where('mail_class', FirstVisitMail::class)
                        ->where('step', $step);
                })
                ->without('answers')
                ->with(['answers' => fn ($q) => $q->where('question_id', 45)->whereNull('conviviente_id')])
                ->chunkById(200, function ($usersWithAnswersNoAyuda) use ($step, $brevoService, $whatsappTemplates) {
                    foreach ($usersWithAnswersNoAyuda as $user) {
                        try {
                            Mail::to($user->email)->send(new FirstVisitMail($user, $step, collect()));
                            MailTracking::track($user, FirstVisitMail::class, $step);

                            $telefono = $user->answers->firstWhere('question_id', 45)?->answer;

                            if ($telefono) {
                                $telefonoFormateado = '34'.preg_replace('/[^0-9]/', '', $telefono);
                                $templateId = $whatsappTemplates[$step] ?? null;
                                if ($templateId) {
                                    $response = $brevoService->sendSimpleWhatsAppMessage($telefonoFormateado, $templateId);

                                    Log::info("WhatsApp enviado a {$telefonoFormateado} para usuario {$user->id} en step {$step}", ['response' => $response]);
                                } else {
                                    Log::warning("No hay templateId definido para step {$step}");
                                }
                            } else {
                                Log::warning("No se encontró teléfono para usuario {$user->id} en step {$step}");
                            }
                        } catch (\Throwable $e) {
                            Log::warning('No se pudo enviar el correo: '.$e->getMessage());
                        }
                    }
                });
        }
    }

    public function ContratacionMail()
    {
        $steps = [
            2 => Carbon::now()->subHours(42),
            3 => Carbon::now()->subHours(84),
            4 => Carbon::now()->subHours(126),
            5 => Carbon::now()->subHours(168),
        ];

        $whatsappTemplates = [
            2 => 309,
            3 => 310,
            4 => 311,
            5 => 312,
        ];
        $brevoService = app(\App\Services\BrevoService::class);
        foreach ($steps as $step => $timeThreshold) {
            User::where('created_at', '<=', $timeThreshold)
                ->where('is_admin', 0)
                ->whereHas('contrataciones', function ($query) {
                    $query->whereHas('estadosContratacion', fn ($q) => $q->where('codigo', 'OP1-Documentacion'));
                })
                ->whereDoesntHave('mailTracking', function ($query) use ($step) {
                    $query->where('mail_class', ContratacionMail::class)
                        ->where('step', $step);
                })
                ->without('answers')
                ->with([
                    'contrataciones' => fn ($q) => $q->whereHas('estadosContratacion', fn ($eq) => $eq->where('codigo', 'OP1-Documentacion'))->with('ayuda'),
                    'answers' => fn ($q) => $q->where('question_id', 45)->whereNull('conviviente_id'),
                ])
                ->chunkById(200, function ($usersWithContratacionDocumentacion) use ($step, $brevoService, $whatsappTemplates) {
                    foreach ($usersWithContratacionDocumentacion as $user) {
                        $contratacion = $user->contrataciones->first();
                        $ayuda = $contratacion?->ayuda;

                        if ($ayuda) {
                            try {
                                Mail::to($user->email)->send(new ContratacionMail(
                                    $user->name,
                                    $ayuda,
                                    $step
                                ));

                                MailTracking::track($user, ContratacionMail::class, $step);

                                $telefono = $user->answers->firstWhere('question_id', 45)?->answer;

                                if ($telefono) {
                                    $telefonoFormateado = '34'.preg_replace('/[^0-9]/', '', $telefono);
                                    $templateId = $whatsappTemplates[$step] ?? null;

                                    if ($templateId) {
                                        $response = $brevoService->sendSimpleWhatsAppMessage($telefonoFormateado, $templateId);
                                        Log::info("WhatsApp enviado a {$telefonoFormateado} para usuario {$user->id} en step {$step}", ['response' => $response]);
                                    } else {
                                        Log::warning("No hay templateId definido para step {$step}");
                                    }
                                } else {
                                    Log::warning("No se encontró teléfono para usuario {$user->id} en step {$step}");
                                }
                            } catch (\Throwable $e) {
                                Log::warning('No se pudo enviar el correo: '.$e->getMessage());
                            }
                        }
                    }
                });
        }
    }

    public function BonoCulturalJoven()
    {
        $steps = [
            2 => Carbon::now()->subHours(24),
            3 => Carbon::now()->subHours(48),
            4 => Carbon::now()->subHours(72),
        ];

        foreach ($steps as $step => $timeThreshold) {
            User::where('created_at', '<=', $timeThreshold)
                ->where('is_admin', 0)
                ->whereHas('mailTracking', function ($q) {
                    $q->where('mail_class', \App\Mail\BonoCulturalJovenMail::class)
                        ->where('step', 1); // Solo usuarios que recibieron el step 1
                })
                ->whereDoesntHave('contrataciones', function ($q) {
                    $q->whereHas('ayuda', function ($q) {
                        $q->where('slug', 'bono_cultural_joven');
                    });
                })
                ->whereDoesntHave('mailTracking', function ($q) use ($step) {
                    $q->where('mail_class', \App\Mail\BonoCulturalJovenMail::class)
                        ->where('step', $step);
                })
                ->without('answers')
                ->with(['answers' => fn ($q) => $q->where('question_id', 177)])
                ->chunkById(200, function ($users) use ($step) {
                    foreach ($users as $user) {
                        try {
                            $nombrePila = $user->nombrePila() ?? '';
                            Mail::to($user->email)->send(new \App\Mail\BonoCulturalJovenMail($user, $step, $nombrePila));
                            MailTracking::track($user, \App\Mail\BonoCulturalJovenMail::class, $step);
                        } catch (\Throwable $e) {
                            Log::warning("No se pudo enviar BonoCulturalJovenMail step {$step} al usuario {$user->id}: ".$e->getMessage());
                        }
                    }
                });
        }
    }

    public function BonoCulturalDocumentacionMail()
    {
        $steps = [
            2 => now()->subHours(24),
            3 => now()->subHours(48),
            4 => now()->subHours(72),
        ];

        foreach ($steps as $step => $fechaLimite) {
            User::whereHas('contrataciones', function ($q) use ($fechaLimite) {
                $q->whereHas('estadosContratacion', fn ($eq) => $eq->where('codigo', 'OP1-Documentacion'))
                    ->whereHas('ayuda', function ($q) {
                        $q->where('slug', 'bono-cultural-joven');
                    })
                    ->where('updated_at', '<=', $fechaLimite);
            })
                ->whereDoesntHave('mailTracking', function ($q) use ($step) {
                    $q->where('mail_class', \App\Mail\BonoCulturalDocumentacionMail::class)
                        ->where('step', $step);
                })
                ->without('answers')
                ->with([
                    'contrataciones' => function ($q) use ($fechaLimite) {
                        $q->whereHas('estadosContratacion', fn ($eq) => $eq->where('codigo', 'OP1-Documentacion'))
                            ->whereHas('ayuda', fn ($aq) => $aq->where('slug', 'bono-cultural-joven'))
                            ->where('updated_at', '<=', $fechaLimite)
                            ->with('ayuda');
                    },
                    'answers' => fn ($q) => $q->where('question_id', 177),
                ])
                ->chunkById(200, function ($users) use ($step) {
                    foreach ($users as $user) {
                        $contratacion = $user->contrataciones->first();

                        if (! $contratacion) {
                            continue;
                        }

                        Mail::to($user->email)->send(new \App\Mail\BonoCulturalDocumentacionMail($user, $step));
                        MailTracking::track($user, \App\Mail\BonoCulturalDocumentacionMail::class, $step);
                    }
                });
        }
    }

    public function SeguimientoReferidosBonoCultural()
    {
        $steps = [
            1 => now()->subHours(24),
            2 => now()->subHours(72),
            3 => now()->subHours(120),
            4 => now()->subHours(168),
        ];

        foreach ($steps as $step => $thresholdTime) {
            Contratacion::whereHas('estadosContratacion', fn ($q) => $q->where('codigo', 'OP1-Documentacion'))
                ->whereColumn('updated_at', 'created_at')
                ->where('created_at', '<=', $thresholdTime) // Lleva tiempo sin moverse
                ->where('ayuda_id', 44) // Bono Cultural Joven
                ->whereHas('user', function ($q) {
                    $q->where('is_admin', 0);
                })
                ->whereHas('user', function ($query) use ($step) {
                    $query->whereDoesntHave('mailTracking', function ($q) use ($step) {
                        $q->where('mail_class', \App\Mail\RecordatorioReferidoMail::class)
                            ->where('step', $step);
                    });
                })
                ->with(['user' => fn ($q) => $q->without('answers')->with(['answers' => fn ($aq) => $aq->where('question_id', 177)])])
                ->chunkById(200, function ($contrataciones) use ($step) {
                    $userIds = $contrataciones->pluck('user_id')->unique()->values()->all();
                    $userIdsConReferidoConContratacion = \App\Models\User::whereIn('ref_by', $userIds)
                        ->whereHas('contrataciones', fn ($q) => $q->where('ayuda_id', 44))
                        ->pluck('ref_by')
                        ->flip()
                        ->all();

                    foreach ($contrataciones as $contratacion) {
                        $user = $contratacion->user;

                        if (isset($userIdsConReferidoConContratacion[$user->id])) {
                            continue;
                        }

                        try {
                            $nombre = $user->nombrePila() ?? '';
                            Mail::to($user->email)->send(new \App\Mail\RecordatorioReferidoMail($user, $step, $nombre));
                            \App\Models\MailTracking::track($user, \App\Mail\RecordatorioReferidoMail::class, $step);
                        } catch (\Throwable $e) {
                            Log::warning("Error al enviar RecordatorioReferidoMail step {$step} al user {$user->id}: ".$e->getMessage());
                        }
                    }
                });
        }
    }

    public function RecordatorioPostAvisoReferido()
    {
        $usersData = MailTracking::where('mail_class', AvisoContratacionReferidoMail::class)
            ->select('user_id', DB::raw('MAX(created_at) as last_sent'))
            ->groupBy('user_id')
            ->having('last_sent', '<=', now()->subHours(72))
            ->get()
            ->keyBy('user_id');

        if ($usersData->isEmpty()) {
            return;
        }

        $userIds = $usersData->keys()->all();

        $alreadyRemindedUserIds = MailTracking::where('mail_class', RecordatorioPostAvisoReferidoMail::class)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->flip()
            ->all();

        collect($userIds)->chunk(200)->each(function ($chunkIds) use ($alreadyRemindedUserIds) {
            $chunkIds = $chunkIds->values()->all();
            $users = User::whereIn('id', $chunkIds)
                ->without('answers')
                ->with([
                    'referidos.contrataciones' => function ($q) {
                        $q->where('ayuda_id', 44);
                    },
                ])
                ->get()
                ->keyBy('id');

            foreach ($chunkIds as $userId) {
                $user = $users->get($userId);
                if (! $user) {
                    continue;
                }

                $tieneReferido = $user->referidos
                    ->filter(function ($referido) {
                        return $referido->contrataciones->count() > 0;
                    })
                    ->isNotEmpty();

                $yaTieneRecordatorio = isset($alreadyRemindedUserIds[$user->id]);

                if ($tieneReferido || $yaTieneRecordatorio) {
                    continue;
                }

                try {
                    Mail::to($user->email)->send(new RecordatorioPostAvisoReferidoMail($user));
                    MailTracking::track($user, RecordatorioPostAvisoReferidoMail::class);
                } catch (\Throwable $e) {
                    Log::warning("❌ Error al enviar RecordatorioPostAvisoReferidoMail al user {$user->id}: {$e->getMessage()}");
                }
            }
        });
    }
}
