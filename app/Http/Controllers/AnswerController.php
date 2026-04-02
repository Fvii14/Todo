<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Question;
use App\Models\UserDocument;
use App\Services\GcsUploaderService;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnswerController extends Controller
{
    public function collector(Request $request)
    {
        $sessionId = $request->input('sessionId');

        if (! $sessionId) {
            Log::error('IframeCollector: No sessionId provided in request.');

            return redirect('/error-page')->with('error', 'Falta el ID de sesión del iframe.');
        }

        $host = env('BANKFLIP_HOST');
        $bearerToken = env('BANKFLIP_API_KEY');

        $user = Auth::user();

        $client = new Client;
        $jsonDocumentContent = [];

        try {
            $documentsUrl = "https://{$host}/session/{$sessionId}/document";
            $response1 = $client->get($documentsUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$bearerToken}",
                ],
            ]);

            $data1 = json_decode($response1->getBody()->getContents(), true);
            $jsonDocuments = collect($data1['data'])->where('extension', 'json');
            $pdfDocuments = collect($data1['data'])->where('extension', 'pdf');

            if ($pdfDocuments->isNotEmpty()) {
                $this->processPdfDocumentsConcurrently($pdfDocuments, $host, $bearerToken, $client, $user);
            }

            if ($jsonDocuments->isNotEmpty()) {
                $jsonDocumentContent = $this->processJsonDocumentsConcurrently($jsonDocuments, $host, $bearerToken, $client);
            } else {
                return response()->json([
                    'success' => true,
                    'redirect' => route('onboarder'),
                    'message' => 'Datos procesados y guardados con éxito.',
                ]);
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            Log::error("IframeCollector: Error de cliente ({$statusCode}) en Bankflip API: ".$responseBody);

            return redirect('/error-page')->with('error', "Error en el servicio externo (código: {$statusCode}).");
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getBody()->getContents();
            Log::error("IframeCollector: Error de servidor ({$statusCode}) en Bankflip API: ".$responseBody);

            return redirect('/error-page')->with('error', "Error en el servidor externo (código: {$statusCode}).");
        } catch (\Exception $e) {
            Log::error('IframeCollector: Error general en los fetchs de Bankflip: '.$e->getMessage());

            return redirect('/error-page')->with('error', 'Ocurrió un error inesperado al obtener los datos.');
        }

        try {
            $this->processAndSaveData($jsonDocumentContent, $user);
        } catch (\Exception $e) {
            Log::error('Error al guardar los datos de Bankflip a nuestra BD: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'redirect' => route('onboarder'),
            'message' => 'Datos procesados y guardados con éxito.',
        ]);
    }

    private function processPdfDocumentsConcurrently($pdfDocuments, $host, $bearerToken, $client, $user)
    {
        $hardcodedIds = [4, 5];
        $promises = [];
        $documentData = [];

        foreach ($pdfDocuments as $index => $pdfDocument) {
            $idOfPDFDocument = $pdfDocument['id'];
            $contentUrl = "https://{$host}/document/{$idOfPDFDocument}/content";

            $promises[$index] = $client->getAsync($contentUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$bearerToken}",
                ],
            ]);

            $documentData[$index] = [
                'filename' => $pdfDocument['filename'] ?? "document_{$idOfPDFDocument}.pdf",
                'documentId' => $hardcodedIds[$index] ?? 4,
            ];
        }

        $responses = Promise\Utils::settle($promises)->wait();

        foreach ($responses as $index => $response) {
            if ($response['state'] === 'fulfilled') {
                $this->processPdfResponse($response['value'], $documentData[$index], $user);
            } else {
                Log::error("Error descargando PDF {$index}: ".$response['reason']->getMessage());
            }
        }
    }

    private function processPdfResponse($response, $documentData, $user)
    {
        try {
            $contentDisposition = $response->getHeader('Content-Disposition')[0];
            preg_match('/filename="?([^"]+)"?/', $contentDisposition, $matches);
            $filename = $matches[1] ?? $documentData['filename'];

            $fileContent = $response->getBody()->getContents();
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
            file_put_contents($tempFile, $fileContent);

            $file = new UploadedFile(
                $tempFile,
                $filename,
                $response->getHeader('Content-Type')[0],
                null,
                true
            );

            $GCSUploaderService = new GcsUploaderService;
            $fileUrl = $GCSUploaderService->uploadFile($file, 'documentos/usuario_'.$user->id.'/'.$filename);

            UserDocument::create([
                'user_id' => $user->id,
                'document_id' => $documentData['documentId'],
                'file_path' => $fileUrl,
                'file_name' => $filename,
                'file_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'estado' => 'validado',
            ]);

            unlink($tempFile);
        } catch (\Exception $e) {
            Log::error('Error procesando PDF: '.$e->getMessage());
        }
    }

    private function processJsonDocumentsConcurrently($jsonDocuments, $host, $bearerToken, $client)
    {
        $promises = [];
        $documentIds = [];

        foreach ($jsonDocuments as $index => $jsonDocument) {
            $idOfJsonDocument = $jsonDocument['id'];
            $contentUrl = "https://{$host}/document/{$idOfJsonDocument}/content";

            $promises[$index] = $client->getAsync($contentUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$bearerToken}",
                ],
            ]);

            $documentIds[$index] = $idOfJsonDocument;
        }

        $responses = Promise\Utils::settle($promises)->wait();
        $jsonDocumentContent = [];

        foreach ($responses as $index => $response) {
            if ($response['state'] === 'fulfilled') {
                $content = json_decode($response['value']->getBody()->getContents(), true);
                $jsonDocumentContent = array_merge_recursive($jsonDocumentContent, $content);
            } else {
                Log::error("Error descargando JSON {$documentIds[$index]}: ".$response['reason']->getMessage());
            }
        }

        return $jsonDocumentContent;
    }

    private function resolveProvinceMunicipioInDb($municipio, $provincia, $ccaaId = null): array
    {
        if (! $provincia && ! $municipio) {
            return [null, null, null];
        }

        $normalize = function ($s) {
            if ($s === null) {
                return null;
            }
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
            $s = preg_replace('/[^A-Za-z0-9\s]/', '', $s);

            return strtoupper(trim(preg_replace('/\s+/', ' ', $s)));
        };

        $provNorm = $normalize($provincia);
        $munNorm = $normalize($municipio);

        $provinciaNombreFinal = null;
        $provinciaId = null;

        if ($provNorm) {
            $provQuery = Provincia::select('id', 'nombre_provincia');
            if (! is_null($ccaaId)) {
                $provQuery->where('id_ccaa', $ccaaId);
            }
            $provincias = $provQuery->get();
            foreach ($provincias as $p) {
                $pNorm = $normalize($p->nombre_provincia);
                if ($pNorm === $provNorm) {
                    $provinciaNombreFinal = $p->nombre_provincia;
                    $provinciaId = $p->id;
                    break;
                }
            }

            if (! $provinciaId) {
                foreach ($provincias as $p) {
                    $pNorm = $normalize($p->nombre_provincia);
                    if (str_contains($pNorm, $provNorm) || str_contains($provNorm, $pNorm)) {
                        $provinciaNombreFinal = $p->nombre_provincia;
                        $provinciaId = $p->id;
                        break;
                    }
                }
            }
        }

        $municipioNombreFinal = null;
        $matchedMunicipioProvId = null;
        if ($munNorm) {
            $query = Municipio::select('id', 'nombre_municipio', 'provincia_id');
            if ($provinciaId) {
                $query->where('provincia_id', $provinciaId);
            }
            $municipios = $query->get();

            foreach ($municipios as $m) {
                $mNorm = $normalize($m->nombre_municipio);
                if ($mNorm === $munNorm) {
                    $municipioNombreFinal = $m->nombre_municipio;
                    $matchedMunicipioProvId = $m->provincia_id;
                    break;
                }
            }

            if (! $municipioNombreFinal) {
                foreach ($municipios as $m) {
                    $mNorm = $normalize($m->nombre_municipio);
                    if (str_starts_with($mNorm, $munNorm) || str_starts_with($munNorm, $mNorm) || str_contains($mNorm, $munNorm)) {
                        $municipioNombreFinal = $m->nombre_municipio;
                        $matchedMunicipioProvId = $m->provincia_id;
                        break;
                    }
                }
            }
        }

        if (! $provinciaId && $matchedMunicipioProvId) {
            $prov = Provincia::select('id', 'nombre_provincia')->find($matchedMunicipioProvId);
            if ($prov) {
                $provinciaId = $prov->id;
                $provinciaNombreFinal = $prov->nombre_provincia;
            }
        }

        return [$municipioNombreFinal, $provinciaNombreFinal, $provinciaId];
    }

    private function parseProvinceMunicipioFromDireccion(?string $direccion): array
    {
        if (! $direccion || ! is_string($direccion)) {
            return [null, null];
        }

        $text = trim(preg_replace('/\s+/', ' ', $direccion));

        if (! preg_match('/(\d{5})\s+(.*)$/u', $text, $matches)) {
            return [null, null];
        }

        $cp = $matches[1];
        $tail = trim($matches[2]);

        $provinciasDb = Provincia::select('nombre_provincia')->get()->pluck('nombre_provincia')->toArray();

        $normalize = function (string $s): string {
            $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
            $s = preg_replace('/[^A-Za-z0-9\s]/', '', $s);

            return strtoupper(trim(preg_replace('/\s+/', ' ', $s)));
        };

        $tailNorm = $normalize($tail);

        $provincia = null;
        $matched = null;
        $provNormList = array_map(function ($p) use ($normalize) {
            return $normalize($p);
        }, $provinciasDb);
        usort($provNormList, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
        foreach ($provNormList as $prov) {
            if (preg_match('/\b'.preg_quote($prov, '/').'\b$/u', $tailNorm)) {
                $provincia = $prov;
                $matched = $prov;
                break;
            }
        }

        if ($provincia === null) {
            return [null, null];
        }

        $municipioNorm = trim(preg_replace('/\b'.preg_quote($matched, '/').'\b$/u', '', $tailNorm));

        $posProv = mb_strripos($tailNorm, $matched);
        $municipioRaw = trim(mb_substr($tail, 0, $posProv !== false ? $posProv : mb_strlen($tail)));
        $municipioRaw = trim(preg_replace('/[\s,]+$/', '', $municipioRaw));

        $pretty = function (string $s): string {
            $s = strtolower($s);

            return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
        };

        $provinciaPretty = null;
        if ($matched !== null) {
            foreach ($provinciasDb as $provDbName) {
                if ($normalize($provDbName) === $matched) {
                    $provinciaPretty = $provDbName;
                    break;
                }
            }
        }
        if ($provinciaPretty === null && $provincia !== null) {
            $provinciaPretty = $pretty($provincia);
        }
        $municipioPretty = $pretty($municipioRaw);

        return [$municipioPretty, $provinciaPretty];
    }

    private function processAndSaveData($jsonDocumentContent, $user)
    {
        $questionsCache = $this->getQuestionsCache();

        $data = $jsonDocumentContent['rawModel']['data'] ?? [];
        $declarante = $data['datosIdentificativos']['declarante'] ?? [];
        $economicos = $data['datosEconomicos'] ?? [];
        $iban = $data['datosIngresoDevolucion']['iban'] ?? 0;
        $direccionPersonal = $jsonDocumentContent['informacionPersonal']['direccion'] ?? '';
        [$municipioExtraido, $provinciaExtraida] = $this->parseProvinceMunicipioFromDireccion($direccionPersonal);
        $ccaaId = $this->getCcaaIdFromEconomicos($economicos);
        [$municipioNombreFinal, $provinciaNombreFinal, $provinciaIdFinal] = $this->resolveProvinceMunicipioInDb($municipioExtraido, $provinciaExtraida, $ccaaId);
        $fechaNacimiento = $declarante['fechaNacimiento'] ?? $jsonDocumentContent['informacionPersonal']['fechaNacimiento'] ?? '';
        $dni = $declarante['nif'] ?? $jsonDocumentContent['informacionPersonal']['identificacion'] ?? '';
        $nombreFuente = $jsonDocumentContent['informacionPersonal']['nombre'] ?? ($declarante['apellidosNombre'] ?? null);
        $orden = isset($jsonDocumentContent['informacionPersonal']['nombre']) ? 'names_first' : 'surnames_first';
        [$solo_nombre, $primer_apellido, $segundo_apellido] = $this->parseSpanishName($nombreFuente, $orden);

        $situacionData = $this->processSituaciones($jsonDocumentContent);
        $datosEconomicos = $this->calculateEconomicData($economicos);
        $respuestas = $this->prepareAnswers($declarante, $economicos, $situacionData, $datosEconomicos, $questionsCache, $jsonDocumentContent, $iban, $direccionPersonal, $fechaNacimiento, $dni, $provinciaNombreFinal, $municipioNombreFinal, $solo_nombre, $primer_apellido, $segundo_apellido);

        $this->saveAnswersBatch($respuestas, $user->id);
    }

    private function getQuestionsCache()
    {
        return Question::whereIn('slug', [
            'tiene_dni',
            'edad',
            'retribucion_trabajo',
            'base_imponible',
            'dinero_ganado',
            'iban',
            'ha_trabajado_3_meses_ultimos_6_meses',
            'fecha_collector',
            'fecha_formulario_inicial',
            'direccion-actual',
            'fecha_nacimiento',
            'tiene_menos_31_years',
            'eres_padre_madre',
            'tiene_dni',
            'provincia',
            'municipio',
            'solo_nombre',
            'primer_apellido',
            'segundo_apellido',
        ])->pluck('id', 'slug')->toArray();
    }

    private function processSituaciones($jsonDocumentContent)
    {
        $estaTrabajandoActualmente = false;
        $recibePrestacionDesempleo = false;
        $trabajoMinimoTresMeses = false;
        $trabajoYearPasado = false;
        $diasTrabajados = 0;

        $hoy = new DateTime;
        $haceSeisMeses = (clone $hoy)->modify('-6 months');
        $inicioYearPasado = new DateTime(($hoy->format('Y') - 1).'-01-01');
        $finYearPasado = new DateTime(($hoy->format('Y') - 1).'-12-31');

        if (isset($jsonDocumentContent['situaciones']) && is_array($jsonDocumentContent['situaciones'])) {
            $situacionesOrdenadas = $jsonDocumentContent['situaciones'];
            usort($situacionesOrdenadas, function ($a, $b) {
                $fechaA = isset($a['fechaAlta']) ? strtotime($a['fechaAlta']) : 0;
                $fechaB = isset($b['fechaAlta']) ? strtotime($b['fechaAlta']) : 0;

                return $fechaB - $fechaA;
            });

            $ultimaSituacion = reset($situacionesOrdenadas);
            if (
                $ultimaSituacion && isset($ultimaSituacion['fechaAlta']) && ! empty($ultimaSituacion['fechaAlta']) &&
                (! isset($ultimaSituacion['fechaBaja']) || is_null($ultimaSituacion['fechaBaja']) || empty($ultimaSituacion['fechaBaja']))
            ) {
                $estaTrabajandoActualmente = true;
            }

            foreach ($jsonDocumentContent['situaciones'] as $situacion) {
                $inicio = new DateTime($situacion['fechaAlta']);
                $fin = isset($situacion['fechaBaja']) && ! empty($situacion['fechaBaja']) ? new DateTime($situacion['fechaBaja']) : clone $hoy;

                $rangoInicio = $inicio > $haceSeisMeses ? $inicio : $haceSeisMeses;
                $rangoFin = $fin < $hoy ? $fin : $hoy;

                if ($rangoInicio <= $rangoFin) {
                    $dias = $rangoFin->diff($rangoInicio)->days + 1;
                    $diasTrabajados += $dias;
                }

                if ($fin >= $inicioYearPasado && $inicio <= $finYearPasado) {
                    $trabajoYearPasado = true;
                }

                $nombreEmpresa = $situacion['nombreEmpresa'] ?? '';
                $nombreEmpresaUpper = strtoupper($nombreEmpresa);

                $esPrestacion = (str_contains($nombreEmpresaUpper, 'PRESTACION') || str_contains($nombreEmpresaUpper, 'DESEMPLEO') || str_contains($nombreEmpresaUpper, 'PRESTACIÓN'));
                $sinFechaBaja = (! isset($situacion['fechaBaja']) || is_null($situacion['fechaBaja']) || empty($situacion['fechaBaja']));

                if ($esPrestacion && $sinFechaBaja) {
                    $recibePrestacionDesempleo = true;
                    break;
                }
            }
            $trabajoMinimoTresMeses = $diasTrabajados >= 90;
        }

        return [
            'estaTrabajandoActualmente' => $estaTrabajandoActualmente,
            'recibePrestacionDesempleo' => $recibePrestacionDesempleo,
            'trabajoMinimoTresMeses' => $trabajoMinimoTresMeses,
            'trabajoYearPasado' => $trabajoYearPasado,
        ];
    }

    private function calculateEconomicData($economicos)
    {
        $datos = $economicos['titulares'][0]['rendimientoTrabajo'] ?? [];
        $retribucionesDinerarias = (float) ($datos['retribucionesDinerarias'] ?? 0);
        $retribucionesEspecie = (float) ($datos['retribucionesEspecie'] ?? 0);
        $contribucionesPlanes = (float) ($datos['contribucionesEmpresarialesPlanes'] ?? 0);
        $contribucionesSeguros = (float) ($datos['contribucionesEmpresarialesSegurosColectivos'] ?? 0);
        $retribucion_trabajo = $retribucionesDinerarias + $retribucionesEspecie + $contribucionesPlanes + $contribucionesSeguros;

        $baseImponible = $economicos['resultados']['baseImponible'] ?? [];
        $baseGeneral = (float) ($baseImponible['general'] ?? 0);
        $baseAhorro = (float) ($baseImponible['ahorro'] ?? 0);
        $base_imponible = $baseGeneral + $baseAhorro;

        return [
            'retribucion_trabajo' => $retribucion_trabajo,
            'base_imponible' => $base_imponible,
        ];
    }

    private function parseSpanishName(?string $fullName, string $order = 'names_first'): array
    {
        if (! $fullName || ! is_string($fullName)) {
            return [null, null, null];
        }

        $name = trim(preg_replace('/\s+/', ' ', $fullName));

        $particles = ['de', 'del', 'de la', 'de los', 'de las', 'la', 'las', 'los', 'y'];

        $tokens = preg_split('/\s+/', $name);
        if (! $tokens || count($tokens) === 0) {
            return [null, null, null];
        }

        $joinParticlesFrom = function (array $arr, int $startIdx, int $endIdx) use ($particles) {
            $slice = array_slice($arr, $startIdx, $endIdx - $startIdx + 1);
            for ($i = 0; $i < count($slice) - 1; $i++) {
                $w = mb_strtolower($slice[$i], 'UTF-8');
                if (in_array($w, $particles, true)) {
                    $slice[$i + 1] = $slice[$i].' '.$slice[$i + 1];
                    $slice[$i] = null;
                }
            }
            $slice = array_values(array_filter($slice, fn ($x) => $x !== null && $x !== ''));

            return $slice;
        };

        $nombre = null;
        $apellido1 = null;
        $apellido2 = null;

        if ($order === 'surnames_first') {
            $rejoined = $joinParticlesFrom($tokens, 0, count($tokens) - 1);
            $n = count($rejoined);
            if ($n >= 3) {
                $names = [];
                for ($i = $n - 1; $i >= 0 && count($rejoined) - count($names) > 2; $i--) {
                    array_unshift($names, array_pop($rejoined));
                }
                $m = count($rejoined);
                if ($m === 2) {
                    $apellido1 = $rejoined[0];
                    $apellido2 = $rejoined[1];
                } else {
                    $split = (int) floor($m / 2);
                    $apellido1 = trim(implode(' ', array_slice($rejoined, 0, $split)));
                    $apellido2 = trim(implode(' ', array_slice($rejoined, $split)));
                }
                $nombre = trim(implode(' ', $names));
            } elseif ($n === 2) {
                $apellido1 = $rejoined[0];
                $nombre = $rejoined[1];
            } else {
                $nombre = $rejoined[0];
            }
        } else {
            $rejoined = $joinParticlesFrom($tokens, 0, count($tokens) - 1);
            $n = count($rejoined);
            if ($n >= 3) {
                $apellido2 = array_pop($rejoined);
                $apellido1 = array_pop($rejoined);
                $nombre = trim(implode(' ', $rejoined));
            } elseif ($n === 2) {
                $nombre = $rejoined[0];
                $apellido1 = $rejoined[1];
            } else {
                $nombre = $rejoined[0];
            }
        }

        return [
            $nombre ? trim($nombre) : null,
            $apellido1 ? trim($apellido1) : null,
            $apellido2 ? trim($apellido2) : null,
        ];
    }

    private function prepareAnswers($declarante, $economicos, $situacionData, $datosEconomicos, $questionsCache, $jsonDocumentContent, $iban, $direccionPersonal, $fechaNacimiento, $dni, $provinciaNombreFinal = null, $municipioNombreFinal = null, $solo_nombre = null, $primer_apellido = null, $segundo_apellido = null)
    {
        $estadoCivilMap = [
            '1' => 'Soltero',
            '2' => 'Casado',
            '3' => 'Viudo',
            '4' => 'Separado/Divorciado',
        ];

        $comunidadMap = $this->getCcaaMap();

        $edad = isset($declarante['fechaNacimiento']) ? Carbon::parse($declarante['fechaNacimiento'])->age : null;
        $tieneMenos31Years = $edad !== null ? $edad <= 31 : null;
        $valorPadreMadre = ($declarante['sexo'] ?? null) === 'H' ? 0 : (($declarante['sexo'] ?? null) === 'M' ? 1 : null);

        return [
            ['question_id' => 34, 'value' => $dni],
            ['question_id' => $questionsCache['tiene_dni'] ?? 0, 'value' => ! empty($dni)],
            ['question_id' => $questionsCache['fecha_nacimiento'] ?? 0, 'value' => $fechaNacimiento],
            ['question_id' => $questionsCache['edad'] ?? 0, 'value' => isset($declarante['fechaNacimiento']) ? Carbon::parse($declarante['fechaNacimiento'])->age : null],
            ['question_id' => 42, 'value' => $declarante['sexo'] ?? 0],
            ['question_id' => $questionsCache['eres_padre_madre'] ?? 0, 'value' => $valorPadreMadre],
            ['question_id' => $questionsCache['tiene_dni'], 'value' => true],
            ['question_id' => 33, 'value' => $jsonDocumentContent['informacionPersonal']['nombre'] ?? ($declarante['apellidosNombre'] ?? null)],
            ['question_id' => 58, 'value' => $declarante['gradoDiscapacidad'] ?? 0],
            ['question_id' => 41, 'value' => $estadoCivilMap[$declarante['estadoCivil'] ?? ''] ?? 0],
            ['question_id' => 38, 'value' => $comunidadMap[$this->normalizeComunidadAutonomaCode($economicos) ?? ''] ?? 0],
            ['question_id' => 53, 'value' => isset($declarante['hijos']) ? count($declarante['hijos']) : 0],
            ['question_id' => 47, 'value' => isset($economicos['titulares']['inmuebles']) ? true : false],
            ['question_id' => 35, 'value' => $direccionPersonal ?? 0],
            ['question_id' => $questionsCache['direccion-actual'] ?? 0, 'value' => $direccionPersonal ?? 0],
            ['question_id' => 46, 'value' => $situacionData['estaTrabajandoActualmente']],
            ['question_id' => 57, 'value' => $situacionData['recibePrestacionDesempleo']],
            ['question_id' => 59, 'value' => json_encode($jsonDocumentContent)],
            ['question_id' => $questionsCache['retribucion_trabajo'] ?? 0, 'value' => $datosEconomicos['retribucion_trabajo']],
            ['question_id' => $questionsCache['base_imponible'] ?? 0, 'value' => $datosEconomicos['base_imponible']],
            ['question_id' => $questionsCache['dinero_ganado'] ?? 0, 'value' => $datosEconomicos['base_imponible']],
            ['question_id' => $questionsCache['iban'] ?? 0, 'value' => $iban],
            ['question_id' => $questionsCache['ha_trabajado_3_meses_ultimos_6_meses'] ?? 0, 'value' => $situacionData['trabajoMinimoTresMeses']],
            ['question_id' => $questionsCache['fecha_collector'] ?? 0, 'value' => Carbon::now()],
            ['question_id' => $questionsCache['fecha_formulario_inicial'] ?? 0, 'value' => Carbon::now()],
            ['question_id' => $questionsCache['tiene_menos_31_years'] ?? 0, 'value' => $tieneMenos31Years],
            ['question_id' => $questionsCache['provincia'] ?? 0, 'value' => $provinciaNombreFinal],
            ['question_id' => $questionsCache['municipio'] ?? 0, 'value' => $municipioNombreFinal],
            ['question_id' => $questionsCache['solo_nombre'] ?? 0, 'value' => $solo_nombre],
            ['question_id' => $questionsCache['primer_apellido'] ?? 0, 'value' => $primer_apellido],
            ['question_id' => $questionsCache['segundo_apellido'] ?? 0, 'value' => $segundo_apellido],
        ];
    }

    private function normalizeComunidadAutonomaCode($economicos): ?string
    {
        $raw = $economicos['comunidadAutonoma'] ?? null;
        if ($raw === null) {
            return null;
        }
        $value = is_array($raw) ? ($raw[0] ?? null) : $raw;
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return sprintf('%02d', (int) $value);
        }

        return (string) $value;
    }

    private function getCcaaIdFromEconomicos($economicos): ?int
    {
        $map = $this->getCcaaMap();
        $code = $this->normalizeComunidadAutonomaCode($economicos);
        if (! $code) {
            return null;
        }

        return $map[$code] ?? null;
    }

    private function getCcaaMap(): array
    {
        return [
            '01' => 1,
            '02' => 11,
            '03' => 15,
            '04' => 13,
            '05' => 9,
            '06' => 14,
            '07' => 7,
            '08' => 5,
            '09' => 2,
            '10' => 10,
            '11' => 6,
            '12' => 3,
            '13' => 12,
            '16' => 17,
            '17' => 4,
            '18' => 18,
            '19' => 19,
            '20' => 20,
        ];
    }

    private function saveAnswersBatch($respuestas, $userId)
    {
        $now = now();
        $bulkInsert = [];

        foreach ($respuestas as $respuesta) {
            if ($respuesta['question_id'] > 0 && ! is_null($respuesta['value'])) {
                $bulkInsert[] = [
                    'user_id' => $userId,
                    'question_id' => $respuesta['question_id'],
                    'answer' => $respuesta['value'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($bulkInsert)) {
            DB::transaction(function () use ($bulkInsert) {
                Answer::upsert(
                    $bulkInsert,
                    ['user_id', 'question_id'],
                    ['answer', 'updated_at']
                );
            });
        }
    }
}
