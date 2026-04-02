<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(CcaaSeeder::class);
        $this->call(OrganosTableSeeder::class);
        $this->call(QuestionnaireSeeder::class);
        $this->call(AyudaSeeder::class);
        $this->call(DocumentoSeeder::class);
        $this->call(DocumentoSeeder2::class);
        $this->call(QuestionSeeder::class);
        $this->call(AyudaRequisitoSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AyudaAlquilerSeeder::class);
        $this->call(AyudaDocumentoSeeder::class);
        $this->call(AyudasCcaaSeeder::class);
        $this->call(QuestionSectorSeeder::class);
        $this->call(QuestionCategoriaSeeder::class);
        $this->call(AnyadirPreguntasCCAASeeder::class);
        $this->call(QuestionSeederVivesAlquiler::class);
        $this->call(QuestionnaireQuestionSeeder::class);
        $this->call(QuestionnaireQuestionSeeder2::class);
        $this->call(QuestionnaireSeeder2::class);
        $this->call(QuestionConditionSeeder::class);
        $this->call(ProvinciaSeeder::class);
        $this->call(MunicipioSeeder::class);
        $this->call(QuestionConditionSeeder2::class);

        $this->call(AyudaRequisitosJsonBAJAndaluciaSeeder::class);
        $this->call(AyudaRequisitosJsonBAJMadridSeeder::class);
        $this->call(AyudaRequisitosJsonBAJGenericoSeeder::class);
        $this->call(AddCuestionarioIdAyudaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVCastillaLSeeder::class);
        $this->call(AyudaRequisitosJsonBAJPVascoSeeder::class);
        $this->call(AyudaRequisitosJsonPAVAndaluciaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVAragonSeeder::class);
        $this->call(AyudaRequisitosJsonPAVAsturiasSeeder::class);
        $this->call(AyudaRequisitosJsonPAVIslasBalearesSeeder::class);
        $this->call(AyudaRequisitosJsonBAJBalearesSeeder::class);
        $this->call(AyudaRequisitosJsonPAVCanariasSeeder::class);
        $this->call(AyudaRequisitosJsonBAJCanariasSeeder::class);
        $this->call(AyudaRequisitosJsonPAVCantabriaSeeder::class);
        $this->call(AyudaRequisitosJsonBAJCantabriaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVExtremaduraSeeder::class);
        $this->call(AyudaRequisitosJsonPAVLaRiojaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVValenciaSeeder::class);
        $this->call(QuestionnaireQuestionsSeeder3::class);
        $this->call(AyudaRequisitosJsonPAVMadridSeeder::class);
        $this->call(AyudaRequisitosJsonBAJGaliciaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVGaliciaSeeder::class);
        $this->call(AyudaRequisitosJsonBAJCatalunyaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVCatalunyaSeeder::class);
        $this->call(AyudaRequisitosJsonPAVCastillaLMSeeder::class);
        $this->call(AyudaRequisitosJsonPAVMurciaSeeder::class);
        $this->call(AyudaRequisitosJsonBAJMurciaSeeder::class);
        $this->call(AyudaRequisitosJsonBAJNavarraSeeder::class);
        $this->call(AyudaRequisitosJsonBAJAragon::class);
        $this->call(QuestionSeeder2::class);
        $this->call(AyudasHijosMadridGaliciaSeeder::class);
        $this->call(AyudaPeriodosSeeder::class);
        $this->call(QuestionSeeder3::class);
        $this->call(AyudasHijosMadridGaliciaSeeder2::class);
        $this->call(CuestionariosConvivientesSeeder::class);
        $this->call(QuestionnaireSolicitudSeeder::class);
        $this->call(QuestionnaireQuestionPAVCatalunya::class);
        $this->call(QuestionnaireQuestionsSeeder4::class);
        $this->call(DocumentMultiUploadSeeder::class);
        $this->call(FormCollectorSeeder::class);
        $this->call(PAVsCatalunyaSeeder::class);
        $this->call(QuestionSeederDatos::class);
        $this->call(AyudaRequisitosJsonSeeder::class);

        $this->call(QuestionSolicitanteSeeder::class);
        $this->call(QuestionnaireQuestionSolicitante::class);
        $this->call(QuestionsCoditionsSolicitud::class);
        $this->call(QQCuestionariosConvivientesSeeder::class);

        $this->call(ProductosSeeder::class);
        $this->call(AyudaRequisitosJsonSeeder::class);
        $this->call(QuestionConditionConvivientesSeeder::class);

        $this->call(InitialUsersSeeder::class);
        $this->call(AyudaEnlaceSeeder::class);

        $this->call(QuestionConditionGananciaSeeder::class);

    }
}
