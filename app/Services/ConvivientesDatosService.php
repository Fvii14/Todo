<?php

namespace App\Services;

use App\Models\Conviviente;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use Illuminate\Support\Facades\DB;

class ConvivientesDatosService
{
    private $cuestionarioCompletoService;

    public function __construct(CuestionarioCompletoService $cuestionarioCompletoService)
    {
        $this->cuestionarioCompletoService = $cuestionarioCompletoService;
    }

    /**
     * Procesa toda la información necesaria del bloque
     * "convivientes" de la funcion index de AyudaSolicitadasController
     */
    public function obtenerDatosConvivientes($userId, $ayudaSolicitada, $convivienteQuestionnaire = null)
    {
        $result = [
            'questionnaire_id' => null,
            'conditions' => [],
            'preguntas_finales' => [],
            'estadoConvivientes' => [],
            'convivientes' => [],
        ];

        // Obtener ID del cuestionario de convivientes
        if ($convivienteQuestionnaire) {
            $convivienteQuestionnaireId = $convivienteQuestionnaire->id;
        } else {
            $convivienteQuestionnaireId = Questionnaire::where('ayuda_id', $ayudaSolicitada->ayuda->id)
                ->where('tipo', 'conviviente')
                ->value('id');
        }

        $result['questionnaire_id'] = $convivienteQuestionnaireId;

        // Verificar si existe cuestionario de convivientes antes de llamar al método
        if ($convivienteQuestionnaireId !== null) {
            $estadoConvivientes[$convivienteQuestionnaireId] = $this->cuestionarioCompletoService
                ->convivientesTienenCuestionarioCompleto($userId, $convivienteQuestionnaireId);
        }

        if (! $convivienteQuestionnaireId) {
            return $result; // No hay cuestionario → devolvemos vacío
        }

        $result['estadoConvivientes'] = $estadoConvivientes[$convivienteQuestionnaireId];

        // Obtener condiciones del cuestionario de convivientes
        $conditions = QuestionCondition::getConditions($convivienteQuestionnaireId);

        $result['conditions'] = $conditions;

        // !!Esto hay que cambiarlo en el futuro
        // Preguntas finales obligatorias (podrías mover esto a config)
        $preguntasObligatorias = [33, 34, 40, 42, 117, 118, 147, 145, 85, 127, 142, 143, 144, 152, 153, 154, 157, 156, 158];

        $preguntasFormulario = DB::table('questionnaire_questions')
            ->where('questionnaire_id', $convivienteQuestionnaireId)
            ->pluck('question_id')
            ->toArray();

        $preguntasFinales = array_intersect($preguntasObligatorias, $preguntasFormulario);
        $result['preguntas_finales'] = $preguntasFinales;

        // Obtener convivientes del user
        $convivientes = Conviviente::byUser($userId)
            ->orderBy('index')
            ->get();

        // Calcular si el formulario de convivientes esta "completo" para cada conviviente
        foreach ($convivientes as $conviviente) {
            $resultado = $this->cuestionarioCompletoService->convivienteTieneCuestionarioCompleto(
                $conviviente->id,
                $convivienteQuestionnaireId
            );
            $conviviente->completo = $resultado['completo'];
        }

        $result['convivientes'] = $convivientes;

        return $result;
    }
}
