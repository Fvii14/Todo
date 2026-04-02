<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable (mass assignment).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'ref_code',
        'id_unidad_familiar',
        'brevo_id',
        'ref_by',
        'holded_contact_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            do {
                $code = strtoupper(Str::random(8));
            } while (User::where('ref_code', $code)->exists());

            $user->ref_code = $code;

            do {
                $id_unidad_familiar = mt_rand(100000000, 999999999);
            } while (User::where('id_unidad_familiar', $id_unidad_familiar)->exists());

            $user->id_unidad_familiar = $id_unidad_familiar;
        });
    }

    // Siempre carga sus respuestas para tenerlas disponibles
    protected $with = ['answers'];

    // Expone estos atributos extra al serializar el usuario
    protected $appends = ['dni', 'telefono', 'calle_completa', 'holded_address'];

    /**
     * Accessor para DNI (question_id = 34).
     */
    public function getDniAttribute(): ?string
    {
        return optional(
            $this->answers->firstWhere('question_id', 34)
        )->getFormattedAnswer();
    }

    /**
     * Accessor para Teléfono (question_id = 45).
     */
    public function getTelefonoAttribute(): ?string
    {
        return optional(
            $this->answers->firstWhere('question_id', 45)
        )->getFormattedAnswer();
    }

    public function taxInfo()
    {
        return $this->hasOne(UserTaxInfo::class);
    }

    public function convivientes()
    {
        return $this->hasMany(Conviviente::class);
    }

    public function ayudasSolicitadas()
    {
        return $this->hasMany(AyudaSolicitada::class);
    }

    public function contrataciones()
    {
        return $this->hasMany(Contratacion::class);
    }

    public function notasContrataciones()
    {
        return $this->hasMany(NotaContratacion::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(AyudaSolicitada::class);
    }

    public function passwordResetTokens() // Para enlazar con la tabla password_reset_token
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }

    public function mailTracking()
    {
        return $this->hasMany(MailTracking::class);
    }

    public function userDocuments()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function historialActividad()
    {
        return $this->hasMany(HistorialActividad::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Obtiene las respuestas del usuario a las preguntas del formulario.
     * Devuelve un array indexado por question_id con las respuestas decodificadas.
     *
     * @param  bool  $includeConvivientes  Si es true, incluye respuestas de convivientes
     * @return array Array con estructura [question_id => answer]
     */
    public function obtenerRespuestas(bool $includeConvivientes = false): array
    {
        $query = Answer::where('user_id', $this->id);

        if (! $includeConvivientes) {
            $query->whereNull('conviviente_id');
        }

        $rawAnswers = $query->pluck('answer', 'question_id');

        $answers = $rawAnswers->map(function ($answer) {
            $decoded = json_decode($answer, true);

            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
        });

        // Asegurar que las claves (question_id) se mantengan al convertir a array
        $answersArray = [];
        foreach ($answers as $questionId => $answer) {
            $answersArray[$questionId] = $answer;
        }

        return $answersArray;
    }

    /**
     * Obtiene las respuestas del usuario para los slugs objetivo específicos.
     * Útil para obtener respuestas de preguntas específicas por su slug.
     *
     * @param  array  $slugsObjetivo  Array de slugs de preguntas a buscar
     * @return array Array con estructura [slug => answer]
     */
    public function obtenerRespuestasPorSlugs(array $slugsObjetivo): array
    {
        // Mapeo slug -> question_id desde la tabla global `questions`
        $pregsNecesarias = Question::query()
            ->whereIn(DB::raw('TRIM(slug)'), $slugsObjetivo)
            ->get(['id', 'slug']);

        $slugToId = $pregsNecesarias
            ->mapWithKeys(fn ($q) => [trim($q->slug) => (int) $q->id])
            ->all();

        // Obtener respuestas directamente desde la BD usando los question_id
        $answers = Answer::where('user_id', $this->id)
            ->whereNull('conviviente_id')
            ->whereIn('question_id', array_values($slugToId))
            ->pluck('answer', 'question_id')
            ->map(function ($answer) {
                $decoded = json_decode($answer, true);

                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
            })
            ->all();

        // Construir respuestas por slug
        $answersPorSlug = [];
        foreach ($slugToId as $slug => $questionId) {
            $answersPorSlug[$slug] = $answers[$questionId] ?? null;
        }

        return $answersPorSlug;
    }

    public function crmTasks()
    {
        return $this->hasMany(CrmTask::class);
    }

    public function comunicaciones()
    {
        return $this->hasMany(ComunicacionOperativa::class);
    }

    public function stateHistory()
    {
        return $this->hasMany(CrmStateHistory::class);
    }

    public function ayudas()
    {
        return $this->hasMany(UserAyuda::class);
    }

    public function nombrePila(): ?string
    {
        // Prioridad: atributo calculado nombre_completo → answer 177 → name
        $answer177 = $this->relationLoaded('answers')
            ? $this->answers->firstWhere('question_id', 177)?->answer
            : $this->answers()->where('question_id', 177)->value('answer');
        $nombreCompleto = $this->nombre_completo ?? $answer177 ?? $this->name;

        if (! $nombreCompleto) {
            return null;
        }

        $tokens = preg_split('/\s+/', trim($nombreCompleto));

        return $tokens[0] ?? null; // primera palabra = nombre de pila
    }

    public function getApellido1Attribute(): ?string
    {
        return optional($this->answers->firstWhere('question_id', 170))->answer
            ? trim($this->answers->firstWhere('question_id', 170)->answer) : null;
    }

    public function getApellido2Attribute(): ?string
    {
        return optional($this->answers->firstWhere('question_id', 171))->answer
            ? trim($this->answers->firstWhere('question_id', 171)->answer) : null;
    }

    /**
     * Obtiene el nombre completo del usuario construido a partir de las respuestas
     * de las preguntas con los slugs: solo_nombre, primer_apellido, segundo_apellido
     */
    public function getNombreCompletoFromAnswers(): string
    {
        $slugs = ['solo_nombre', 'primer_apellido', 'segundo_apellido'];

        $questionsBySlug = \App\Models\Question::whereIn('slug', $slugs)->get()->keyBy('slug');

        $nombre = $this->answers()
            ->where('question_id', $questionsBySlug->get('solo_nombre')?->id)
            ->value('answer') ?? '';

        $primerApellido = $this->answers()
            ->where('question_id', $questionsBySlug->get('primer_apellido')?->id)
            ->value('answer') ?? '';

        $segundoApellido = $this->answers()
            ->where('question_id', $questionsBySlug->get('segundo_apellido')?->id)
            ->value('answer') ?? '';

        $nombreCompleto = trim(collect([$nombre, $primerApellido, $segundoApellido])->filter()->implode(' '));

        return $nombreCompleto ?: $this->name ?? 'Sin nombre';
    }

    public function comunicacionesOperativas()
    {
        return $this->hasMany(ComunicacionOperativa::class, 'user_id');
    }

    // Enum de estado de usuario
    public const ESTADO_FRIO = 'frio';

    public const ESTADO_TIBIO = 'tibio';

    public const ESTADO_CALIENTE = 'caliente';

    // Accesor para icono y color
    public function getEstadoUsuarioIconoAttribute()
    {
        switch ($this->estado_usuario) {
            case self::ESTADO_FRIO:
                return ['icon' => 'fa-snowflake', 'color' => 'text-blue-400'];
            case self::ESTADO_TIBIO:
                return ['icon' => 'fa-mug-hot', 'color' => 'text-yellow-500'];
            case self::ESTADO_CALIENTE:
                return ['icon' => 'fa-fire', 'color' => 'text-red-500'];
            default:
                return ['icon' => 'fa-question-circle', 'color' => 'text-gray-400'];
        }
    }

    public function getNombreCompletoAttribute(): ?string
    {
        $val =
            optional($this->answers->firstWhere('question_id', 33))->answer
            ?? optional($this->answers->firstWhere('question_id', 177))->answer
            ?? $this->name;

        return $val ? trim($val) : null;
    }

    // 👉 Helper genérico para leer una respuesta (formateada si existe)
    protected function answerValue(int $qid): ?string
    {
        $a = $this->answers->firstWhere('question_id', $qid);
        if (! $a) {
            return null;
        }

        // Si tu modelo Answer tiene getFormattedAnswer()
        if (method_exists($a, 'getFormattedAnswer')) {
            $v = $a->getFormattedAnswer();
        } else {
            $v = $a->answer;
        }
        $v = is_string($v) ? trim($v) : $v;

        return $v === '' ? null : $v;
    }

    /** COMPONENTES SUELTOS **/
    public function getProvinciaAttribute(): ?string
    {
        return $this->answerValue(36);
    }

    public function getMunicipioAttribute(): ?string
    {
        return $this->answerValue(37);
    }

    public function getComunidadAutonomaAttribute(): ?string
    {
        return $this->answerValue(38);
    }

    public function getCodigoPostalAttribute(): ?string
    {
        return $this->answerValue(39);
    }

    public function getTipoViaAttribute(): ?string
    {
        return $this->answerValue(104);
    }

    public function getNombreViaAttribute(): ?string
    {
        return $this->answerValue(105);
    }

    public function getNumeroDomicilioAttribute(): ?string
    {
        return $this->answerValue(106);
    }

    public function getBloqueAttribute(): ?string
    {
        return $this->answerValue(107);
    }

    public function getPortalAttribute(): ?string
    {
        return $this->answerValue(108);
    }

    public function getEscaleraAttribute(): ?string
    {
        return $this->answerValue(109);
    }

    public function getPisoAttribute(): ?string
    {
        return $this->answerValue(110);
    }

    public function getPuertaAttribute(): ?string
    {
        return $this->answerValue(111);
    }

    /** Calle completa tipo “Calle Mayor 10, Bloque 2, Portal B, Esc. 1, Piso 3, Puerta D” */
    public function getCalleCompletaAttribute(): ?string
    {
        $main = trim(implode(' ', array_filter([
            $this->tipo_via,         // p.ej. "Calle", "Avda."
            $this->nombre_via,       // p.ej. "Mayor"
            $this->numero_domicilio, // p.ej. "10"
        ], fn ($v) => ! is_null($v) && $v !== '')));

        $extras = array_values(array_filter([
            $this->bloque ? 'Bloque '.$this->bloque : null,
            $this->portal ? 'Portal '.$this->portal : null,
            $this->escalera ? 'Esc. '.$this->escalera : null,
            $this->piso ? 'Piso '.$this->piso : null,
            $this->puerta ? 'Puerta '.$this->puerta : null,
        ]));

        if (! $main && empty($extras)) {
            return null;
        }

        return $main.(empty($extras) ? '' : ', '.implode(', ', $extras));
    }

    /**
     * Dirección lista para Holded.
     * Campos típicos: street, city, zipCode, country (y opcional region/province).
     */
    public function getHoldedAddressAttribute(): ?array
    {
        $street = $this->calle_completa;
        $city = $this->municipio;
        $zip = $this->codigo_postal;

        // Si no hay nada relevante, no devolvemos dirección
        if (! $street && ! $city && ! $zip) {
            return null;
        }

        $addr = [
            'street' => $street ?? '',
            'city' => $city ?? '',
            'zipCode' => $zip ?? '',
            'country' => 'ES',             // por defecto España
        ];

        // Algunas cuentas de Holded aceptan "region" o "state" para provincia
        if ($this->provincia) {
            $addr['region'] = $this->provincia;
            // $addr['state'] = $this->provincia; // si prefieres 'state'
        }

        return $addr;
    }

    public function arrendatarios()
    {
        return $this->hasMany(\App\Models\Arrendatario::class, 'user_id');
    }

    public function saleAlerts()
    {
        return $this->hasMany(SaleAlert::class);
    }

    public function referidos()
    {
        return $this->hasMany(User::class, 'ref_by');
    }
}
