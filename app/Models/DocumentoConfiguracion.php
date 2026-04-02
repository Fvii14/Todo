<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentoConfiguracion extends Model
{
    use HasFactory;

    protected $table = 'documento_configuraciones';

    protected $fillable = [
        'contratacion_id',
        'document_id',
        'visible',
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    public function contratacion()
    {
        return $this->belongsTo(Contratacion::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Obtener documentos visibles para una contratación
     * Si no hay configuración guardada, devuelve los documentos de la ayuda por defecto
     */
    public static function getDocumentosVisibles($contratacionId)
    {
        $contratacion = Contratacion::with('ayuda.ayudaDocumentos.documento')->find($contratacionId);

        if (! $contratacion || ! $contratacion->ayuda) {
            return collect();
        }

        // Obtener respuestas del usuario para determinar documentos especiales
        $answers = DB::table('answers')
            ->where('user_id', $contratacion->user_id)
            ->pluck('answer', 'question_id')
            ->map(function ($answer) {
                $decoded = json_decode($answer, true);

                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $answer;
            });

        // Obtener slugs de documentos especiales condicionales (usando la misma lógica que AyudasSolicitadasController)
        $slugsEspeciales = self::obtenerSlugsDocumentosEspecialesCondicionales($contratacion->ayuda->id, $answers);

        // Buscar configuración guardada
        $configuraciones = self::where('contratacion_id', $contratacionId)
            ->where('visible', true)
            ->with('document')
            ->get();

        // Obtener documentos de ayuda aplicables (obligatorios + especiales según respuestas)
        $documentosAyuda = \App\Models\AyudaDocumento::with('documento')
            ->where('ayuda_id', $contratacion->ayuda->id)
            ->get()
            ->filter(function ($docRel) use ($slugsEspeciales) {
                return $docRel->documento &&
                    (
                        $docRel->documento->tipo === 'general' ||
                        ($docRel->documento->tipo === 'especial' && in_array($docRel->documento->slug, $slugsEspeciales))
                    );
            })
            ->pluck('documento')
            ->filter();

        // Si hay configuración guardada, filtrar solo los que NO estén en ayuda_documentos
        if ($configuraciones->isNotEmpty()) {
            $documentosConfigurados = $configuraciones->pluck('document')->filter();

            // Obtener IDs de documentos de ayuda para comparar
            $idsDocumentosAyuda = $documentosAyuda->pluck('id')->toArray();

            // Filtrar documentos configurados que NO estén en ayuda_documentos
            $documentosAdicionales = $documentosConfigurados->filter(function ($doc) use ($idsDocumentosAyuda) {
                return ! in_array($doc->id, $idsDocumentosAyuda);
            });

            $resultado = $documentosAdicionales;
        } else {
            // Si NO hay configuración, usar documentos de ayuda (obligatorios + especiales según respuestas)
            $resultado = $documentosAyuda;
        }

        return $resultado;
    }

    /**
     * Obtener slugs de documentos especiales condicionales basados en las respuestas del usuario
     * Copiado de AyudasSolicitadasController para mantener la misma lógica
     */
    private static function obtenerSlugsDocumentosEspecialesCondicionales($ayudaId, $answers)
    {
        $ayuda = \App\Models\Ayuda::with('questionnaire.questions')->find($ayudaId);

        if (! $ayuda || ! $ayuda->questionnaire) {
            return [];
        }

        $preguntas = $ayuda->questionnaire->questions;
        // Añadirlas a la colección original
        $slugsFaltantes = ['propietario-vivienda', 'situaciones-propietario'];

        $slugsExistentes = $preguntas->pluck('slug')->all();

        $slugsQueFaltan = array_diff($slugsFaltantes, $slugsExistentes);

        if (! empty($slugsQueFaltan)) {
            $preguntasFaltantes = \App\Models\Question::whereIn('slug', $slugsQueFaltan)->get();
            $preguntas = $preguntas->concat($preguntasFaltantes);
        }

        $preguntasVulnerabilidad = $preguntas->filter(function ($q) {
            return in_array($q->slug, [
                'grupo-vulnerable',
                'familia-vulnerable',
                'situacion-especial',
                'situacion-especial-2',
                'situaciones-propietario',
                'situaciones-conviviente-propietario',
                'andalucia-grupo-vulnerable',
                'andalucia-grupo-vulnerable2',
                'propietario-vivienda',
            ]);
        });

        $answersVulnerabilidad = $preguntasVulnerabilidad->mapWithKeys(function ($q) use ($answers) {
            return [$q->slug => $answers[$q->id] ?? null];
        });

        $documentosEspeciales = DB::table('ayuda_documentos')
            ->where('ayuda_id', $ayudaId)
            ->join('documents', 'ayuda_documentos.documento_id', '=', 'documents.id')
            ->where('documents.tipo', 'especial')
            ->select('documents.slug')
            ->get()
            ->pluck('slug')
            ->toArray();

        $slugs = [];

        if ($answersVulnerabilidad->get('grupo-vulnerable') === 'Familia numerosa, monoparental, persona con discapacidad ±33%') {
            $familia = (array) $answersVulnerabilidad->get('familia-vulnerable');
            if (in_array('Familia numerosa', $familia)) {
                $slugs[] = 'certificado-familia-numerosa';
            }
            if (in_array('Persona con discapacidad ≥ 33%', $familia)) {
                $slugs[] = 'certificado-discapacidad';
            }
            if (in_array('Familia monoparental', $familia)) {
                $slugs[] = 'certificado-familia-monoparental';
            }
        }

        if ($answersVulnerabilidad->get('grupo-vulnerable') === 'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a') {
            $casos = (array) $answersVulnerabilidad->get('situacion-especial-2');
            if (in_array('He sido víctima de violencia de género', $casos)) {
                $slugs[] = 'certificado-violencia-genero';
            }
            if (in_array('He sido víctima de terrorismo', $casos)) {
                $slugs[] = 'certificado-victima-terrorismo';
            }
            if (in_array('Estoy en riesgo de exclusión social', $casos)) {
                $slugs[] = 'certificado-riesgo-exclusion-social';
            }
            if (in_array('Soy joven extutelado/a', $casos)) {
                $slugs[] = 'certificado-centro-residencial-menores';
            }
            if (in_array('He estado en prisión (exconvicto/a)', $casos)) {
                $slugs[] = 'certificado-exconvicto';
            }
        }

        if ($answersVulnerabilidad->get('grupo-vulnerable') === 'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones') {
            $situacion = (array) $answersVulnerabilidad->get('situacion-especial');
            if (in_array('Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones', $situacion)) {
                $slugs[] = 'certificado-situacion-desempleo';
            }
        }

        if ($answersVulnerabilidad->get('grupo-vulnerable') === 'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica') {
            $situacion = (array) $answersVulnerabilidad->get('situacion-especial');
            if (in_array('Desahucio o ejecución hipotecaria de mi vivienda habitual en los últimos cinco años', $situacion)) {
                $slugs[] = 'certificado-desahucio';
            }
            if (in_array('Dación en pago de mi vivienda habitual en los últimos cinco años', $situacion)) {
                $slugs[] = 'certificado-dacion-pago';
            }
            if (in_array('Situación catastrófica que afecte a mi vivienda habitual', $situacion)) {
                $slugs[] = 'certificado-situacion-catastrofica';
            }
        }

        $esPropietario = $answersVulnerabilidad->get('propietario-vivienda');
        $situacionesPropietario = (array) $answersVulnerabilidad->get('situaciones-propietario');

        if ((int) $esPropietario === 1 && ! empty($situacionesPropietario)) {
            if (! in_array('Ninguna de las anteriores', $situacionesPropietario)) {
                if (in_array('Separación o divorcio', $situacionesPropietario)) {
                    $slugs[] = 'resolucion_divorcio_separacion';
                }
                if (in_array('Propietario por herencia de una parte de la casa', $situacionesPropietario)) {
                    $slugs[] = 'nota-simple';
                }
                if (
                    in_array('Propiedad inaccesible por discapacidad tuya o de algún miembro de tu unidad de convivencia', $situacionesPropietario) ||
                    in_array('No puedes acceder a casa por cualquier causa ajena a tu voluntad', $situacionesPropietario)
                ) {
                    $slugs[] = 'justificante_imposibilidad_habitar_vivienda';
                }
            }
        }

        return array_values(array_intersect($slugs, $documentosEspeciales));
    }

    /**
     * Configurar documentos visibles para una contratación
     */
    public static function configurarDocumentos($contratacionId, $documentIds)
    {
        // Eliminar configuraciones existentes
        self::where('contratacion_id', $contratacionId)->delete();

        // Crear nuevas configuraciones
        $configuraciones = [];
        foreach ($documentIds as $documentId) {
            $configuraciones[] = [
                'contratacion_id' => $contratacionId,
                'document_id' => $documentId,
                'visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($configuraciones)) {
            self::insert($configuraciones);
        }
    }

    /**
     * Verificar si un documento está configurado como visible
     */
    public static function isDocumentoVisible($contratacionId, $documentId)
    {
        return self::where('contratacion_id', $contratacionId)
            ->where('document_id', $documentId)
            ->where('visible', true)
            ->exists();
    }
}
