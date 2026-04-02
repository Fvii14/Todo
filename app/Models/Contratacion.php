<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contratacion extends Model
{
    protected $table = 'contrataciones';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_payment_method',
        'product_id',
        'card_last4',
        'card_brand',
        'card_exp_month',
        'card_exp_year',
        'card_funding',
        'estado',
        'fase',
        'subfase',
        'fecha_contratacion',
        'ayuda_id',
        'monto_comision',
        'monto_total_ayuda',
        'liquidada',
    ];

    protected $casts = [
        'liquidada' => 'boolean',
        'monto_total_ayuda' => 'decimal:2',
        'monto_comision' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contrataciones()
    {
        return $this->hasMany(Contratacion::class); // Un usuario puede tener muchas contrataciones.
    }

    // Relación con el modelo Ayuda
    public function ayuda()
    {
        return $this->belongsTo(Ayuda::class);
    }

    // Relación con la tabla Products
    public function producto()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class); // Pagos directos
    }

    public function contratacionPagos()
    {
        return $this->hasMany(ContratacionPagos::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }

    public function isCompleta()
    {
        // 1️⃣ Comprobar documentos validados
        $documentosPendientes = collect();

        foreach ($this->documentos_faltantes as $doc) {
            $docValidado = \App\Models\UserDocument::where('user_id', $this->user_id)
                ->where('slug', $doc->slug)
                ->where('estado', 'validado')
                ->latest()
                ->first();

            if (! $docValidado) {
                $documentosPendientes->push($doc->slug);
            }
        }

        // 2️⃣ Comprobar convivientes completos (si es vivienda)
        $datosConvivientesCompletos = true;

        if ($this->ayuda->sector == 'vivienda' && $this->n_convivientes > 0) {
            for ($i = 1; $i <= $this->n_convivientes; $i++) {
                $conv = $this->convivientes->firstWhere('index', $i);
                if (! $conv || ! $conv->completo) {
                    $datosConvivientesCompletos = false;
                    break;
                }
            }
        }

        // 3️⃣ Resultado final
        return $documentosPendientes->isEmpty() && $datosConvivientesCompletos;
    }

    /**
     * Documentos de tramitación personalizados para esta contratación
     */
    public function documentosTramitacionPersonalizados()
    {
        return $this->hasMany(ContratacionDocumentoTramitacion::class)
            ->orderBy('orden');
    }

    public function userDocuments()
    {
        // clave foránea en user_documents = user_id
        // clave local en contratacion = user_id
        return $this->hasMany(
            \App\Models\UserDocument::class,
            'user_id',   // FK de la tabla user_documents
            'user_id'    // PK/local de la tabla contrataciones
        );
    }

    public function getValidadosAttribute(): int
    {
        // IDs de los documentos que exige la ayuda
        $docIds = $this->ayuda->documentos->pluck('id');

        // Cuenta los user_documents de este usuario y esta contratación
        return $this->user
            ->userDocuments
            ->whereIn('document_id', $docIds)
            ->where('estado', 'validado')
            ->count();
    }

    /**
     * Devuelve el número de documentos pendientes o faltantes.
     */
    public function getPendientesAttribute(): int
    {
        $total = $this->ayuda->documentos->count();

        // Ya tenemos cuántos validados
        return $total - $this->validados;
    }

    public function historial()
    {
        return $this->hasMany(HistorialActividad::class, 'contratacion_id')
            ->orderByDesc('fecha_inicio');
    }

    // Appends para DNI/teléfono (igual que antes)
    protected $with = ['user', 'ayuda', 'userDocuments'];

    protected $appends = ['dni', 'telefono'];

    public function getDniAttribute()
    {
        return optional($this->user->answers->firstWhere('question_id', 34))->getFormattedAnswer();
    }

    public function getTelefonoAttribute()
    {
        return optional($this->user->answers->firstWhere('question_id', 45))->getFormattedAnswer();
    }

    public function isEmpty() {}

    public function subsanacionDocumentos()
    {
        return $this->hasMany(SubsanacionDocument::class, 'contratacion_id');
    }

    public function pagosAdministracion()
    {
        return $this->hasMany(PagoAdministracion::class);
    }

    public function cobros()
    {
        return $this->hasMany(CobroTtf::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Esta funcion devuelve los Estados OPx asociados a la contratación (puede haber varios a la vez).
     */
    public function estadosContratacion(): BelongsToMany
    {
        return $this->belongsToMany(
            EstadoContratacion::class,
            'contratacion_estado_contratacion'
        );
    }

    public function getNombreAyudaAttribute(): ?string
    {
        return optional($this->ayuda)->nombre_ayuda;
    }

    public function motivosSubsanacionContrataciones()
    {
        return $this->hasMany(MotivoSubsanacionContratacion::class, 'contratacion_id');
    }

    // Relación con Estado
    public function estadoRef()
    {
        return $this->belongsTo(Estado::class, 'estado', 'slug');
    }

    // Relación con Fase
    public function faseRef()
    {
        return $this->belongsTo(Fase::class, 'fase', 'slug');
    }

    // Fuencion para cambiar uno o varios estados OPx de la contratación
    public function cambiarEstadosOPx(array $estados)
    {
        $this->estadosContratacion()->sync($estados);
    }

    /**
     * Devuelve el nombre del componente ayuda-card.estados.XXX según los estados OPx
     * de la tabla contratacion_estado_contratacion (prioridad de más específico a más genérico).
     */
    public function getAyudaCardComponentName(): string
    {
        $this->loadMissing('estadosContratacion');
        $codigos = $this->estadosContratacion->pluck('codigo')->all();

        if (in_array('OP5-Rechazado', $codigos, true)) {
            return 'ayuda-card.estados.cierre-rechazada';
        }
        if (in_array('OP4-Cobrando', $codigos, true)) {
            return 'ayuda-card.estados.cierre';
        }
        if (in_array('OP2-Documentacion', $codigos, true)) {
            return 'ayuda-card.estados.tramitacion-en_seguimiento';
        }
        if (in_array('OP1-Tramitacion', $codigos, true)) {
            return 'ayuda-card.estados.tramitacion';
        }
        if (in_array('OP1-Documentacion', $codigos, true)) {
            return 'ayuda-card.estados.documentacion';
        }

        return 'ayuda-card.estados.documentacion';
    }

    /**
     * Indica si el contenido del ayuda-card debe mostrarse siempre (sin colapsar).
     */
    public function getAyudaCardMostrarSiempre(): bool
    {
        $componente = $this->getAyudaCardComponentName();

        return in_array($componente, [
            'ayuda-card.estados.cierre',
            'ayuda-card.estados.cierre-rechazada',
            'ayuda-card.estados.tramitacion-en_seguimiento',
        ], true);
    }

    /**
     * Devuelve datos para mostrar badge de estado y porcentaje según estados OPx.
     * Keys: porcentaje, label, badge_classes (Tailwind), badge_classes_bs (Bootstrap), mensaje_estado, color_mensaje.
     */
    public function getAyudaCardDisplayData(): array
    {
        $componente = $this->getAyudaCardComponentName();

        return match ($componente) {
            'ayuda-card.estados.documentacion' => [
                'porcentaje' => 25,
                'label' => 'Documentación',
                'badge_classes' => 'bg-yellow-100 text-yellow-800',
                'badge_classes_bs' => 'bg-warning text-dark',
                'mensaje_estado' => '⚠️ Faltan por subir documentos o datos. Completa la información cuanto antes para que podamos tramitar tu ayuda.',
                'color_mensaje' => 'alert-warning',
            ],
            'ayuda-card.estados.tramitacion' => [
                'porcentaje' => 50,
                'label' => 'Tramitación',
                'badge_classes' => 'bg-blue-300 text-blue-900',
                'badge_classes_bs' => 'bg-info text-dark',
                'mensaje_estado' => '✅ Nuestro equipo ya está tramitando tu ayuda. No hace falta que hagas nada más por ahora.',
                'color_mensaje' => 'alert-info',
            ],
            'ayuda-card.estados.tramitacion-en_seguimiento' => [
                'porcentaje' => 50,
                'label' => 'En seguimiento',
                'badge_classes' => 'bg-blue-300 text-blue-900',
                'badge_classes_bs' => 'bg-info text-dark',
                'mensaje_estado' => '✅ Nuestro equipo ya está tramitando tu ayuda. No hace falta que hagas nada más por ahora.',
                'color_mensaje' => 'alert-info',
            ],
            'ayuda-card.estados.cierre' => [
                'porcentaje' => 100,
                'label' => 'Cierre',
                'badge_classes' => 'bg-green-100 text-green-800',
                'badge_classes_bs' => 'bg-success text-white',
                'mensaje_estado' => '🎉 ¡Enhorabuena! Tu ayuda ha sido concedida. Te contactaremos pronto para los siguientes pasos.',
                'color_mensaje' => 'alert-success',
            ],
            'ayuda-card.estados.cierre-rechazada' => [
                'porcentaje' => 100,
                'label' => 'Rechazada',
                'badge_classes' => 'bg-red-100 text-red-800',
                'badge_classes_bs' => 'bg-danger text-white',
                'mensaje_estado' => '❌ Lamentablemente, la Administración ha rechazado la solicitud. Si crees que se trata de un error o quieres saber si puedes presentar alegaciones, contacta con nosotros y te orientaremos.',
                'color_mensaje' => 'alert-danger',
            ],
            default => [
                'porcentaje' => 25,
                'label' => 'Documentación',
                'badge_classes' => 'bg-gray-100 text-gray-800',
                'badge_classes_bs' => 'bg-secondary text-white',
                'mensaje_estado' => null,
                'color_mensaje' => 'alert-info',
            ],
        };
    }

    /** Porcentaje de progreso (0-100) según estados OPx. */
    public function getAyudaCardPorcentaje(): int
    {
        return $this->getAyudaCardDisplayData()['porcentaje'];
    }

    /** Etiqueta del estado para el badge según estados OPx. */
    public function getAyudaCardEstadoLabel(): string
    {
        return $this->getAyudaCardDisplayData()['label'];
    }
}
