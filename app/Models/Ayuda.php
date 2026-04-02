<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayuda extends Model
{
    protected $table = 'ayudas';

    protected $fillable = [
        'ccaa_id',
        'nombre_ayuda',
        'codigo_hubspot',  // Ref. HS para HubSpot (ej: A1P-UNK-25, BAJ-And-25)
        'slug',
        'description',
        'sector',
        'create_time',
        'questionnaire_id',
        'presupuesto',       // Nuevo campo
        'fecha_inicio',     // Nuevo campo
        'fecha_fin',        // Nuevo campo
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'organo_id',
        'cuantia_usuario',
        'activo',
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'fecha_inicio' => 'date',    // Nuevo cast
        'fecha_fin' => 'date',       // Nuevo cast
        'presupuesto' => 'float',    // Nuevo cast
        'sector' => 'string',
    ];

    public function documentos()
    {
        return $this->belongsToMany(
            \App\Models\Document::class, // Ajusta el namespace si tu modelo Document está en otra ruta
            'ayuda_documentos',          // Nombre de la tabla pivote
            'ayuda_id',                  // FK hacia Ayuda en la pivote
            'documento_id'                // FK hacia Document en la pivote
        );
    }

    public function ayudaDocumentos()
    {
        return $this->hasMany(AyudaDocumento::class);
    }

    // Relación con Questionnaire
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function questionnaires()
    {
        // questionnaires.ayuda_id -> ayudas.id
        return $this->hasMany(\App\Models\Questionnaire::class, 'ayuda_id');
    }

    public function datos()
    {
        return $this->hasMany(AyudaDato::class, 'ayuda_id');
    }

    public function organo()
    {
        return $this->belongsTo(Organo::class, 'organo_id');
    }

    const SECTOR_FAMILIA = 'familia';

    const SECTOR_TRABAJO = 'trabajo';

    const SECTOR_REFORMAS_OBRAS = 'Reformas y Obras';

    const SECTOR_DESARROLLO_TECNOLOGICO = 'Desarrollo Tecnológico';

    const SECTOR_VIAJE = 'viaje';

    const SECTOR_VIVIENDA = 'vivienda';

    const SECTOR_IMV = 'imv';

    const SECTOR_EDUCACION = 'educacion';

    const SECTOR_SALUD = 'salud';

    const SECTOR_EMPLEO = 'empleo';

    const SECTOR_JOVENES = 'jovenes';

    const SECTOR_POBREZA = 'pobreza';

    const SECTOR_MUJER = 'mujer';

    const SECTOR_MEDIO_AMBIENTE = 'Medio Ambiente';

    public static function getSectores()
    {
        return [
            self::SECTOR_FAMILIA,
            self::SECTOR_TRABAJO,
            self::SECTOR_REFORMAS_OBRAS,
            self::SECTOR_DESARROLLO_TECNOLOGICO,
            self::SECTOR_VIAJE,
            self::SECTOR_VIVIENDA,
            self::SECTOR_IMV,
            self::SECTOR_EDUCACION,
            self::SECTOR_SALUD,
            self::SECTOR_EMPLEO,
            self::SECTOR_JOVENES,
            self::SECTOR_POBREZA,
            self::SECTOR_MUJER,
            self::SECTOR_MEDIO_AMBIENTE,
            self::SECTOR_IMV,
        ];
    }

    public function getDineroFormateado($cantidad, $decimals = 2)
    {
        return number_format($cantidad, $decimals, ',', '.').'€';
    }

    public function documents()
    {
        return $this->hasMany(AyudaDocumento::class);
    }

    public function requisitos()
    {
        return $this->hasMany(AyudaRequisito::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'ayudas_id');
    }

    /**
     * Relación con Products a través de AyudaProducto
     */
    public function productos()
    {
        return $this->belongsToMany(Product::class, 'ayuda_producto', 'ayuda_id', 'product_id')
            ->withPivot('recomendado')
            ->withTimestamps();
    }

    /**
     * Relación con AyudaProducto
     */
    public function ayudaProductos()
    {
        return $this->hasMany(AyudaProducto::class, 'ayuda_id');
    }

    public function cuestionarioPrincipal()
    {
        return $this->hasOne(Questionnaire::class, 'ayuda_id')
            ->where('tipo', 'pre'); // usa el nuevo campo ENUM
    }

    /**
     * Devuelve el id del questionnaire de tipo 'conviviente' para una ayuda dada.
     */
    public static function getConvivienteQuestionnaireId(int $ayudaId): ?int
    {
        return Questionnaire::where('ayuda_id', $ayudaId)
            ->where('tipo', 'conviviente')
            ->value('id');
    }

    public function enlaces()
    {
        return $this->hasMany(AyudaEnlace::class);
    }

    public function recursos()
    {
        return $this->belongsToMany(Recurso::class, 'ayuda_recurso', 'ayuda_id', 'recurso_id')
            ->withPivot('orden', 'activo')
            ->withTimestamps();
    }

    public function preRequisitos()
    {
        return $this->hasMany(AyudaPreRequisito::class, 'ayuda_id')->ordered();
    }

    public function preRequisitosActivos()
    {
        return $this->hasMany(AyudaPreRequisito::class, 'ayuda_id')
            ->where('active', true)
            ->ordered();
    }

    public function preRequisitosRequeridos()
    {
        return $this->hasMany(AyudaPreRequisito::class, 'ayuda_id')
            ->where('active', true)
            ->where('is_required', true)
            ->ordered();
    }

    public function motivosSubsanacionAyuda()
    {
        return $this->hasMany(MotivoSubsanacionAyuda::class, 'ayuda_id');
    }

    public static function idsPorPrograma(): array
    {
        return self::where('slug', 'like', 'programa-estatal-de-vivienda%')
            ->pluck('id')
            ->all();
    }

    // el slug debe empezar por bono-alquiler-joven- + la ccaa
    public static function idsBonoAlquilerJoven(): array
    {
        return self::where('slug', 'like', 'bono-alquiler-joven%')
            ->pluck('id')
            ->all();

    }

    public function documentosConvivientes()
    {
        return $this->belongsToMany(
            Document::class,
            'ayuda_documentos_convivientes',
            'ayuda_id',
            'documento_id'
        )->withPivot('es_obligatorio');
    }

    public function ayudaDocumentosConvivientes()
    {
        return $this->hasMany(AyudaDocumentoConviviente::class);
    }

    public function ayudaRequisitosJson()
    {
        return $this->hasMany(AyudaRequisitoJson::class, 'ayuda_id');
    }

    /**
     * Relación con CCAA
     */
    public function ccaa()
    {
        return $this->belongsTo(Ccaa::class, 'ccaa_id');
    }

    /**
     * Funcion que obtiene la ccaa_id de la ayuda y despues obtiene el nombre de la ccaa
     * y lo devuelve
     */
    public function getCcaa()
    {
        if (! $this->ccaa_id) {
            return null;
        }

        // Usar find() directamente en lugar de acceder a la relación para evitar problemas de serialización
        $ccaa = Ccaa::find($this->ccaa_id);

        return $ccaa ? $ccaa->nombre_ccaa : null;
    }
}
