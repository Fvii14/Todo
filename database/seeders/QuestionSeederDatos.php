<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeederDatos extends Seeder
{
    public function run()
    {
        DB::table('questions')->insert([
            [
                'slug' => 'primer_apellido',
                'text' => 'Primer apellido',
                'sub_text' => 'Inserta aquí el primer apellido',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 28,
                'exclude_none_option' => false,

            ], // 173X
            [
                'slug' => 'segundo_apellido',
                'text' => 'Segundo apellido',
                'sub_text' => 'Inserta aquí el primer apellido',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 28,
                'exclude_none_option' => false,

            ], // 174X
            [
                'slug' => 'porcentaje_discapacidad',
                'text' => '¿Qué porcentaje de discapacidad tienes reconocido oficialmente?',
                'sub_text' => 'Indica el procentaje desde 0 hasta 100',
                'type' => 'integer',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 29,
                'exclude_none_option' => false,

            ], // 175X
            [
                'slug' => 'movilidad_reducida',
                'text' => '¿Tienes reconocida una situación de movilidad reducida?',
                'sub_text' => '',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,

            ], // 176X
            [
                'slug' => 'tienes_id_electronica',
                'text' => '¿Tienes alguna forma de identificación electrónica válida?',
                'sub_text' => 'Selecciona una o varias opciones',
                'type' => 'multiple',
                'options' => json_encode(['Cl@ve PIN', 'Certificado digital']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => true,

            ], // 177X
            [
                'slug' => 'tienes_id_electronica_catalunya',
                'text' => '¿Tienes alguna forma de identificación electrónica válida?',
                'sub_text' => 'Selecciona una o varias opciones',
                'type' => 'multiple',
                'options' => json_encode(['Cl@ve PIN', 'Certificado digital', 'idCAT']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => true,

            ], // 178X
            [
                'slug' => 'mayor_14_anyos',
                'text' => '¿Es mayor de 14 años?',
                'sub_text' => 'Si es mauyor de 14 años, debe tener DNI o NIE',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,

            ], // 179
            [
                'slug' => 'solo_nombre',
                'text' => 'Nombre',
                'sub_text' => 'Inserta solo el nombre, sin apellidos',
                'type' => 'string',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => 28,
                'exclude_none_option' => false,

            ], // 180X
            [
                'slug' => 'tipo_documento_identidad_catalunya',
                'text' => 'Tipo de documento de identidad',
                'sub_text' => 'Selecciona el tipo de documento que utilizas como identificación',
                'type' => 'select',
                'options' => json_encode([
                    'DNI',
                    'NIE',
                    'Pasaporte',
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ], // 181X
            [
                'slug' => 'ingresos-conviviente',
                'text' => '¿Tuvo algún ingreso en el año pasado?',
                'sub_text' => 'Se considera cualquier ingreso, ya sea por trabajo, pensión, ayudas, etc.',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ], // 182
            [
                'slug' => 'cuantos-ingresos-conviviente',
                'text' => '¿Cuantos ingresos tuvo el conviviente?',
                'sub_text' => 'Indica el número de ingresos que tuvo el conviviente en el año pasado',
                'type' => 'integer',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ], // 183
            [
                'slug' => 'solicitante-ha-hecho-renta',
                'text' => '¿Realizaste la declaración de la renta el año pasado?',
                'sub_text' => 'Indica si el solicitante realizó la declaración de la renta el año pasado',
                'type' => 'boolean',
                'options' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'regex_id' => null,
                'exclude_none_option' => false,
            ], // 184
        ]);
    }
}
