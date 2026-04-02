<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\Contratacion;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitialUsersSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/usuarios_cat_julio_2025.csv');
        $handle = fopen($file, 'r');

        if (! $handle) {
            throw new \Exception('No se pudo abrir el archivo CSV.');
        }

        // Leer encabezado y limpiarlo
        $headers = fgetcsv($handle);
        $headers = array_map(function ($header) {
            $header = preg_replace('/\x{FEFF}/u', '', $header);

            return trim(preg_replace('/\s+/', ' ', $header));
        }, $headers);

        // Cachear preguntas y ayudas por slug
        $questionsBySlug = Question::all()->keyBy('slug');
        $ayudasBySlug = Ayuda::all()->keyBy('slug');

        // Emails ya en base de datos
        $existingEmails = DB::table('users')->pluck('email')->map(fn ($e) => strtolower($e))->flip()->all();
        $seenEmails = [];

        $now = now();
        $passwordHash = Hash::make('hanD73Z1V8XC', ['rounds' => 4]);

        // Mapeo de preguntas
        $questionMap = [
            'telefono' => 'GENÉRICO-Teléfono Solicitante',
            'nombre_completo' => 'GENÉRICO-Solicitante Nombre',
            'comunidad_autonoma' => 'GENÉRICO-CCAA',
            'fecha_nacimiento' => 'GENÉRICO- Solicitante Fecha de nacimiento',
            'edad' => 'GENÉRICO-Edad Solicitante',
            'dni_nie' => 'Doc-Datos DNI/NIE solicitante',
            'estado_civil' => 'Estado civil',
            'esta_trabajando' => 'Situación laboral',
            'personas-vivienda' => 'Num Convivientes',
            'iban' => 'Numero de cuenta',
            'tipo_via' => 'TipoVia',
            'nombre_via' => 'NombreVia',
            'numero_domicilio' => 'NumVia',
            'bloque' => 'Bloque',
            'portal' => 'Portal',
            'escalera' => 'Escalera',
            'piso' => 'Piso',
            'puerta' => 'Puerta',
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            $email = trim($data['GENÉRICO-Email solicitante'] ?? '');

            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $email = strtolower($email);
            if (isset($seenEmails[$email]) || isset($existingEmails[$email])) {
                continue;
            }

            $seenEmails[$email] = true;

            // Crear usuario
            $user = User::create([
                'name' => $email,
                'email' => $email,
                'password' => $passwordHash,
            ]);

            // Crear respuestas
            $answers = [];

            foreach ($questionMap as $slug => $csvColumn) {
                $question = $questionsBySlug[$slug] ?? null;
                if (! $question) {
                    continue;
                }

                $value = trim($data[$csvColumn] ?? '');

                // Valor por defecto para tipo_via
                if ($slug === 'tipo_via' && empty($value)) {
                    $value = 'Calle';
                }

                if (! empty($value)) {
                    $answers[] = [
                        'user_id' => $user->id,
                        'question_id' => $question->id,
                        'answer' => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! empty($answers)) {
                Answer::insert($answers);
            }

            $tipoAyuda = strtoupper(trim($data['GENÉRICO-Tipo de ayuda Alquiler'] ?? ''));
            $fechaNacimientoStr = trim($data['GENÉRICO- Solicitante Fecha de nacimiento'] ?? '');

            $ayudaId = null;

            if (! empty($fechaNacimientoStr)) {
                try {
                    $fechaNacimiento = Carbon::createFromFormat('d/m/Y', $fechaNacimientoStr);
                    $edad = $fechaNacimiento->age;

                    if ($tipoAyuda === 'BONO ALQUILER JOVEN') {
                        $ayudaId = $ayudasBySlug['baj_cataluna']->id ?? null;
                    } elseif (in_array($tipoAyuda, ['PROGRAMA ESTATAL VIVIENDA -36', 'PROGRAMA ALQUILER DE VIVIENDA'])) {
                        if ($edad >= 65) {
                            $ayudaId = $ayudasBySlug['pav_cataluna_mas_65']->id ?? null;
                        } elseif ($edad > 36) {
                            $ayudaId = $ayudasBySlug['pav_cataluna_mas_36_menos_65']->id ?? null;
                        } else {
                            $ayudaId = $ayudasBySlug['pav_cataluna_menos_36']->id ?? null;
                        }
                    } else {
                        $ayudaId = $ayudasBySlug['alquiler']->id ?? null;
                    }
                } catch (\Exception $e) {
                }
            }

            if ($ayudaId) {
                Contratacion::create([
                    'user_id' => $user->id,
                    'estado' => 'documentacion',
                    'fase' => 'solicitud',
                    'ayuda_id' => $ayudaId,
                ]);
            }
        }

        fclose($handle);
    }
}
