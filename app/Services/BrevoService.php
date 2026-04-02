<?php

namespace App\Services;

use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\UpdateContact;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Ayuda;
use App\Models\User;

enum LISTS: int
{
    case USER_REGISTERED_LIST = 179;
    case NEWSLETTER_LIST = 174;
};
//!!esto tenemos que cambiarlo en produccion para que los ids coincidan con los atributos de Brevo
class BENEFICIARY_ATTRIBUTES
{
    /**
     * Devuelve un array con el mapeo de IDs de ayuda a nombres de atributos de Brevo
     * Incluye los casos estáticos y los IDs dinámicos de Ayuda::idsPorPrograma()
     * 
     * @return array<int, string> Array con [id_ayuda => 'NOMBRE_ATRIBUTO_BREVO']
     */
    public static function getMapping(): array
    {
        $mapping = [
            40 => 'BENEFICIARIO_DAVID_NAVARRA',
            446 => 'BENEFICIARIO_DEDUCCION_MATERNIDAD_AEAT',
            448 => 'BENEFICIARIO_NATALIDAD_ASTURIAS',
            449 => 'BENEFICIARIO_TARXETA_BENVIDA',
            450 => 'BENEFICIARIO_BONO_CONCILIA',
            451 => 'BENEFICIARIO_BONO_NACIMIENTO',
            452 => 'BENEFICIARIO_AYUDA_500_HIJO',
            2 => 'BENEFICIARIO_INGRESO_MINIMO_VITAL',
        ];

        // Añadir IDs dinámicos de programa_estatal_vivienda
        $ids = Ayuda::idsPorPrograma();
        foreach ($ids as $id) {
            $mapping[$id] = 'BENEFICIARIO_PROGRAMA_ESTATAL_VIVIENDA';
        }

        $ids = Ayuda::idsBonoAlquilerJoven();
        foreach ($ids as $id) {
            $mapping[$id] = 'BENEFICIARIO_BONO_ALQUILER_JOVEN';
        }

        return $mapping;
    }

    /**
     * Obtiene el nombre del atributo de Brevo para un ID de ayuda dado
     * 
     * @param int $ayudaId
     * @return string|null
     */
    public static function getAttributeName(int $ayudaId): ?string
    {
        return self::getMapping()[$ayudaId] ?? null;
    }
}

class CONTRATADO_ATTRIBUTES
{
    public static function getMapping(): array
    {
        $mapping = [
            40 => 'CONTRATADA_DAVID_NAVARRA',
            446 => 'CONTRATADA_DEDUCCION_MATERNIDAD_AEAT',
            448 => 'CONTRATADA_NATALIDAD_ASTURIAS',
            449 => 'CONTRATADA_TARXETA_BENVIDA',
            450 => 'CONTRATADA_BONO_CONCILIA',
            451 => 'CONTRATADA_BONO_NACIMIENTO',
            452 => 'CONTRATADA_AYUDA_500_HIJO',
            2 => 'CONTRATADA_INGRESO_MINIMO_VITAL',
        ];

        // Añadir IDs dinámicos de programa_estatal_vivienda
        $ids = Ayuda::idsPorPrograma();
        foreach ($ids as $id) {
            $mapping[$id] = 'CONTRATADA_PROGRAMA_ESTATAL_VIVIENDA';
        }

        $ids = Ayuda::idsBonoAlquilerJoven();
        foreach ($ids as $id) {
            $mapping[$id] = 'CONTRATADA_BONO_ALQUILER_JOVEN';
        }

        return $mapping;
    }

    /**
     * Obtiene el nombre del atributo de Brevo para un ID de ayuda dado
     * 
     * @param int $ayudaId
     * @return string|null
     */
    public static function getAttributeName(int $ayudaId): ?string
    {
        return self::getMapping()[$ayudaId] ?? null;
    }
}

// TODO: Define attributes structure
// email, tipo de ayuda, ccaa, id, nombre, apellidos, telefono
class BrevoService
{
    protected ContactsApi $contactsApi;

    public function __construct()
    {
        // Usar config() en lugar de env() directamente
        // config() funciona incluso cuando hay caché de configuración
        $apiKey = config('services.brevo.key');

        if (empty($apiKey)) {
            Log::error('[BREVO] API Key no configurada. Verifica BREVO_API_KEY en .env y ejecuta: php artisan config:clear');
            throw new \Exception('Brevo API Key no está configurada. Verifica BREVO_API_KEY en tu archivo .env');
        }

        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $apiKey);

        $this->contactsApi = new ContactsApi(
            new GuzzleClient,
            $config
        );
    }

    /*
    *   Este metodo procesa los eventos de Brevo
    *   y los envia a los metodos correspondientes
    *   para ser procesados
    *
    *   @param $eventoOriginal
    *   @return void
    */

    public function procesar($eventoOriginal): void
    {
        $tipoEvento = class_basename($eventoOriginal);

        try {
            switch ($tipoEvento) {

                case 'EventUserRegistered':
                    
                    if ($eventoOriginal->user->brevo_id) {
                        
                        return;
                    } else {
                        $brevoUserId = $this->createContact($eventoOriginal->data);
                        if ($brevoUserId > 0) {
                            $this->insertBrevoId($eventoOriginal->data['id'], $brevoUserId);
                        } else {
                            Log::error('[BREVO][procesar] Error al crear el contacto: ', ['data' => $eventoOriginal->data]);
                            return;
                        }
                    }

                    if ($brevoUserId > 0) {
                        $this->updateContact([
                            'email' => $eventoOriginal->data['email'],
                            'brevo_id' => $brevoUserId,
                            'REGISTRADO_EN_APP' => true,
                            'COLLECTOR' => false,
                            'SOURCE' => 'APP',
                            'BENEFICIARIO_DAVID_NAVARRA' => ["VACIO"],
                            'BENEFICIARIO_DEDUCCION_MATERNIDAD_AEAT' => ["VACIO"],
                            'BENEFICIARIO_NATALIDAD_ASTURIAS' => ["VACIO"],
                            'BENEFICIARIO_TARXETA_BENVIDA' => ["VACIO"],
                            'BENEFICIARIO_BONO_CONCILIA' => ["VACIO"],
                            'BENEFICIARIO_BONO_NACIMIENTO' => ["VACIO"],
                            'BENEFICIARIO_AYUDA_500_HIJO' => ["VACIO"],
                            'BENEFICIARIO_INGRESO_MINIMO_VITAL' => ["VACIO"],
                            'BENEFICIARIO_PROGRAMA_ESTATAL_VIVIENDA' => ["VACIO"],
                            'BENEFICIARIO_BONO_ALQUILER_JOVEN' => ["VACIO"],
                            'CONTRATADA_BONO_ALQUILER_JOVEN' => ["VACIO"],
                            'CONTRATADA_PROGRAMA_ESTATAL_VIVIENDA' => ["VACIO"],
                            'CONTRATADA_DAVID_NAVARRA' => ["VACIO"],
                            'CONTRATADA_DEDUCCION_MATERNIDAD_AEAT' => ["VACIO"],
                            'CONTRATADA_NATALIDAD_ASTURIAS' => ["VACIO"],
                            'CONTRATADA_TARXETA_BENVIDA' => ["VACIO"],
                            'CONTRATADA_BONO_CONCILIA' => ["VACIO"],
                            'CONTRATADA_BONO_NACIMIENTO' => ["VACIO"],
                            'CONTRATADA_AYUDA_500_HIJO' => ["VACIO"],
                            'CONTRATADA_INGRESO_MINIMO_VITAL' => ["VACIO"],

                        ]);
                    } else {
                        Log::error('[BREVO][procesar] Error al crear el contacto: ', ['data' => $eventoOriginal->data]);
                    }

                    //AÑADIR CONTACTO A LA LISTA DE NEWSLETTER
                    $this->addEmailsToList(LISTS::NEWSLETTER_LIST->value, [$eventoOriginal->data['email']]);
                    break;

                case 'EventUserUpdated':
                  
                    //obtenemos el brevo_id del usuario
                    $brevoId = $this->getOrFetchBrevoId($eventoOriginal->user);

                    //añadimos el brevo_id a data
                    $eventoOriginal->data['id'] = $brevoId;

                    //añadimos atributo COLLECTOR a Yes
                    $eventoOriginal->data['COLLECTOR'] = true;
                    $this->updateContact($eventoOriginal->data);
                    break;

                case 'MailUsuarioValidado':

                    break;

                case 'EventUserIsBeneficiary':
                    
                    $ayudaId = $eventoOriginal->ayuda->id;
                    //pasamos el id al map par saber que atributo de Brevo es
                    $beneficiaryAttributeName = BENEFICIARY_ATTRIBUTES::getAttributeName($ayudaId);
                    $contratadoAttributeName = CONTRATADO_ATTRIBUTES::getAttributeName($ayudaId);

                    $brevo_id = $this->getOrFetchBrevoId($eventoOriginal->user);
                    //ahora lo añadimos a data
                    $data = [
                        $beneficiaryAttributeName => ["SI"],
                        $contratadoAttributeName => ["NO"],
                        'id' => $brevo_id,
                        'email' => $eventoOriginal->user->email,
                    ];

                    $this->updateContact($data);

                    break;

                case 'UserIsNotBeneficiary':
                   
                    $ayudaId = $eventoOriginal->ayuda->id;
                    //pasamos el id al map par saber que atributo de Brevo es
                    $beneficiaryAttributeName = BENEFICIARY_ATTRIBUTES::getAttributeName($ayudaId);
                    $brevo_id = $this->getOrFetchBrevoId($eventoOriginal->user);
                    //ahora lo añadimos a data
                    $data = [
                        $beneficiaryAttributeName => ["NO"],
                        'id' => $brevo_id,
                        'email' => $eventoOriginal->user->email,
                    ];

                    $this->updateContact($data);

                    break;

                case 'EventUserContracted':
                   
                    $ayudaId = $eventoOriginal->ayuda->id;
                    //pasamos el id al map par saber que atributo de Brevo es
                    $contratadoAttributeName = CONTRATADO_ATTRIBUTES::getAttributeName($ayudaId);
                    $brevo_id = $this->getOrFetchBrevoId($eventoOriginal->user);
                    //ahora lo añadimos a data
                    $data = [
                        $contratadoAttributeName => ["SI"],
                        'id' => $brevo_id,
                        'email' => $eventoOriginal->user->email,
                    ];

                    $this->updateContact($data);

                    break;

                default:
                    Log::info("[Brevo] Evento no manejado: {$tipoEvento}");
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("[Brevo] Error procesando {$tipoEvento}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    /**
     * Crea un contacto en Brevo
     * @param array $data
     * @return int
     * @throws Exception
     */
    # https://github.com/getbrevo/brevo-php/blob/main/docs/Api/ContactsApi.md#createcontact

    public function createContact(array $data): int
    {
        Log::info('[BREVO][createContact] Start: ', ['data' => $data]);

        $email = $data['email'] ?? null;
        //Debe ser un string para que la API de Brevo lo acepte
        $extId = (string) ($data['id'] ?? null);

        if (! $email || ! $extId) {
            Log::error('[BREVO][createContact] Missing email or extId', ['email' => $email, 'extId' => $extId]);

            return -1;
        }

        $data = array_filter($data, function ($key) {
            return ! in_array($key, ['email', 'id']);
        }, ARRAY_FILTER_USE_KEY);

        $contactProperties = [
            'email' => $email,
            'extId' => $extId,
            'listIds' => [LISTS::USER_REGISTERED_LIST->value],
            'attributes' => $data,

        ];
        $body = new CreateContact($contactProperties);

        try {
            $result = $this->contactsApi->createContact($body);
            Log::info('[BREVO][createContact] API Success: ', ['result' => $result]);

            return $result->getId();
        } catch (Exception $e) {
            Log::error('[BREVO][createContact] API Error: '.$e->getMessage());

            return -1;
        }
    }

    # https://github.com/getbrevo/brevo-php/blob/main/docs/Api/ContactsApi.md#updatecontact
    /**
     * Actualiza un contacto en Brevo
     * Summary of updateContact
     * @param array $data Debe contener 'id' (user_id) o 'brevo_id' (ID interno de Brevo)
     * @return void
     * @throws Exception
     */
    public function updateContact(array $data): void
    {
        Log::info('[BREVO][updateContact] Start: ', ['data' => $data]);

        $brevoId = $data['brevo_id'] ?? null;
        $userId  = $data['id'] ?? null;
        $email   = $data['email'] ?? null;

        $contactId = null;
        $identifierType = null;

        if ($brevoId) {
            // Identificador interno de Brevo
            $contactId = (string)$brevoId;
            $identifierType = 'contact_id';
        } elseif ($userId && ($user = User::find($userId)) && $user->brevo_id) {
            // Tenemos brevo_id guardado en Users
            $contactId = (string)$user->brevo_id;
            $identifierType = 'contact_id';
            Log::info('[BREVO][updateContact] Usando brevo_id del usuario', ['brevo_id' => $contactId, 'user_id' => $userId]);
        } elseif ($email) {
            // Fallback por email
            $contactId = $email;
            $identifierType = 'email_id';
            Log::info('[BREVO][updateContact] Usando email como fallback', ['email' => $email]);
        } else {
            Log::error('[BREVO][updateContact] Missing identifier (brevo_id/email/userId)', ['data' => $data]);
            return;
        }

        // ---- Construcción del payload ----
        $attrs = array_filter($data, fn($k) => !in_array($k, ['id', 'brevo_id', 'email']), ARRAY_FILTER_USE_KEY);
        $attrs = array_combine(array_map('strtoupper', array_keys($attrs)), array_values($attrs));

        // Construye el cuerpo. Si quieres actualizar el email del contacto, hazlo en top-level:
        $contactProperties = [
            'attributes' => $attrs,
        ];
        $body = new UpdateContact($contactProperties);

        try {
            $this->contactsApi->updateContact($body, $contactId, $identifierType);
            Log::info('[BREVO][updateContact] API Success', [
                'contactId' => $contactId,
                'type' => $identifierType,
                'attributes_sent' => array_keys($attrs)
            ]);
        } catch (Exception $e) {
            Log::error('[BREVO][updateContact] API Error: ' . $e->getMessage(), [
                'contactId' => $contactId,
                'type' => $identifierType
            ]);
        }
    }

    /**
     * Resetea todos los atributos BENEFICIARIO_ y CONTRATADO_ a "VACIO" y actualiza REGISTRADO_EN_APP
     * @param int $extId El ID externo del usuario (user_id)
     * @param string|null $email Opcional: email del usuario para obtener atributos existentes
     * @return void
     */
    public function resetBeneficiarioContratadoAttributes(int $extId, ?string $email = null): void
    {
        Log::info('[BREVO][resetBeneficiarioContratadoAttributes] Start: ', ['extId' => $extId, 'email' => $email]);

        $attributesToUpdate = [
            'REGISTRADO_EN_APP' => 'Yes',
        ];

        // Si tenemos el email, obtenemos el contacto para ver qué atributos tiene
        if ($email) {
            $contactResponse = $this->getContact($email);
            if ($contactResponse->successful()) {
                $contact = $contactResponse->json();
                $existingAttributes = $contact['attributes'] ?? [];

                // Buscar todos los atributos que empiezan con BENEFICIARIO_ o CONTRATADO_
                foreach ($existingAttributes as $key => $value) {
                    $upperKey = strtoupper($key);
                    if (str_starts_with($upperKey, 'BENEFICIARIO_') || str_starts_with($upperKey, 'CONTRATADO_')) {
                        $attributesToUpdate[$upperKey] = 'VACIO';
                    }
                }
            } else {
                Log::warning('[BREVO][resetBeneficiarioContratadoAttributes] No se pudo obtener contacto por email, actualizando solo REGISTRADO_EN_APP', [
                    'email' => $email,
                    'status' => $contactResponse->status()
                ]);
            }
        } else {
            // Si no tenemos email, necesitarías pasar una lista de todos los posibles atributos
            // Por ahora solo actualizamos REGISTRADO_EN_APP
            Log::warning('[BREVO][resetBeneficiarioContratadoAttributes] No se proporcionó email, solo se actualizará REGISTRADO_EN_APP');
        }

        // Actualizar usando updateContact
        $this->updateContact(array_merge(['id' => $extId], $attributesToUpdate));

        Log::info('[BREVO][resetBeneficiarioContratadoAttributes] Completed: ', [
            'extId' => $extId,
            'attributesUpdated' => count($attributesToUpdate)
        ]);
    }

    # https://github.com/getbrevo/brevo-php/blob/main/docs/Api/ContactsApi.md#deletecontact
    public function deleteContact(int $extId): void
    {
        Log::info('[BREVO][deleteContact] Start: ', ['extId' => $extId]);

        try {
            $this->contactsApi->deleteContact($extId, 'ext_id');
            Log::info('[BREVO][deleteContact] API Success: ', ['extId' => $extId]);
        } catch (Exception $e) {
            Log::error('[BREVO][deleteContact] API Error: '.$e->getMessage());
        }
    }

    /**
     * Obtiene un contacto por email usando la API de Brevo
     * @param string $email
     * @return \Illuminate\Http\Client\Response
     */
    public function getContact(string $email)
    {
        $response = Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'accept'  => 'application/json',
        ])->get("https://api.brevo.com/v3/contacts/{$email}");

        return $response;
    }

    /**
     * Método helper para hacer requests HTTP a la API de Brevo
     * @param string $method
     * @param string $url
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    protected function request(string $method, string $url, array $data = [])
    {
        $client = Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        switch (strtolower($method)) {
            case 'post':
                return $client->post($url, $data);
            case 'put':
                return $client->put($url, $data);
            case 'delete':
                return $client->delete($url);
            default:
                throw new \InvalidArgumentException("Método HTTP no soportado: {$method}");
        }
    }

    /**
     * Añade emails a una lista de Brevo
     * @param int $listId
     * @param array $emails
     * @return \Illuminate\Http\Client\Response
     */
    public function addEmailsToList(int $listId, array $emails)
    {
        return Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post("https://api.brevo.com/v3/contacts/lists/{$listId}/contacts/add", [
            'emails' => array_values(array_unique($emails)),
        ]);
    }

    public function sendSimpleWhatsAppMessage(string $recipientNumber, int $templateId)
    {
        // Preparar el payload con 'contactNumbers' como array (aunque sea uno solo)
        $payload = [
            'senderNumber' => '34655211569', // tu número aprobado en Brevo
            'contactNumbers' => [$recipientNumber],
            'templateId' => $templateId,
        ];

        Log::info('📤 Enviando WhatsApp sin parámetros', ['payload' => $payload]);

        $response = $this->request('post', 'https://api.brevo.com/v3/whatsapp/sendMessage', $payload);

        if ($response->successful()) {
            Log::info('✅ WhatsApp enviado correctamente', ['response' => $response->json()]);
        } else {
            Log::error('❌ Error al enviar WhatsApp', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }

    public function sendWhatsAppMessageWithParams(string $recipientNumber, int $templateId, array $params = [])
    {
        // Formatear número para que siempre lleve el prefijo +34 (o el que uses)
        $formattedNumber = preg_replace('/[^0-9]/', '', $recipientNumber);
        if (!str_starts_with($formattedNumber, '34')) {
            $formattedNumber = '34' . $formattedNumber;
        }

        $payload = [
            'senderNumber' => '34655211569',  // tu número WhatsApp autorizado
            'contactNumbers' => [$formattedNumber],
            'templateId' => $templateId,
        ];

        // Solo añadir params si hay, para evitar errores en API
        if (!empty($params)) {
            $payload['params'] = $params;
        }

        Log::info('📤 Enviando WhatsApp con parámetros a través de Brevo', [
            'payload' => $payload
        ]);

        $response = $this->request('post', 'https://api.brevo.com/v3/whatsapp/sendMessage', $payload);

        if ($response->successful()) {
            Log::info('✅ WhatsApp enviado correctamente', ['response' => $response->json()]);
        } else {
            Log::error('❌ Error al enviar WhatsApp', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }

    /**
     * Inserta/actualiza un contacto en la lista de newsletter con REGISTRADO_EN_APP = "Yes".
     * Esta funcion se esta utilizando para el registro de usuarios en la app, desde el link de la newsletter
     * y desde el formulario de registro en la web de wordpress.
     *
     * @param array $data  ['email' => '...', 'FIRSTNAME' => '...', 'LASTNAME' => '...', ...]
     * @param int   $listId  ID de lista en Brevo (por defecto 174, ajusta)
     * @return array  Resultado combinado de acciones realizadas
     */
    public function upsertNewsletterContact(array $data, int $listId = 174, bool $registeredInApp = true): array
    {
        $email = strtolower(trim((string)($data['email'] ?? '')));
        if ($email === '') {
            Log::warning('[BREVO] upsertNewsletterContact: email vacío', ['data' => $data]);
            return ['ok' => false, 'reason' => 'empty_email'];
        }

        // Atributos a enviar/actualizar (añade los que uséis en Brevo)
        $attributes = array_filter([
            'FIRSTNAME'          => $data['FIRSTNAME'] ?? null,
            'LASTNAME'           => $data['LASTNAME']  ?? null,
            'NOMBRE'            => $data['FIRSTNAME'] ?? null,
            'REGISTRADO_EN_APP'  => $registeredInApp,
            'SOURCE'             => $data['SOURCE'] ?? null,
        ], fn($v) => $v !== null && $v !== '');

        // 1) Consultar si existe
        $get = $this->getContact($email);

        // 1.a) No existe → crearlo + añadir a lista (updateEnabled true cubre ambos casos)
        if ($get->status() === 404) {
            $payload = [
                'email'         => $email,
                'attributes'    => $attributes,
                'listIds'       => [$listId],
                'updateEnabled' => true, // si existiera, lo actualiza
            ];

            Log::info('[BREVO] create+upsert contacto (no existía)', ['email' => $email, 'payload' => $payload]);
            $resp = $this->request('post', 'https://api.brevo.com/v3/contacts', $payload);

            if (!$resp->successful()) {
                Log::error('[BREVO] fallo creando contacto', [
                    'email' => $email,
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                ]);
                return ['ok' => false, 'status' => $resp->status(), 'body' => $resp->json()];
            }

            return ['ok' => true, 'created' => true, 'addedToList' => true, 'setYes' => true, 'response' => $resp->json()];
        }

        // 1.b) Error no controlado
        if (!$get->successful()) {
            Log::error('[BREVO] error consultando contacto', [
                'email' => $email,
                'status' => $get->status(),
                'body' => $get->body(),
            ]);
            return ['ok' => false, 'status' => $get->status(), 'body' => $get->json()];
        }

        // 2) Existe → comprobar lista y atributo
        $contact = $get->json();
        $listIds = $contact['listIds'] ?? [];
        $inList  = in_array((int)$listId, $listIds, true);

        $currentAttr = $contact['attributes']['REGISTRADO_EN_APP'] ?? null;
        $isYes = (is_string($currentAttr) && strcasecmp($currentAttr, 'Yes') === 0);

        $result = ['ok' => true, 'created' => false, 'addedToList' => false, 'updatedAttr' => false];

        // 2.a) Añadir a lista si falta
        if (!$inList) {
            $add = $this->addEmailsToList($listId, [$email]);
            if ($add->successful()) {
                $result['addedToList'] = true;
            } else {
                Log::error('[BREVO] fallo añadiendo a lista', [
                    'email' => $email,
                    'status' => $add->status(),
                    'body' => $add->body(),
                ]);
                // seguimos para intentar actualizar atributo igualmente
            }
        }

        // 2.b) Forzar REGISTRADO_EN_APP = "Yes" si no lo está
        if (!$isYes) {
            // Actualizar usando el API HTTP directamente ya que updateContact requiere extId
            $updateData = array_merge(['REGISTRADO_EN_APP' => 'Yes'], $attributes);
            $upd = $this->request('put', "https://api.brevo.com/v3/contacts/{$email}", [
                'attributes' => $updateData
            ]);
            // updateContact ya loguea éxito/fracaso; reflejamos el estado:
            $result['updatedAttr'] = $upd->successful(); // si Brevo devuelve error, quedará registrado en logs
            $result['updateResponse'] = $upd->json();
        }

        return $result;
    }

    /**
     * Obtiene el brevo_id de un usuario.
     * Si no existe en la BD, lo busca en Brevo por email y lo guarda.
     * 
     * @param User|object $user El objeto User o un objeto con propiedades id, email y brevo_id
     * @return int|null El brevo_id o null si no se encuentra
     */
    public function getOrFetchBrevoId($user): ?int
    {
        // Si ya tenemos el brevo_id guardado, lo retornamos
        if (isset($user->brevo_id) && $user->brevo_id) {
            return $user->brevo_id;
        }

        // Si no, intentamos buscarlo en Brevo por email
        $email = $user->email ?? null;
        if (!$email) {
            Log::warning('[BREVO][getOrFetchBrevoId] No se proporcionó email para buscar brevo_id', [
                'user_id' => $user->id ?? null
            ]);
            return null;
        }

        $response = $this->getContact($email);

        if ($response->successful()) {
            $brevoId = $response->json()['id'] ?? null;

            if ($brevoId && isset($user->id)) {
                // Guardamos el brevo_id en la BD para futuras consultas
                $this->insertBrevoId($user->id, $brevoId);
                Log::info('[BREVO][getOrFetchBrevoId] brevo_id obtenido y guardado', [
                    'user_id' => $user->id,
                    'brevo_id' => $brevoId
                ]);
            }

            return $brevoId;
        }

        Log::warning('[BREVO][getOrFetchBrevoId] No se encontró contacto en Brevo', [
            'email' => $email,
            'user_id' => $user->id ?? null,
            'status' => $response->status()
        ]);

        return null;
    }

    /**
     * Inserta el brevo_id en la tabla users
     * @param int $userId
     * @param int $brevoId
     * @return void
     */
    public function insertBrevoId(int $userId, int $brevoId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->brevo_id = $brevoId;
            $user->save();
        }
    }
}
