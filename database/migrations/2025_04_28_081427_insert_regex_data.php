<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertRegexData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('regex')->insert([
            ['name' => 'validar nombre', 'pattern' => '^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]{6,}$', 'error_message' => 'El nombre completo debe tener al menos 6 letras y solo puede incluir letras, tildes y espacios.'], // 1
            ['name' => 'validar teléfono', 'pattern' => '^(\+34|0034|34)?[6-9]\d{8}$', 'error_message' => 'Introduce un número de teléfono válido en España.'], // 2
            ['name' => 'validar correo electrónico', 'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$', 'error_message' => 'Introduce una dirección de correo electrónico válida.'],
            ['name' => 'validar fecha', 'pattern' => '^\d{4}-\d{2}-\d{2}$', 'error_message' => 'La fecha debe tener el formato AAAA-MM-DD.'],
            ['name' => 'validar tarjeta de crédito', 'pattern' => '^\d{4}-\d{4}-\d{4}-\d{4}$', 'error_message' => 'Introduce un número de tarjeta válido en formato XXXX-XXXX-XXXX-XXXX.'],
            ['name' => 'validar código postal', 'pattern' => '^\d{5}$', 'error_message' => 'El código postal debe tener 5 dígitos.'],
            ['name' => 'validar contraseña fuerte', 'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$', 'error_message' => 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, un número y un símbolo.'],
            ['name' => 'validar URL', 'pattern' => '^(https?:\/\/)?([a-z0-9-]+\.)+[a-z0-9]{2,6}(\/[a-zA-Z0-9#]+\/?)*$', 'error_message' => 'La URL introducida no es válida.'],
            ['name' => 'validar número entero', 'pattern' => '^\d+$', 'error_message' => 'Solo se permiten números enteros.'],
            ['name' => 'validar número con decimales', 'pattern' => '^\d+(\.\d{1,2})?$', 'error_message' => 'Solo se permiten números con hasta 2 decimales.'],
            ['name' => 'validar DNI', 'pattern' => '^\d{8}[A-Za-z]$', 'error_message' => 'El DNI debe tener 8 números seguidos de una letra.'],
            ['name' => 'validar NIE', 'pattern' => '^[X|Y|Z]\d{7}[A-Za-z]$', 'error_message' => 'El NIE debe comenzar con X, Y o Z, seguido de 7 números y una letra.'],
            ['name' => 'validar IBAN', 'pattern' => '^[A-Z]{2}\d{2}[A-Z0-9]{4}\d{7}[A-Z0-9]{0,16}$', 'error_message' => 'El IBAN introducido no es válido.'],
            ['name' => 'validar DNI o NIE', 'pattern' => '^(?:\d{8}|[XYZ]\d{7})[A-Za-z]$', 'error_message' => 'El valor debe ser un DNI o NIE válido.'],
            ['name' => 'validar precio de alquiler', 'pattern' => '^\d+(\.\d{1,2})?$', 'error_message' => 'Introduce un precio válido. Solo se permiten números positivos con hasta 2 decimales.'],
            ['name' => 'validar NIF', 'pattern' => '^\d{8}[A-HJ-NP-TV-Z]$', 'error_message' => 'El NIF debe tener 8 números seguidos de una letra válida.'],
            ['name' => 'validar domicilio', 'pattern' => '^[a-zA-ZÀ-ÿ0-9ºª°.,\\-\\/\\s]{5,100}$', 'error_message' => 'La dirección contiene caracteres no válidos o es demasiado corta.'],
            ['name' => 'número de convivientes', 'pattern' => '^(?:[2-9]|1[0])$', 'error_message' => 'Debe indicar un número de convivientes entre 2 y 10.'],
            ['name' => 'número entero mayor de 0', 'pattern' => '^[1-9][0-9]*$', 'error_message' => 'Debe indicar un número entero mayor que 0.'],
            ['name' => 'número de soporte del documento de identidad', 'pattern' => '^[A-Z]{1}[0-9]{8}$', 'error_message' => 'Debe indicar letra mayúscula seguida de 8 dígitos.'], // 20
            ['name' => 'número de convivientes-2', 'pattern' => '^(?:[1-9]|1[0])$', 'error_message' => 'Debe indicar un número de convivientes entre 1 y 10.'], // 21
            ['name' => 'mayor edad', 'pattern' => '^(1[89]|[2-9][0-9]|1[01][0-9]|120)$', 'error_message' => 'Debe ser mayor de edad'], // 22

            ['name' => 'validar calle', 'pattern' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\-]+$', 'error_message' => 'La calle debe tener solo letras, números, espacios, guiones y puntos.'], // 23
            ['name' => 'validar número de la calle', 'pattern' => '^\d+([A-Za-z])?$', 'error_message' => 'El número de la calle debe ser un número y, opcionalmente, puede tener una letra.'], // 24
            ['name' => 'validar bloque/portal/escalera', 'pattern' => '^[a-zA-Z0-9\-]+$', 'error_message' => 'El bloque, portal o escalera debe contener letras, números y guiones.'], // 25
            ['name' => 'validar piso', 'pattern' => '^\d+[A-Za-z]?$', 'error_message' => 'El piso debe contener números y, opcionalmente, una letra.'], // 26
            ['name' => 'validar puerta', 'pattern' => '^\\d+[A-Za-z]?|(DERECHA|DCHA|DERECH|dcha|IZQUIERDA|IZQ|izquierda|izq|derecha|izq|CENTRO|centro|CENTRO-Izq|Izq-Dcha|DERECHA-Dcha|izquierda-Dcha|\\d+)$', 'error_message' => 'La puerta debe contener números y, opcionalmente, una letra, o una de las siguientes palabras: Derecha, Dcha, IZQUIERDA, IZQ, Centro, Centro-Izq, Izq-Dcha, etc., o simplemente un número.'], // 27
            ['name' => 'validar nombe o apellido', 'pattern' => "^[A-Za-zÁÉÍÓÚáéíóúÑñüÜ'´\\- ]{2,50}$", 'error_message' => 'Introduce entre 2 y 50 letras. Puedes usar tildes, espacios y guiones.'], // 28
            ['name' => 'validar porcentaje discapacidad', 'pattern' => '^(100|[1-9]?[0-9])$', 'error_message' => 'Introduce un porcentaje válido entre 0 y 100 (sin decimales).'], // 29

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('regex')->truncate(); // Elimina todos los registros insertados si la migración se deshace
    }
}
