<?php

namespace Database\Seeders;

use App\Models\Ayuda;
use App\Models\AyudaEnlace;
use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudaEnlaceSeeder extends Seeder
{
    public function run(): void
    {
        AyudaEnlace::insert([
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_100_por_hijo')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.boe.es/buscar/act.php?id=BOE-A-2006-20764#dt-5',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'ingreso_minimo_vital')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.boe.es/buscar/act.php?id=BOE-A-2021-21007',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_cataluna')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/32827f0a-2a27-4181-b98f-b8ee39601911/BASES_BAJ_CATALUNA.pdf?table=block&id=214b52f3-795c-802f-8f1c-fea314cfc44e&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=3RlD0nBw-7LIfKarwzXq76JLUIjrNj3zsSj8TETfLBQ&downloadName=BASES+BAJ+CATALUÑA.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_cataluna_menos_36')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://dogc.gencat.cat/es/document-del-dogc/index.html?documentId=1011898',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_valencia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://dogv.gva.es/datos/2025/03/17/pdf/2025_6481_es.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_valencia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://habitatge.gva.es/es/web/vivienda-y-calidad-en-la-edificacion/ajudes-lloguer-habitatge-2025/bases-i-convocatoria',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_canarias')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.gobiernodecanarias.org/cmsgob2/export/sites/vivienda/galerias/docs/Informacion/General/boc-a-2021-003-67.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_canarias')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.gobiernodecanarias.org/boc/2022/151/016.html',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_rioja')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://ias1.larioja.org/boletin/Bor_Boletin_visor_Servlet?referencia=22101745-1-PDF-548641',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_rioja')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://ias1.larioja.org/cexabc/trami/DocumentoServletEnc?code=024901180111012001220068007500770079005101110112011501200129012900810107005801230135012200870105007600790076007900800084008100880083008700900095009100910091009200880157014401490084016501550163016301120090016501530173017501180133009901790161016901620163016401730178017801300121011101820184018501750190017801410163018301960195019302030184019301240158014002000128014201430217013201460147019702110203022302150151021602070208014702070223021802160194018301840176&1748501514972',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_extremadura')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://doe.juntaex.es/pdfs/doe/2025/30o/24040277.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_extremadura')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://doe.juntaex.es/pdfs/doe/2022/1350o/22062210.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_asturias')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/86d9f02b-4c06-4038-b13d-71053ab79d26/Resolucion_aprobacion_convocatoria_alq_general_2023_(2).pdf?table=block&id=217b52f3-795c-8007-ad70-ebed3db8999f&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=0FnhJdBar9Qb52Y7ARg-fareUmuOFXicB0u9cICppUA&downloadName=Resolucion_aprobacion_convocatoria+alq+general+2023+%282%29.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_asturias')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://tramita.asturias.es/sta/docs/GetDocumentServlet?CUD=14156033221320777171&HASH_CUD=caa5d0a03c2efc4e60ba3bea6bd6ffa8c4aba76b&APP_CODE=STA',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_aragon')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/d60ddd88-48c3-4725-8d89-7ac1b393f73f/CSVNA5SNNF2J2150XFIL_ORDEN_AYUDAS_AL_ALQUILER_DE_VIVIENDA_PARA_2025_(1).pdf?table=block&id=220b52f3-795c-8089-a8c0-fb275c9751ec&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=jh8VMHdafphZbBTiaBOTThNYr4QUg-5fewFeWh7OL1w&downloadName=CSVNA5SNNF2J2150XFIL+ORDEN+AYUDAS+AL+ALQUILER+DE+VIVIENDA+PARA+2025+%281%29.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_aragon')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/5bafdff5-a1ac-4428-a461-5bfdd7a7db17/CSVND1K5UA4I11L0XFIL_Orden_Convocatoria_BAJ_(2).pdf?table=block&id=215b52f3-795c-8083-8ed2-c22eb08d4bda&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=tZ6TypPk2JvelFkwWegat9tmBMcYq1pRa7POsoCEo68&downloadName=CSVND1K5UA4I11L0XFIL+Orden+Convocatoria+BAJ+%282%29.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_castilla_leon')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/855a2a76-51d1-4315-a88b-3e34dbebb43e/IAPA_2366_3368_BBRR_Orden_MAV_1420_2022_CorreccionyModificacion_(1).pdf?table=block&id=216b52f3-795c-8046-b615-d218e79eea73&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=ccU2a5myZvgQccbZpDVqbqhFCRM76wEdihAXyIeE9kk&downloadName=IAPA_2366_3368_BBRR_Orden_MAV_1420_2022_CorreccionyModificacion+%281%29.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_castilla_leon')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.boe.es/buscar/pdf/2022/BOE-A-2022-802-consolidado.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_castilla_lamancha')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://docm.jccm.es/docm/descargarArchivo.do?ruta=2024/12/12/pdf/2024_9985.pdf&tipo=rutaDocm',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_castilla_lamancha')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://docm.jccm.es/docm/descargarArchivo.do?ruta=2024/12/12/pdf/2024_9985.pdf&tipo=rutaDocm',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_baleares')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://intranet.caib.es/eboibfront/es/2024/11992/691919/orden-36-2024-de-7-de-octubre-del-consejero-de-viv',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_baleares')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/1783b5ff-12ce-4558-969f-8e7ceffe30dd/Document_BDNS_(castella)_(3).pdf?table=block&id=212b52f3-795c-80a5-868d-ea9e7595bf55&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=m0ToKH25gHA-vdl9kG7g84B-wZC_Euz3Y0NR3_s_8-Y&downloadName=Bases.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_andalucia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.juntadeandalucia.es/sites/default/files/inline-files/2024/07/BOJA24-122-00022-46938-01_00303888.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_andalucia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.juntadeandalucia.es/boja/2025/60/BOJA25-060-00003-4287-01_00317890.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_murcia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.borm.es/services/anuncio/ano/2022/numero/6239/pdf?id=813485',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_murcia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.boe.es/buscar/pdf/2022/BOE-A-2022-802-consolidado.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_madrid')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://file.notion.so/f/f/166f02f0-ab61-4827-875b-2f257523cbe0/f28eb9d5-27c8-4806-ae79-2195c0642d5e/BASES.pdf?table=block&id=211b52f3-795c-8067-9e01-e0cbc6cb0dc1&spaceId=166f02f0-ab61-4827-875b-2f257523cbe0&expirationTimestamp=1751500800000&signature=Ipxb_sZmOLrP2FOIol7psmRuaWU47nfTWk8t_hwA7IE&downloadName=BASES.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_madrid')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.comunidad.madrid/sites/default/files/doc/vivienda/baj2024_acuerdo_gobierno_261224.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_cantabria')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://viviendadecantabria.es/documents/4851088/0/Orden_FOM-7-2025_-_Bono_Alquiler_Joven.report.pdf/76e8145d-436c-07de-bd05-40d1868e7179?t=1747646970114',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_galicia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.xunta.gal/dog/Publicados/2024/20240111/AnuncioC3Q2-191223-0003_es.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_galicia')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.xunta.gal/dog/Publicados/2025/20250220/AnuncioC3Q2-070225-0001_es.html',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'baj_pais_vasco')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.euskadi.eus/web01-bopv/es/bopv2/datos/2022/05/2201931a.pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_500_hijo_madrid')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.bocm.es/boletin/CM_Orden_BOCM/2021/12/27/BOCM-20211227-25.PDF',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'ayuda_galicia_tarxeta_benvida')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.xunta.gal/dog/Publicados/2025/20250129/AnuncioG0762-140125-0001_es.html',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'bono_cultural_joven_2025')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://www.boe.es/eli/es/rd/2023/03/21/191/dof/spa/pdf',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_cataluna_mas_36_menos_65')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://dogc.gencat.cat/es/document-del-dogc/index.html?documentId=1006926',
                'orden' => 1,
            ],
            [
                'ayuda_id' => Ayuda::where('slug', 'pav_cataluna_mas_65')->first()->id,
                'texto_boton' => 'Bases',
                'url' => 'https://dogc.gencat.cat/es/document-del-dogc/index.html?documentId=1006926',
                'orden' => 1,
            ],
        ]);

        Question::create([
            'text' => '¿Cuáles fueron tus ingresos el año pasado?',
            'slug' => 'ingresos_year_pasado',
            'type' => 'integer',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $questionnaire3Id = DB::table('questionnaires')->insertGetId([
            'name' => 'Formulario Post Collector trabajado el año pasado',
            'active' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'tipo' => 'collector',
            'redirect_url' => null,
            'slug' => 'post_collector_trabajo_year_pasado',
            'ayuda_id' => null,
        ]);
        DB::table('questionnaire_questions')->insert([
            [
                'questionnaire_id' => $questionnaire3Id,
                'question_id' => Question::where('slug', 'provincia')->first()->id,
                'orden' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $questionnaire3Id,
                'question_id' => Question::where('slug', 'municipio')->first()->id,
                'orden' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $questionnaire3Id,
                'question_id' => Question::where('slug', 'tiene_hijos_o_pronto')->first()->id,
                'orden' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $questionnaire3Id,
                'question_id' => Question::where('slug', 'vives_alquiler')->first()->id,
                'orden' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $questionnaire3Id,
                'question_id' => Question::where('slug', 'quieres_vives_alquiler')->first()->id,
                'orden' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'questionnaire_id' => $questionnaire3Id,
                'question_id' => Question::where('slug', 'ingresos_year_pasado')->first()->id,
                'orden' => 6,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);

        DB::table('question_conditions')->insert([
            [
                'question_id' => Question::where('slug', 'vives_alquiler')->value('id'),
                'questionnaire_id' => Questionnaire::where('slug', 'post_collector_trabajo_year_pasado')->value('id'),
                'condition' => json_encode([0]),
                'next_question_id' => Question::where('slug', 'quieres_vives_alquiler')->value('id'),
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
