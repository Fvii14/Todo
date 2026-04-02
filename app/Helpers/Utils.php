<?php

// namespace App\Helpers;

use App\Services\GcsUploaderService;
/*
class Utils
{
    public static function comprobarPropietario($propietario, $propietario_situacion, $propietario_collector)
    {
        // Comprobar si la respuesta es "sí" a la pregunta de propiedad
        return isset($propietario) && $propietario == '1' && $propietario_situacion !== '4' && $propietario_collector !== '0';
        // Si la respuesta a la pregunta "¿Eres propietario de alguna vivienda?" es sí
        //Tambien comprobamos el valor que ha recogido en collector
        //y no es "4" para la prgunta Siendo propietario ¿Te encuentras en alguna de estas situaciones?
    }
    // Comprobar si la respuesta es "sí" a tener deudas con Hacienda o la Seguridad Social
    //Devulve true si el usuario tiene deudas y false si no las tiene
    public static function deudasHacienda_SS($answers, $deudas)
    {
        // Comprobar si algún conviviente tiene deudas
        //Comprobamos si vive solo o con convivientes
        if ($answers[1] == '0') {
            //✅ Devuelve true si $deudas existe y no es la cadena '0'.
            //❌ Devuelve false si $deudas no está definida o su valor es '0'.
            return isset($deudas) && $deudas !== '0';
        } else {
            return isset($answers[15], $deudas) && $answers[15] == '1' && $deudas !== '0';
            // Si la respuesta a la pregunta "¿Alguno de los que vive en casa tiene deudas con Hacienda o la Seguridad Social?" es sí
            //Tambien comprobamos si el colector ha recogido el valor "1" en deudas
        }
    }

    public static function comprobarPagoRecibos($answers, $comunidadAutonoma)
    {
        // Comprobar si la persona tiene contrato válido
        return isset($answers[13]) && $answers[13] == '1' && $comunidadAutonoma !== 'Región de Murcia';
        // Si la respuesta a "¿Los recibos del alquiler los pagas por transferencia bancaria, Bizum o ingreso?" es sí y no es Región de Murcia
    }

    // Verificación de edad e ingresos
    public static function comprobarIngresosBAJ($num_convivientes, $situacion, $comunidadAutonoma, $dinero, $dinero_convivientes, $grupo_vulnerable)
    {

        $incomeLimit = 25200; // Límite de ingresos
        if ($situacion == '3' || $situacion == '4') {


            if ($comunidadAutonoma == 'Comunidad Valenciana' || $comunidadAutonoma == 'Cantabria') {
                if ($num_convivientes == 2) {
                    $limite = 33600; // Límite de ingresos para 2 convivientes
                } else if ($num_convivientes >= 3) {
                    $limite = 42000; // Límite de ingresos para 3 convivientes
                }
                if ($dinero_convivientes > $limite || $dinero > $incomeLimit) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Andalucía') {
                if ($num_convivientes > 4) {
                    return false; // Debe ser menor o igual a 4
                } else {
                    $limite = 33600; // Límite de ingresos para 3 convivientes
                }
                if ($dinero_convivientes > $limite || $dinero > $incomeLimit) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Navarra') {
                $limite = 30000;
                if ($dinero_convivientes < 3000 || $dinero < 3000) {
                    return false; // Ingresos inferiores al minimo
                }
                if ($num_convivientes > 1 && ($dinero_convivientes > $limite || $dinero > 22000)) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'País Vasco') {
                $limite = 32000;
                if (in_array('1', $grupo_vulnerable) || in_array('2', $grupo_vulnerable)) {
                    $limite = 32000;
                }
                if ($dinero_convivientes > $limite || $dinero > $limite) {
                    return false; // Ingresos superiores al límite
                }
            } else {
                if ($dinero > $incomeLimit || $dinero_convivientes > $incomeLimit) {
                    return false; // Ingresos superiores al límite
                }
            }
        } else {
            //Vive solo
            if ($comunidadAutonoma == 'Navarra') {
                if ($dinero > 22000 || $dinero < 3000) {
                    return false; // Ingresos superiores al maximo o inferior al minimo
                }
            } else if ($comunidadAutonoma == 'País Vasco') {
                $limite_minimo = 3000;
                $limite_maximo = 24500;

                if (in_array('1', $grupo_vulnerable) || in_array('2', $grupo_vulnerable)) {
                    $limite_maximo = 32000;
                }

                if ($dinero < $limite_minimo || $dinero > $limite_maximo) {
                    return false; // Ingresos inferiores al mínimo o superiores al máximo
                }
            } else {
                if ($dinero > $incomeLimit) {
                    return false; // Ingresos superiores al límite
                }
            }
        }
        return true; // Ingresos válidos
    }

    // Mapeo de provincias a comunidades autónomas (según el INE)


    // Función para obtener la comunidad autónoma por el nombre de la provinciaff
    public static function obtenerComunidadAutonoma($provincia)
    {
        $provinciasCCAA = [
            'Álava' => 'País Vasco',
            'Albacete' => 'Castilla-La Mancha',
            'Alicante/Alacant' => 'Comunidad Valenciana',
            'Almería' => 'Andalucía',
            'Asturias' => 'Asturias',
            'Ávila' => 'Castilla y León',
            'Badajoz' => 'Extremadura',
            'Barcelona' => 'Cataluña',
            'Burgos' => 'Castilla y León',
            'Cáceres' => 'Extremadura',
            'Cádiz' => 'Andalucía',
            'Cantabria' => 'Cantabria',
            'Castellón/Castelló' => 'Comunidad Valenciana',
            'Ceuta' => 'Ceuta',
            'Ciudad Real' => 'Castilla-La Mancha',
            'Córdoba' => 'Andalucía',
            'Cuenca' => 'Castilla-La Mancha',
            'Girona/Gerona' => 'Cataluña',
            'Granada' => 'Andalucía',
            'Guadalajara' => 'Castilla-La Mancha',
            'Guipúzcoa/Gipuzkoa' => 'País Vasco',
            'Huelva' => 'Andalucía',
            'Huesca' => 'Aragón',
            'Jaén' => 'Andalucía',
            'La Rioja' => 'La Rioja',
            'Las Palmas' => 'Canarias',
            'León' => 'Castilla y León',
            'Lleida/Segrià' => 'Cataluña',
            'Madrid' => 'Comunidad de Madrid',
            'Málaga' => 'Andalucía',
            'Murcia' => 'Región de Murcia',
            'Navarra' => 'Navarra',
            'Ourense/Orense' => 'Galicia',
            'Palencia' => 'Castilla y León',
            'Pontevedra' => 'Galicia',
            'Salamanca' => 'Castilla y León',
            'Santa Cruz de Tenerife' => 'Canarias',
            'Segovia' => 'Castilla y León',
            'Sevilla' => 'Andalucía',
            'Soria' => 'Castilla y León',
            'Tarragona' => 'Cataluña',
            'Teruel' => 'Aragón',
            'Toledo' => 'Castilla-La Mancha',
            'Valencia/València' => 'Comunidad Valenciana',
            'Valladolid' => 'Castilla y León',
            'Vizcaya/Bizkaia' => 'País Vasco',
            'Zamora' => 'Castilla y León',
            'Zaragoza' => 'Aragón',
            'Ceuta' => 'Ceuta',
            'Melilla' => 'Melilla',
            'Islas Baleares' => 'Islas Baleares'
        ];
        // Normalizar el nombre de la provincia
        $provinciaNormalizada = ucwords(strtolower(trim($provincia)));

        // Buscar la comunidad autónoma correspondiente
        return $provinciasCCAA[$provinciaNormalizada] ?? null;
    }

    // Función para calcular el alquiler total y compararlo con el límite
    //Devuelve 0 si el alquiler supera el limite
    public static function verificarAlquilerBAJ($provincia, $municipio, $garaje, $trastero, $gastosComunidad, $comunidadAutonoma, $habitacion_vivienda, $pago_alquiler)
    {

        // Mapeo de límites para provincias y comunidades autónomas
        $limitesAlquiler = [
            'Comunidad Valenciana' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'Valencia' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Castellón de la Plana' => ['piso_completo' => 800, 'habitacion' => 400],
                    'Afectados por la DANA' => [
                        'Alaquàs' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Albal' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Albalat de la Ribera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alberic' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alborache' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alcàsser' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alcúdia' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Aldaia' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alfafar' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alfarp' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Algemesí' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alginet' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Almussafes' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Alzira' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Aras de los Olmos' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Barxeta' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benagéber' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benaguasil' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benetússer' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benicull de Xúquer' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benifaió' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benimodo' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Benimuslem' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Beniparrell' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Bétera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Bugarra' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Buñol' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Calles' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Camporrobles' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Carlet' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Casinos' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Pedralba' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Castelló' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Castielfabib' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Catadau' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Catarroja' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Caudete de las Fuentes' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Chelva' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Chulilla' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Corbera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Cullera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Dos Aguas' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Énova' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Favara' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Fortaleny' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Fuenterrobles' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Gavarda' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Gestalgar' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Godelleta' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Guadassuar' => ['piso_completo' => 800, 'habitacion' => 400],
                        'La Pobla Llarga' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Lauri' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Llíria' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Llocnou de la Corona' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Llombai' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Loriguilla' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Losa del Obispo' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Manises' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Macastre' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Manuel' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Massalavés' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Massanassa' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Millares' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Mislata' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Montroi' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Montserrat' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Paiporta' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Paterna' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Picanya' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Picassent' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Polinyà de Xúquer' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Quart de Poblet' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Rafelguaraf' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Real' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Requena' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Riba-roja de Túria' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Riola' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Sant Joanet' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Sedaví' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Senyera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Siete Aguas' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Silla' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Sinarcas' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Sollana' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Sot de Chera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Sueca' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Tavernes de la Valldigna' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Titaguas' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Torrent' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Tous' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Tuéjar' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Turís' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Utiel' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Vilamarxant' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Villar del Arzobispo' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Xeraco' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Xirivella' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Yátova' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Carcaixent' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Cheste' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Chera' => ['piso_completo' => 800, 'habitacion' => 400],
                        'Chiva' => ['piso_completo' => 800, 'habitacion' => 400],
                    ],
                    'Alboraya' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Gandía' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Sagunto' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Benicasim' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Burriana' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Villarreal' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Benidorm' => ['piso_completo' => 700, 'habitacion' => 350],
                    'El Campello' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Elche' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Mutxamel' => ['piso_completo' => 700, 'habitacion' => 350],
                    'San Juan de Alicante' => ['piso_completo' => 700, 'habitacion' => 350],
                    'San Vicente del Raspeig' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Resto de municipios' => ['piso_completo' => 650, 'habitacion' => 300]
                ]
            ],
            'Cataluña' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'Ámbito Metropolitano de Barcelona' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Demarcación de Girona' => ['piso_completo' => 750, 'habitacion' => 400],
                    'Demarcación de Lleida' => ['piso_completo' => 600, 'habitacion' => 300],
                    'Demarcación de Tarragona' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Les Terres de l\'Ebre' => ['piso_completo' => 600, 'habitacion' => 300]
                ]
            ],
            'Comunidad de Madrid' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'Ajalvir' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Alcalá de Henares' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Alcobendas' => ['piso_completo' => 900, 'habitacion' => 450],
                    // Otros municipios con excepciones
                ]
            ],
            'Canarias' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'Adeje' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Arico' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Arona' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Candelaria' => ['piso_completo' => 900, 'habitacion' => 450],
                    'El Rosario' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Granadilla de Abona' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Guía de Isora' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Güímar' => ['piso_completo' => 900, 'habitacion' => 450],
                    'La Oliva' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Las Palmas de Gran Canaria' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Mogán' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Pájara' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Puerto de la Cruz' => ['piso_completo' => 900, 'habitacion' => 450],
                    'San Bartolomé' => ['piso_completo' => 900, 'habitacion' => 450],
                    'San Bartolomé de Tirajana' => ['piso_completo' => 900, 'habitacion' => 450],
                    'San Cristóbal de La Laguna' => ['piso_completo' => 900, 'habitacion' => 450],
                    'San Miguel de Abona' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Santa Brígida' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Santa Cruz de Tenerife' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Santiago del Teide' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Tazacorte' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Teguise' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Tías' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Yaiza' => ['piso_completo' => 900, 'habitacion' => 450],
                ]
            ],
            'Región de Murcia' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'default' => ['piso_completo' => 600, 'habitacion' => 300], // Excepción genérica
                    'garaje' => 0.2, // 20% sobre el alquiler si tiene garaje
                    'trastero' => 0.05, // 5% sobre el alquiler si tiene trastero
                    'gastos_comunidad' => 0.02 // 2% sobre el alquiler si tiene gastos de comunidad
                ]
            ],
            'Andalucía' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'Cádiz' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Rota' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Granada' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Alhaurín de la Torre' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Benalmádena' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Estepona' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Fuengirola' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Málaga' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Marbella' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Mijas' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Rincón de la Victoria' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Torremolinos' => ['piso_completo' => 900, 'habitacion' => 450],
                    'Sevilla' => ['piso_completo' => 900, 'habitacion' => 450],
                    // Otros municipios de Andalucía con límites definidos
                ]
            ],
            'Galicia' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    'A Coruña' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Santiago de Compostela' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Vigo' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Ourense' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Pontevedra' => ['piso_completo' => 700, 'habitacion' => 350],
                    'Ames' => ['piso_completo' => 600, 'habitacion' => 300],
                    'Arteixo' => ['piso_completo' => 600, 'habitacion' => 300],
                    'Cambre' => ['piso_completo' => 600, 'habitacion' => 300],
                    'Carballo' => ['piso_completo' => 600, 'habitacion' => 300],
                    'Culleredo' => ['piso_completo' => 600, 'habitacion' => 300],
                    // Otros municipios con límites específicos
                ]
            ],
            'Islas Baleares' => [
                'default' => [
                    'piso_completo' => 900, // Límite general para piso completo (único)
                    'habitacion' => 900 // No se distingue por habitación
                ]
            ],
            'Cantabria' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ],
                'excepciones' => [
                    '1 joven' => ['piso_completo' => 600, 'habitacion' => 300],
                    '2 jóvenes' => ['piso_completo' => 700, 'habitacion' => 350],
                    '3 jóvenes' => ['piso_completo' => 800, 'habitacion' => 400]
                ]
            ],
            'Navarra' => [
                'default' => [
                    'piso_completo' => 700, // Límite general para piso completo
                    'habitacion' => 700 // Límite general para habitación
                ]
            ],
            'País Vasco' => [
                'default' => [
                    'piso_completo' => 675, // Límite general para piso completo
                    'habitacion' => 675 // Límite general para habitación
                ],
                'excepciones' => [
                    'Bilbao' => ['piso_completo' => 800, 'habitacion' => 400],
                    'Donostia-San Sebastián' => ['piso_completo' => 800, 'habitacion' => 400],
                    'Vitoria-Gasteiz' => ['piso_completo' => 800, 'habitacion' => 400]
                ]
            ],
            'Aragón' => [
                'default' => [
                    'piso_completo' => 600, // Límite general para piso completo
                    'habitacion' => 300 // Límite general para habitación
                ]
            ]
        ];

        // Obtener los límites de la comunidad autónoma
        $limites = $limitesAlquiler[$comunidadAutonoma] ?? null;
        if (!$limites) {
            return "Comunidad Autónoma no válida.";
        }

        // Verificar si el municipio pertenece a los afectados por la DANA
        $municipioDANA = $limites['excepciones']['Afectados por la DANA'][$municipio] ?? null;
        if ($municipioDANA) {
            // Si es un municipio afectado por la DANA, usamos su precio
            $limitePisoCompleto = $municipioDANA['piso_completo'];
            $limiteHabitacion = $municipioDANA['habitacion'];
        } else {
            // Si no es un municipio afectado por la DANA, buscamos en el resto de excepciones
            $limiteProvincia = $limites['excepciones'][$provincia] ?? null;
            if ($limiteProvincia) {
                // Si hay excepciones para esta provincia, usamos esos límites
                $limitePisoCompleto = $limiteProvincia['piso_completo'];
                $limiteHabitacion = $limiteProvincia['habitacion'];
            } else {
                // Si no hay excepciones para la provincia, usamos los límites generales
                $limitePisoCompleto = $limites['default']['piso_completo'];
                $limiteHabitacion = $limites['default']['habitacion'];
            }
        }

        // Calcular el alquiler total (considerando posibles adiciones)
        if ($comunidadAutonoma == 'Región de Murcia') {
            if ($habitacion_vivienda == true) { //vivienda
                $pago_alquiler_final = $pago_alquiler - (($pago_alquiler * 0.2 * $garaje) + ($pago_alquiler * 0.05 * $trastero) + ($pago_alquiler * 0.02 * $gastosComunidad));
            } else { //habitacion
                $pago_alquiler_final = $pago_alquiler - (($pago_alquiler * 0.2 * $garaje) + ($pago_alquiler * 0.05 * $trastero) + ($pago_alquiler * 0.02 * $gastosComunidad));
            }
        } else {
            if ($habitacion_vivienda == true) { //vivienda
                //Calculamos el alquiler y descontamos el garaje, trastero y gastos de comunidad
                $pago_alquiler_final = $pago_alquiler - (($pago_alquiler * 0.2 * $garaje) + ($pago_alquiler * 0.05 * $trastero) + ($pago_alquiler * 0.05 * $gastosComunidad));
            } else { //habitacion
                $pago_alquiler_final = $pago_alquiler - (($pago_alquiler * 0.2 * $garaje) + ($pago_alquiler * 0.05 * $trastero) + ($pago_alquiler * 0.05 * $gastosComunidad));
            }
        }


        // Comparar los alquileres con los límites
        if ($pago_alquiler_final > $limitePisoCompleto && $habitacion_vivienda == true) {
            return 0; // El alquiler está fuera de los límites establecidos
        } else if ($pago_alquiler_final > $limiteHabitacion && $habitacion_vivienda == false) {
            return 0; // El alquiler está fuera de los límites establecidos
        }

        return 1; // El alquiler está dentro de los límites establecidos
    }

    //Funcion para verificar si esta empadronado o si se puede empadronar
    //Devuelve 1 si esta empadronado o puede empadronarse
    //Devuelve 0 si no esta empadronado y  no puede empadronarse
    public static function verificarEmpadronamientoBAJ($empadronamiento, $empadronamiento_aviso, $situacion)
    {   /*
        TODO:
            ESTO HAY QUE CAMBIARLO CUANDO SE AÑADA LA PERGUNTA DE POST COLECTOR "tienes pensado irte de alquiler?"
        */ /*
        //No tiene contrato
        if ($situacion == 2) {
            return 1; // Está empadronado
        } else {
            // Comprobar si el empadronamiento es válido
            if ($empadronamiento == 1) {
                return 1; // Está empadronado
            } else {
                // Comprobar si el aviso de empadronamiento es válido
                if ($empadronamiento_aviso == 1) {
                    return 1; // Puede empadronarse
                } else {
                    return 0; // No está empadronado y no puede empadronarse
                }
            }
        }
    }

    // Verificación de edad e ingresos
    public static function comprobarIngresosPAV($num_convivientes, $situacion, $comunidadAutonoma, $dinero, $dinero_convivientes, $grupo_vulnerable, $conviviente_vulnerable)
    {

        $incomeLimit = 25200; // Límite de ingresos
        $limite_minimo = 99999; // Límite mínimo de ingresos

        //NO VIVEN SOLOS
        if ($situacion == '3' || $situacion == '4') {

            if ($comunidadAutonoma == 'Comunidad Valenciana') {

                $limite_minimo = 2520; // Límite de ingresos

                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                }

                if ($dinero_convivientes > $incomeLimit || $dinero > $incomeLimit || $dinero_convivientes < $limite_minimo || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Comunidad de Madrid' || $comunidadAutonoma == 'Aragón' || $comunidadAutonoma == 'Castilla y León' || $comunidadAutonoma == 'Galicia') { //MADRID, ARAGÓN, CASTILLA Y LEÓN, GALICIA
                $limite_minimo = 4200; // Límite de ingresos
                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('5', $grupo_vulnerable)) {
                    $incomeLimit = 0; // Límite de ingresos para grupo vulnerable
                }
                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                }
                if ($dinero_convivientes > $incomeLimit || $dinero > $incomeLimit || $dinero_convivientes < $limite_minimo || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Canarias' || $comunidadAutonoma == 'Asturias' || $comunidadAutonoma == 'Islas Baleares' || $comunidadAutonoma == 'Cantabria' || $comunidadAutonoma == 'Extremadura' || $comunidadAutonoma == 'La Rioja' || $comunidadAutonoma == 'Castilla-La Mancha') { //CANARIAS, ASTURIAS, ISLAS BALEARES, CANTABRIA, EXTREMADURA, LA RIOJA, CASTILLA LA MANCHA
                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                }
                if ($dinero_convivientes > $incomeLimit || $dinero > $incomeLimit || $dinero_convivientes < $limite_minimo || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Región de Murcia') {

                $limite_minimo = 4200; // Límite de ingresos

                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('5', $grupo_vulnerable)) {
                    $incomeLimit = 33600; // Límite de ingresos para grupo vulnerable
                    $incomeLimit = 0; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                } else if (in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 0; // Límite de ingresos para grupo vulnerable
                    $limite_minimo = 0; // Límite de ingresos
                }

                if ($dinero_convivientes > $incomeLimit || $dinero > $incomeLimit || $dinero_convivientes < $limite_minimo || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else {
                // Otros casos
                if ($dinero > $incomeLimit || $dinero_convivientes > $incomeLimit || $dinero_convivientes < $limite_minimo || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            }
        } else {
            //Vive solo
            if ($comunidadAutonoma == 'Comunidad Valenciana') {

                $limite_minimo = 2520; // Límite de ingresos

                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                }

                if ($dinero > $incomeLimit || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Comunidad de Madrid' || $comunidadAutonoma == 'Aragón' || $comunidadAutonoma == 'Castilla y León' || $comunidadAutonoma == 'Galicia') { //MADRID, ARAGÓN, CASTILLA Y LEÓN, GALICIA
                $limite_minimo = 4200; // Límite de ingresos
                if (in_array('1', $grupo_vulnerable) || in_array('2', $grupo_vulnerable) || in_array('5', $grupo_vulnerable)) {
                    $incomeLimit = 0; // Límite de ingresos para grupo vulnerable
                }
                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                }
                if ($dinero > $incomeLimit || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Canarias' || $comunidadAutonoma == 'Asturias' || $comunidadAutonoma == 'Islas Baleares' || $comunidadAutonoma == 'Cantabria' || $comunidadAutonoma == 'Extremadura' || $comunidadAutonoma == 'La Rioja' || $comunidadAutonoma == 'Castilla-La Mancha') { //CANARIAS, ASTURIAS, ISLAS BALEARES, CANTABRIA, EXTREMADURA, LA RIOJA, CASTILLA LA MANCHA
                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                }
                if ($dinero > $incomeLimit || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else if ($comunidadAutonoma == 'Región de Murcia') {

                $limite_minimo = 4200; // Límite de ingresos

                if (in_array('1', $grupo_vulnerable) || in_array('3', $grupo_vulnerable) || in_array('5', $grupo_vulnerable)) {
                    $incomeLimit = 33.600; // Límite de ingresos para grupo vulnerable
                    $incomeLimit = 0; // Límite de ingresos para grupo vulnerable
                } else if (in_array('2', $grupo_vulnerable) || in_array('4', $grupo_vulnerable)) {
                    $incomeLimit = 42000; // Límite de ingresos para grupo vulnerable
                } else if (in_array('6', $grupo_vulnerable)) {
                    $incomeLimit = 0; // Límite de ingresos para grupo vulnerable
                    $limite_minimo = 0; // Límite de ingresos
                }

                if ($dinero > $incomeLimit || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            } else {
                if ($dinero > $incomeLimit || $dinero > $incomeLimit || $dinero < $limite_minimo || $dinero < $limite_minimo) {
                    return false; // Ingresos superiores al límite
                }
            }
        }
        return true; // Ingresos válidos
    }


    // Función para conseguir el alquiler máximo permitido por la comunidad autónoma
    // Devuelve el alquiler máximo permitido según la comunidad autónoma, municipio y tipo de vivienda
    public static function conseguirAlquilerPAV($comunidadAutonoma, $municipio, $tipo)
    {
        $limites = [
            'Galicia' => [
                // Municipios principales
                'A Coruña' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],
                'Santiago de Compostela' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],
                'Pontevedra' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],
                'Vigo' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],
                'Lugo' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],
                'Ourense' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],
                'Ferrol' => ['general' => 600, 'numerosa' => 720, 'habitacion' => 300],

                // Provincia de A Coruña
                'Ames' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Ares' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Arteixo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'As Pontes de García Rodríguez' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Betanzos' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Boiro' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Cambre' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Carballo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Cee' => ['general' => 550, 'numerosa' => 660],
                'habitacion' => 275,
                'Cedeira' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Culleredo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Fene' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Melide' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Mugardos' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Narón' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Neda' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Noia' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Oleiros' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Ordes' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Oroso' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Padrón' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Pontedeume' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Ribeira' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Sada' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Teo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],

                // Provincia de Lugo
                'Burela' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Cervo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Chantada' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Foz' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Monforte de Lemos' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Ribadeo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Sarria' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Vilalba' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Viveiro' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],

                // Provincia de Ourense
                'Allariz' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'A Rúa' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'O Barco de Valdeorras' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'O Carballiño' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Celanova' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Ribadavia' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Verín' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Xinzo de Limia' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],

                // Provincia de Pontevedra
                'A Estrada' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'A Illa de Arousa' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Baiona' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Bueu' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Cambados' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Cangas' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Gondomar' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Lalín' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Marín' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Moaña' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Mos' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Nigrán' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'O Grove' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'O Porriño' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Poio' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Ponteareas' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Pontecesures' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Redondela' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Sanxenxo' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Tui' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Vilagarcía de Arousa' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
                'Vilanova de Arousa' => ['general' => 550, 'numerosa' => 660, 'habitacion' => 275],
            ],

            'Cantabria' => [
                'default' => ['general' => 700],
            ],
            'Extremadura' => [
                'default' => ['general' => 600, 'habitacion' => 300],
            ],
            'La Rioja' => [
                'default' => ['general' => 600, 'habitacion' => 300],
            ],
            'Castilla y León' => [
                'Burgos' => ['general' => 550, 'numerosa' => 700, 'especial' => 800, 'habitacion' => 275],
                'León' => ['general' => 550, 'numerosa' => 700, 'especial' => 800, 'habitacion' => 275],
                'Salamanca' => ['general' => 550, 'numerosa' => 700, 'especial' => 800, 'habitacion' => 275],
                'Segovia' => ['general' => 550, 'numerosa' => 700, 'especial' => 800, 'habitacion' => 275],
                'Valladolid' => ['general' => 550, 'numerosa' => 700, 'especial' => 800, 'habitacion' => 275],
                'Ávila' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Palencia' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Soria' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Zamora' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Aguilar de Campoo' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Aranda de Duero' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Arroyo de la Encomienda' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Carbajosa de la Sagrada' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'La Cistérniga' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Laguna de Duero' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Medina del Campo' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Miranda de Ebro' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Ponferrada' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Real Sitio de San Ildefonso' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'San Andrés del Rabanedo' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'Santa Marta de Tormes' => ['general' => 500, 'numerosa' => 600, 'especial' => 700, 'habitacion' => 250],
                'resto' => ['general' => 400, 'numerosa' => 550, 'especial' => 650, 'habitacion' => 200],
            ],
        ];

        if (!isset($limites[$comunidadAutonoma])) {
            return null;
        }

        $comunidad = $limites[$comunidadAutonoma];

        // Prioridad: municipio específico → resto → default
        if (isset($comunidad[$municipio])) {
            $data = $comunidad[$municipio];
        } elseif (isset($comunidad['resto'])) {
            $data = $comunidad['resto'];
        } elseif (isset($comunidad['default'])) {
            $data = $comunidad['default'];
        } else {
            return null;
        }

        // Obtener tipo específico o general como fallback
        return $data[$tipo] ?? $data['general'] ?? null;
    }
    // Verificación de alquiler PAV calculado el precio máximo del usuario dependiendo de los extras del contrato
    // Devuelve 1 si el alquiler es válido, 0 si no lo es
    public static function verificarAlquilerPAV($comunidadAutonoma, $provincia, $municipio, $grupo_vulnerable, $garaje, $trastero, $gastosComunidad, $habitacion_vivienda, $pago_alquiler)
    {
        $tipo = 'general';
        if ($habitacion_vivienda == false) {
            $tipo = 'habitacion';
        } else {
            if (in_array($grupo_vulnerable, ['1', '2'])) {
                $tipo = 'numerosa';
            } elseif (in_array($grupo_vulnerable, ['3', '4'])) {
                $tipo = 'especial';
            }
        }


        $limite_maximo = self::conseguirAlquilerPAV($comunidadAutonoma, $municipio, $tipo);
        if (!$limite_maximo) {
            $limite_maximo = self::conseguirAlquilerPAV($comunidadAutonoma, $provincia, $tipo);
        }

        if (!$limite_maximo) {
            $limite_maximo = self::conseguirAlquilerPAV($comunidadAutonoma, 'resto', $tipo);
        }

        if (!$limite_maximo) {
            return 0;
        }

        // Cálculo de deducciones
        $descuento_garaje = $pago_alquiler * 0.2 * $garaje;
        $descuento_trastero = $pago_alquiler * 0.05 * $trastero;
        $descuento_gastos = $pago_alquiler * 0.05 * $gastosComunidad;

        $pago_alquiler_final = $pago_alquiler - ($descuento_garaje + $descuento_trastero + $descuento_gastos);

        if ($pago_alquiler_final > $limite_maximo) {
            return 0;
        }

        return 1;
    }
}
*/

use Illuminate\Support\Facades\Log;

if (! function_exists('gcs_url_or_null')) {
    function gcs_url_or_null(string $path, int $minutes = 10): ?string
    {
        try {
            $gcs = app(GcsUploaderService::class);

            return $gcs->getTemporaryUrl($path, $minutes);
        } catch (\Throwable $e) {
            Log::warning("No se pudo generar URL temporal para '{$path}': {$e->getMessage()}");

            return null;
        }
    }
    /* PARA VISTAS
     @if ($url = gcs_url_or_null($userDocument->file_path))
            <a href="{{ $url }}" target="_blank">{{ $userDocument->file_name }}</a>
        @else
            <span>No se pudo generar el enlace</span>
        @endif



        PARA CONTOLADORES

        $url = gcs_url_or_null($path);

 */
}
