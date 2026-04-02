<?php

// database/migrations/xxxx_xx_xx_xxxxxx_insert_questions_for_alquiler_form.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertQuestionsForAlquilerForm extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('integer_with_range')->nullable()->default(0);
        });
        DB::table('questions')->insert([
            // Pregunta 1: ¿Cuál es tu situación de alquiler?
            [
                'slug' => 'situacion-alquiler',
                'text' => '¿Cuál es tu situación de alquiler?',
                'sub_text' => null,
                'type' => 'select',
                'options' => json_encode([
                    'Vivo de alquiler en una vivienda completa y vivo sola/o.',
                    'Tengo un contrato por habitación.',
                    'Todavía no tengo contrato de alquiler firmado.',
                    'Vivo de alquiler en una vivienda y todas las personas forman parte del contrato.',
                    'Vivo de alquiler en una vivienda completa, pero los demás convivientes NO forman parte del contrato.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 2: ¿El contrato que has firmado es por un periodo de 12 meses o más?
            [
                'slug' => 'contrato-12-meses',
                'text' => '¿El contrato que has firmado es por un periodo de 12 meses o más?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 3: Sabías que puedes pedir la ayuda y cuando te la concedan presentar un contrato de alquiler→Teniendo en cuenta lo anterior...¿quieres pedir la ayuda?
            [
                'slug' => 'quieres-pedir-ayuda',
                'text' => '¿Sabías que puedes pedir la ayuda y cuando te la concedan presentar un contrato de alquiler?Teniendo en cuenta lo anterior...¿quieres pedir la ayuda?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 4: ¿Actualmente esta es la dirección donde vives?
            [
                'slug' => 'direccion-actual',
                'text' => 'Dirección donde vives actualmente',
                'sub_text' => 'Si no es la direccion donde vives actualmente, instroduce la dirección donde vives actualmente.',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => 17,
                'integer_with_range' => false,
            ],
            // Pregunta 5: Contándote a ti, ¿cuántas personas viven en la vivienda?
            [
                'slug' => 'personas-vivienda',
                'text' => 'Contándote a ti, ¿cuántas personas viven en la vivienda?',
                'sub_text' => null,
                'type' => 'integer',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => 18,
                'integer_with_range' => false,
            ],
            // Pregunta 6: ¿Todos los que conviven en casa tienen menos de 36 años?
            [
                'slug' => 'convivientes-menores-36',
                'text' => '¿Todos los que conviven en casa tienen menos de 36 años?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 7: ¿Todas las personas que conviven contigo tienen entre 23 y 33 años (ambos inclusive)?---NAVARRA
            [
                'slug' => 'convivientes-mayores-23-menores-33-navarra',
                'text' => '¿Todas las personas que conviven contigo tienen entre 23 y 33 años (ambos inclusive)?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 8: ¿Perteneces a alguno de estos grupos considerados vulnerables?
            [
                'slug' => 'grupo-vulnerable',
                'text' => '¿Perteneces tú o en su caso algún miembro de la Unidad de Convivencia a alguno de estos grupos considerados vulnerables?',
                'sub_text' => 'Es importantísimo que leas TODAS las opciones de la siguiente pregunta, porque puede decidir que te den la ayuda.',
                'type' => 'select',
                'options' => json_encode([
                    'Ninguna de las anteriores',
                    'Familia numerosa, monoparental, persona con discapacidad ±33%',
                    'Víctima de violencia de género, terrorismo, riesgo de exclusión social, joven extutelado, exconvicto/a',
                    'Toda la unidad de convivencia está desempleada y hayan agotado las prestaciones',
                    'Personas que vivan solas cuyos ingresos proceden de subsidios y no superen anualmente los 8.106,28€',
                    'Desahucio, ejecución hipotecaria o dación en pago de tu vivienda, en los últimos cinco años, o afectado/a por situación catastrófica',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 9: ¿Cuál de estas situaciones se aplica a usted o a algún miembro de su unidad de convivencia?
            [
                'slug' => 'familia-vulnerable',
                'text' => '¿Cuál de estas situaciones se aplica a usted o a algún miembro de su unidad de convivencia?',
                'sub_text' => null,
                'type' => 'multiple',
                'options' => json_encode([
                    'Familia numerosa',
                    'Familia monoparental',
                    'Familia numerosa especial',
                    'Familia monoparental especial',
                    'Persona con discapacidad reconocida inferior o igual al 33%',
                    'Persona con discapacidad reconocida superior al 33%',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 10: Indica el motivo concreto por el que te encuentras en esta situación especial:
            [
                'slug' => 'situacion-especial',
                'text' => 'Indica el motivo concreto por el que te encuentras en esta situación:',
                'sub_text' => null,
                'type' => 'select',
                'options' => json_encode([
                    'He sido desahuciado/a de mi vivienda habitual',
                    'Perdí mi vivienda por una ejecución hipotecaria o porque la entregué al banco en los últimos cinco años.',
                    'He sido afectado/a por una situación catastrófica (inundación, incendio, terremoto, etc.)',
                    'Ninguna de las anteriores',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 11: ¿Cuál de las siguientes situaciones aplica a ti o a alguien con quien convives?
            [
                'slug' => 'situacion-especial-2',
                'text' => '¿Cuál de las siguientes situaciones aplica a ti o a alguien con quien convives?',
                'sub_text' => null,
                'type' => 'multiple',
                'options' => json_encode([
                    'He sido víctima de violencia de género',
                    'He sido víctima de terrorismo',
                    'Estoy en riesgo de exclusión social',
                    'Soy joven extutelado/a',
                    'He estado en prisión (exconvicto/a)',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],

            // Pregunta 12: ¿Eres propietario de alguna vivienda?
            [
                'slug' => 'propietario-vivienda',
                'text' => '¿Tienes propiedades a tu nombre?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 13: Siendo propietario ¿Te encuentras en alguna de estas situaciones?
            [
                'slug' => 'situaciones-propietario',
                'text' => 'Siendo propietario ¿Te encuentras en alguna de estas situaciones?',
                'sub_text' => null,
                'type' => 'select',
                'options' => json_encode([
                    'Separación o divorcio',
                    'Propietario por herencia de una parte de la casa',
                    'Propiedad inaccesible por discapacidad tuya o de algún miembro de tu unidad de convivencia',
                    'No puedes acceder a casa por cualquier causa ajena a tu voluntad',
                    'Ninguna de las anteriores',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 14: ¿Alguno de los que vive en casa es propietario de alguna vivienda?
            [
                'slug' => 'convivientes-propietario',
                'text' => '¿Alguno de los que vive en casa es propietario de alguna vivienda?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 15: Siendo su conviviente propietario ¿Se encuentra en alguna de estas situaciones?
            [
                'slug' => 'situaciones-conviviente-propietario',
                'text' => 'Siendo su conviviente propietario ¿Se encuentra en alguna de estas situaciones?',
                'sub_text' => null,
                'type' => 'select',
                'options' => json_encode([
                    'Separación o divorcio',
                    'Propietario por herencia de una parte de la casa',
                    'Propiedad inaccesible por discapacidad tuya o de algún miembro de tu unidad de convivencia',
                    'No puedes acceder a casa por cualquier causa ajena a tu voluntad',
                    'Ninguna de las anteriores',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 16: ¿Tú o en caso de tener convivientes… ¿Tenéis algún vínculo familiar o laboral con el casero?
            [
                'slug' => 'vinculo-casero-general',
                'text' => 'Tú o, en caso de tener convivientes… ¿Tenéis algún vínculo familiar o laboral con el casero?',
                'sub_text' => 'Se considera vínculo familiar si el casero es tu padre, madre, hijo/a, abuelo/a, nieto/a o hermano/a. También hay vínculo laboral si es tu jefe o socio.',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 17: !!ATENTO!! Acabas de responder que "el casero es familiar o socio en un negocio que tenéis juntos", ¿es correcta esa respuesta?
            [
                'slug' => 'casero-socio',
                'text' => '¡¡ATENTO!! Acabas de responder que "el casero es familiar o socio en un negocio que tenéis juntos", ¿es correcta esa respuesta?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 18: ¿Los recibos del alquiler los pagas por transferencia bancaria, Bizum o ingreso?
            [
                'slug' => 'recibo-alquiler',
                'text' => '¿Los recibos del alquiler los pagas por transferencia bancaria, Bizum o ingreso?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 19: Si hablases con tu casero... ¿Podrías empezar a pagar los recibos por transferencia bancaria, Bizum o ingreso?
            [
                'slug' => 'pago-recibos',
                'text' => 'Si hablases con tu casero... ¿Podrías empezar a pagar los recibos por transferencia bancaria, Bizum o ingreso?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 20: ¿Alguno de los que vive en casa tiene deudas con Hacienda o la Seguridad Social?
            [
                'slug' => 'deudas-hacienda',
                'text' => '¿Alguno de los que vive en casa tiene deudas con Hacienda o la Seguridad Social?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 21: !!ATENTO!! Acabas de responder que alguno de los convivientes no esta al corriente de pagos con Hacienda o la Seguridad Social. ¿Es correcto que alguno tiene deudas con Hacienda o la Seguridad Social?
            [
                'slug' => 'deudas-hacienda-convivientes',
                'text' => '¡¡ATENTO!! Acabas de responder que alguno de los convivientes no esta al corriente de pagos con Hacienda o la Seguridad Social. ¿Es correcto que alguno tiene deudas con Hacienda o la Seguridad Social?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 22: ¿Tienen TODOS los que viven en casa DNI o NIE?
            [
                'slug' => 'dni-nie-general',
                'text' => '¿Tienen TODOS los que viven en casa DNI o NIE?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 23: ¿Tienen TODOS los que viven en casa DNI, NIE o pasaporte?---CATALUÑA
            [
                'slug' => 'dni-nie-pasaporte-catalunya',
                'text' => '¿Tienen TODOS los que viven en casa DNI, NIE o pasaporte?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 24: !!ATENTO!! Acabas de responder que algún conviviente no tiene DNI o NIE. ¿Tienen TODOS los que viven en casa DNI o NIE?
            [
                'slug' => 'dni-nie-convivientes',
                'text' => '¡¡ATENTO!! Acabas de responder que algún conviviente no tiene DNI o NIE. ¿Tienen TODOS los que viven en casa DNI o NIE?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 25: !!ATENTO!! Acabas de responder que algún conviviente no tiene DNI, NIE o pasaporte. ¿Tienen TODOS los que viven en casa DNI, NIE o pasaporte?
            [
                'slug' => 'dni-nie-pasaporte-convivientes-catalunya',
                'text' => '¡¡ATENTO!! Acabas de responder que algún conviviente no tiene DNI, NIE o pasaporte. ¿Tienen TODOS los que viven en casa DNI, NIE o pasaporte?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 26: ¿Estás empadronado en la casa en la que vives de alquiler?
            [
                'slug' => 'empadronado-casa-general',
                'text' => '¿Estás empadronado en la casa en la que vives de alquiler?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 27: !!ATENTO!! Empadronarse es uno de los requisitos necesarios para poder solicitar la ayuda… ¿Podrías empadronarte en la casa en la que vives de alquiler?
            [
                'slug' => 'empadronarse-podrias',
                'text' => '¡¡ATENTO!! Empadronarse es uno de los requisitos necesarios para poder solicitar la ayuda… ¿Podrías empadronarte en la casa en la que vives de alquiler?',
                'sub_text' => null,
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],

            // Pregunta 28: ¿Cuánto ganasteis en total en 2023 sumando a todas las personas que convivís en casa?
            [
                'slug' => 'ganancia-total',
                'text' => 'Sumando los ingresos de todos los que convives en casa ¿Cuánto dinero ganasteis el último año?',
                'sub_text' => 'Ejemplo: tú, tu pareja, tu compañero de piso, tus hijos mayores...',
                'type' => 'integer',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => 10,
                'integer_with_range' => true,
            ],
            // Pregunta 29: ¿Alguno de tus convivientes declaró ingresos superiores a 25.200 € en la última declaración de la renta?
            [
                'slug' => 'ganancia-mayor-25200',
                'text' => '¿Alguien de los que vive contigo ganó más de 25.200 € el último año?',
                'sub_text' => 'Responde SÍ solo si alguien pasó ese límite',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
            // Pregunta 30: ¿Alguno de tus convivientes declaró ingresos superiores a 25.200 € en la última declaración de la renta?
            [
                'slug' => 'aviso-ganancia-mayor-25200',
                'text' => '¡¡ATENTO!! ¿Estás seguro de que alguien en casa ganó más de 25.200 €?',
                'sub_text' => 'Es para confirmar que no te has equivocado al responder antes',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],

            // Pregunta 31: ¿Cuánto pagas al mes de alquiler?
            [
                'slug' => 'pago-mensual-alquiler-generico',
                'text' => '¿Cuánto pagas al mes de alquiler?',
                'sub_text' => null,
                'type' => 'integer',
                'options' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => 15,
                'integer_with_range' => true,
            ],
            // Pregunta 32: ¿Tu contrato incluye... (puedes seleccionar más de una opción)
            [
                'slug' => 'opciones-contrato',
                'text' => '¿Tu contrato incluye... (puedes seleccionar más de una opción)',
                'sub_text' => null,
                'type' => 'multiple',
                'options' => json_encode([
                    'Garaje',
                    'Trastero',
                    'Gastos de comunidad',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'regex_id' => null,
                'integer_with_range' => false,
            ],
        ]);
    }

    public function down()
    {
        // Eliminar las preguntas si es necesario
        DB::table('questions')->whereIn('slug', [
            'situacion-alquiler',
            'contrato-12-meses',
            'quieres-pedir-ayuda',
            'direccion-actual',
            'personas-vivienda',
            'convivientes-menores-36',
            'convivientes-mayores-23-menores-33-navarra',
            'grupo-vulnerable',
            'familia-vulnerable',
            'situacion-especial',
            'situacion-especial-2',
            'propietario-vivienda',
            'situaciones-propietario',
            'convivientes-propietario',
            'situaciones-conviviente-propietario',
            'vinculo-casero-general',
            'casero-socio',
            'recibo-alquiler',
            'pago-recibos',
            'deudas-hacienda',
            'deudas-hacienda-convivientes',
            'dni-nie-general',
            'dni-nie-pasaporte-catalunya',
            'dni-nie-convivientes',
            'dni-nie-pasaporte-convivientes-catalunya',
            'empadronado-casa-general',
            'empadronarse-podrias',
            'ganancia-total',
            'ganancia-mayor-25200',
            'aviso-ganancia-mayor-25200',
            'pago-mensual-alquiler-generico',
            'opciones-contrato',
        ])->delete();
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('integer_with_range');
        });
    }
}
