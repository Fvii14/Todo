<?php

namespace App\Services;

use App\Models\MailTracking;
use App\Models\User;
use App\Models\Wizard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WizardMailService
{
    /**
     * Transforma el wizard completado en el envío real de emails
     */
    public function transformWizardToMail(Wizard $wizard): array
    {
        $data = $wizard->data;

        // Validar que el wizard tenga los datos necesarios
        $this->validateWizardData($data);

        // Obtener usuarios según los criterios
        $users = $this->getUsersByCriteria($data['user_criteria']);

        // Enviar emails
        $results = $this->sendEmails($users, $data['email_config']);

        return [
            'total_users' => count($users),
            'emails_sent' => $results['sent'],
            'emails_failed' => $results['failed'],
            'users' => $users->pluck('id')->toArray(),
        ];
    }

    /**
     * Obtiene usuarios según los criterios especificados
     */
    public function getUsersByCriteria(array $criteria): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::with(['answers.question' => function ($q) {
            $q->select('id', 'text', 'slug', 'type', 'options');
        }]);

        // Filtrar por campos del modelo User
        if (! empty($criteria['user_field_conditions'])) {
            foreach ($criteria['user_field_conditions'] as $condition) {
                $this->applyUserFieldCondition($query, $condition);
            }
        }

        // Filtrar por condiciones de respuestas
        if (! empty($criteria['answer_conditions'])) {
            foreach ($criteria['answer_conditions'] as $condition) {
                $this->applyAnswerCondition($query, $condition);
            }
        }

        return $query->get();
    }

    /**
     * Aplica una condición de campo de usuario al query
     */
    private function applyUserFieldCondition($query, array $condition): void
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'] ?? null;

        // Campos booleanos que necesitan tratamiento especial
        $booleanFields = ['is_admin', 'onboarding_done'];

        // Campos de timestamp que necesitan tratamiento especial
        $timestampFields = ['email_verified_at', 'created_at', 'updated_at'];

        // Campos de enum que necesitan tratamiento especial
        $enumFields = ['estado_usuario'];

        // Campos numéricos que pueden usar operadores de comparación
        $numericFields = ['id', 'brevo_id', 'ref_by', 'id_unidad_familiar'];

        switch ($operator) {
            case 'equals':
                if (in_array($field, $booleanFields)) {
                    $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($boolValue !== null) {
                        $query->where($field, $boolValue);
                    }
                } elseif (in_array($field, $enumFields)) {
                    $query->where($field, $value);
                } else {
                    $query->where($field, $value);
                }
                break;
            case 'not_equals':
                if (in_array($field, $booleanFields)) {
                    $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($boolValue !== null) {
                        $query->where($field, '!=', $boolValue);
                    }
                } elseif (in_array($field, $enumFields)) {
                    $query->where($field, '!=', $value);
                } else {
                    $query->where($field, '!=', $value);
                }
                break;
            case 'contains':
                if (in_array($field, $timestampFields)) {
                    // Para timestamps, buscar por fecha
                    $query->whereDate($field, 'LIKE', "%{$value}%");
                } else {
                    $query->where($field, 'LIKE', "%{$value}%");
                }
                break;
            case 'not_contains':
                if (in_array($field, $timestampFields)) {
                    // Para timestamps, buscar por fecha
                    $query->whereDate($field, 'NOT LIKE', "%{$value}%");
                } else {
                    $query->where($field, 'NOT LIKE', "%{$value}%");
                }
                break;
            case 'greater_than':
                if (in_array($field, $numericFields)) {
                    $query->where($field, '>', (int) $value);
                } elseif (in_array($field, $timestampFields)) {
                    $query->where($field, '>', $value);
                } else {
                    $query->where($field, '>', $value);
                }
                break;
            case 'less_than':
                if (in_array($field, $numericFields)) {
                    $query->where($field, '<', (int) $value);
                } elseif (in_array($field, $timestampFields)) {
                    $query->where($field, '<', $value);
                } else {
                    $query->where($field, '<', $value);
                }
                break;
            case 'greater_than_or_equal':
                if (in_array($field, $numericFields)) {
                    $query->where($field, '>=', (int) $value);
                } elseif (in_array($field, $timestampFields)) {
                    $query->where($field, '>=', $value);
                } else {
                    $query->where($field, '>=', $value);
                }
                break;
            case 'less_than_or_equal':
                if (in_array($field, $numericFields)) {
                    $query->where($field, '<=', (int) $value);
                } elseif (in_array($field, $timestampFields)) {
                    $query->where($field, '<=', $value);
                } else {
                    $query->where($field, '<=', $value);
                }
                break;
            case 'is_null':
                $query->whereNull($field);
                break;
            case 'is_not_null':
                $query->whereNotNull($field);
                break;
        }
    }

    /**
     * Aplica una condición de respuesta al query
     */
    private function applyAnswerCondition($query, array $condition): void
    {
        $questionId = $condition['question_id'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        $query->whereHas('answers', function ($q) use ($questionId, $operator, $value) {
            $q->where('question_id', $questionId);

            switch ($operator) {
                case 'equals':
                    $q->where('answer', $value);
                    break;
                case 'not_equals':
                    $q->where('answer', '!=', $value);
                    break;
                case 'contains':
                    $q->where('answer', 'LIKE', "%{$value}%");
                    break;
                case 'not_contains':
                    $q->where('answer', 'NOT LIKE', "%{$value}%");
                    break;
                case 'greater_than':
                    $q->where('answer', '>', $value);
                    break;
                case 'less_than':
                    $q->where('answer', '<', $value);
                    break;
                case 'in':
                    $q->whereIn('answer', is_array($value) ? $value : [$value]);
                    break;
                case 'not_in':
                    $q->whereNotIn('answer', is_array($value) ? $value : [$value]);
                    break;
            }
        });
    }

    /**
     * Envía emails a los usuarios seleccionados
     */
    private function sendEmails($users, array $emailConfig): array
    {
        $totalUsers = $users->count();
        $chunkSize = 10; // Procesar en chunks pequeños para Cloud Run
        $sent = 0;
        $failed = 0;

        Log::info("Iniciando envío de {$totalUsers} emails en Cloud Run");

        // Procesar en chunks para evitar problemas de memoria
        $users->chunk($chunkSize)->each(function ($userChunk) use ($emailConfig, &$sent, &$failed, $totalUsers) {
            foreach ($userChunk as $user) {
                try {
                    // Crear la clase de email dinámicamente
                    $mailClass = $emailConfig['mail_class'];

                    if (! class_exists($mailClass)) {
                        Log::error("Clase de email no encontrada: {$mailClass}");
                        $failed++;

                        continue;
                    }

                    // Limpiar y validar los datos del email
                    $emailData = $this->cleanEmailData($emailConfig['data'] ?? []);

                    // Crear la instancia de mail según el tipo de constructor
                    $mailInstance = $this->createMailInstance($mailClass, $user, $emailData);

                    // Enviar email
                    Mail::to($user->email)->send($mailInstance);

                    // Registrar en MailTracking
                    MailTracking::track($user, $mailClass, $emailConfig['step'] ?? null);

                    $sent++;

                    // Log de progreso cada 50 emails
                    if ($sent % 50 === 0) {
                        Log::info("Progreso: {$sent} emails enviados de {$totalUsers}");
                    }

                } catch (\Exception $e) {
                    Log::error("Error enviando email a {$user->email}: ".$e->getMessage());
                    $failed++;
                }
            }

            // Pequeña pausa entre chunks para evitar rate limiting
            usleep(100000); // 0.1 segundos
        });

        Log::info("Envío completado: {$sent} enviados, {$failed} fallidos");

        return [
            'sent' => $sent,
            'failed' => $failed,
            'total_users' => $totalUsers,
        ];
    }

    /**
     * Crea una instancia de mail según el tipo de constructor
     */
    private function createMailInstance(string $mailClass, User $user, array $emailData)
    {
        // Clases que solo necesitan el objeto User
        $userOnlyClasses = [
            \App\Mail\FirstTimeFromMigracionMail::class,
            \App\Mail\WelcomeMail::class,
            \App\Mail\UserBeneficiarioMail::class,
            \App\Mail\UserNoBeneficiarioMail::class,
        ];

        // Clases que necesitan datos adicionales
        $dataClasses = [
            \App\Mail\ChangePassMail::class => function ($user, $data) {
                return new \App\Mail\ChangePassMail($user->name, $data['resetLink'] ?? '');
            },
            \App\Mail\AvisoContratacionReferidoMail::class => function ($user, $data) {
                return new \App\Mail\AvisoContratacionReferidoMail(
                    $data['referrerNombre'] ?? $user->name,
                    $data['referrer'] ?? $user,
                    $user
                );
            },
        ];

        // Si es una clase que solo necesita el User
        if (in_array($mailClass, $userOnlyClasses)) {
            return new $mailClass($user);
        }

        // Si es una clase con constructor personalizado
        if (isset($dataClasses[$mailClass])) {
            return $dataClasses[$mailClass]($user, $emailData);
        }

        // Por defecto, intentar con User + datos
        return new $mailClass($user, $emailData);
    }

    /**
     * Limpia y valida los datos del email para evitar errores en las vistas
     */
    private function cleanEmailData(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = $value;
            } elseif (is_numeric($value)) {
                $cleaned[$key] = (string) $value;
            } elseif (is_bool($value)) {
                $cleaned[$key] = $value ? 'Sí' : 'No';
            } elseif (is_array($value)) {
                // Convertir arrays a strings separados por comas
                $cleaned[$key] = implode(', ', array_map(function ($item) {
                    if (is_string($item) || is_numeric($item)) {
                        return (string) $item;
                    }

                    return 'Valor no válido';
                }, $value));
            } elseif (is_null($value)) {
                $cleaned[$key] = '';
            } else {
                // Para otros tipos, intentar convertirlos a string
                $cleaned[$key] = (string) $value;
            }
        }

        return $cleaned;
    }

    /**
     * Valida que el wizard tenga los datos necesarios
     */
    private function validateWizardData(array $data): void
    {
        if (empty($data['user_criteria'])) {
            throw new \InvalidArgumentException('Debe especificar al menos un criterio de selección de usuarios');
        }

        if (empty($data['email_config'])) {
            throw new \InvalidArgumentException('Debe especificar la configuración del email');
        }

        if (empty($data['email_config']['mail_class'])) {
            throw new \InvalidArgumentException('Debe especificar la clase de email a enviar');
        }
    }

    /**
     * Obtiene la estructura de datos esperada para el wizard de mail
     */
    public function getExpectedDataStructure(): array
    {
        return [
            'user_criteria' => [
                'user_field_conditions' => [
                    [
                        'field' => 'string (e.g., "status")',
                        'operator' => 'equals|not_equals|contains|not_contains|is_null|is_not_null',
                        'value' => 'mixed',
                    ],
                ],
                'answer_conditions' => [
                    [
                        'question_id' => 'integer',
                        'operator' => 'equals|not_equals|contains|not_contains|greater_than|less_than|in|not_in',
                        'value' => 'mixed',
                    ],
                ],
            ],
            'email_config' => [
                'mail_class' => 'string (class name)',
                'step' => 'string|null',
                'data' => 'array (additional data for mail)',
            ],
        ];
    }

    /**
     * Obtiene las clases de email disponibles
     */
    public function getAvailableMailClasses(): array
    {
        return [
            \App\Mail\WelcomeMail::class => 'Correo de bienvenida',
            \App\Mail\ChangePassMail::class => 'Cambio de contraseña',
            \App\Mail\ContratacionMail::class => 'Confirmación de contratación',
            \App\Mail\FirstVisitMail::class => 'Recordatorio de primera visita',
            \App\Mail\UserBeneficiarioMail::class => 'Notificación para usuario beneficiario',
            \App\Mail\UserNoBeneficiarioMail::class => 'Notificación para usuario no beneficiario',
            \App\Mail\AvisoContratacionReferidoMail::class => 'Aviso: referido ha contratado',
            \App\Mail\FirstTimeFromMigracionMail::class => 'Primer mail tras migración',
        ];
    }
}
