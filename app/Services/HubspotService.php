<?php

namespace App\Services;

use App\Models\Ayuda;
use App\Models\Contratacion;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubspotService
{
    protected $apiKey;

    protected $baseUrl = 'https://api.hubapi.com';

    // !PIPELINE OPS
    const OP1_PIPELINE = 3303766249;

    const OP1_DOCUMENTACION = 4525648119;

    const OP1_TRAMITACION = 4525648120;

    const OP1_ALEGACIÓN = 4525648121;

    const OP1_SUBSANCIÓN = 4525649082;

    const OP1_RESOLUCION = 4525649098;

    const OP1_CIERRE = 4525649099;

    // !PIPELINE OP2
    const OP2_PIPELINE = 3303766250;

    const OP2_DOCUMENTACION = 4525649100;

    const OP2_TRAMITACION = 4525649101;

    const OP2_PENDIENTE_COBRO = 4720751850;

    const OP2_SUBSANCIÓN = 4525649083;

    const OP2_RENUNCIA = 4648505592;

    const OP2_CIERRE = 4525649105;

    // !PIPELINE OP3
    const OP3_PIPELINE = 3299541241;

    const OP3_DOCUMENTACION = 4527247586;

    const OP3_TRAMITACION = 4527247587;

    const OP3_CIERRE = 4527247588;

    // !PIPELINE OP4
    const OP4_PIPELINE = 3298680035;

    const OP4_PAGANDO = 4527772864;

    const OP4_COBRANDO = 4527772865;

    const OP4_MOROSOS = 4664994023;

    const OP4_COBRADO = 4580156629;

    // !PIPELINE OP5
    const OP5_DESISTIDO = 4579424457;

    const OP5_RECHAZADO = 4579424458;

    const OP5_FUERA_PLAZO = 4676452549;

    // !PIPELINE VENTAS
    // Fases
    const VENTAS_REGISTRO = 4583011531;

    const VENTAS_COLECCTOR = 4640347354;

    const VENTAS_BENEFICIARIO = 4583011532;

    const VENTAS_NO_BENEFICIARIO = 4514566349;

    const VENTAS_CONTRATADA = 4812762351;

    public function __construct()
    {
        $this->apiKey = config('services.hubspot.api_key');

        if (empty($this->apiKey)) {
            Log::error('[HUBSPOT] API Key no configurada. Verifica HUBSPOT_API_KEY en .env y ejecuta: php artisan config:clear');
            throw new \Exception('HubSpot API Key no está configurada. Verifica HUBSPOT_API_KEY en tu archivo .env');
        }

        // Limpiar espacios en blanco que puedan estar al inicio/final
        $this->apiKey = trim($this->apiKey);

    }

    /**
     * Procesa el evento original y lo envía a HubSpot
     */
    public function procesar($eventoOriginal): void
    {
        // Identificar el tipo de evento y procesarlo
        $tipoEvento = class_basename($eventoOriginal);

        switch ($tipoEvento) {
            case 'EventUserRegistered':
                $this->procesarUserRegistered($eventoOriginal);
                break;
            case 'EventUserContracted':
                $this->procesarUserContracted($eventoOriginal);
                break;
            case 'EventUserUpdated':
                $this->procesarUserUpdated($eventoOriginal);
                break;
            case 'EventUserIsBeneficiary':
                $this->procesarUserIsBeneficiary($eventoOriginal);
                break;
            case 'EventUserIsNotBeneficiary':
                $this->procesarUserIsNotBeneficiary($eventoOriginal);
                break;
            case 'EventDocumentUploaded':
                $this->procesarDocumentUploaded($eventoOriginal);
                break;
            case 'EventDocumentValidated':
                $this->procesarDocumentValidated($eventoOriginal);
                break;
            case 'EventDocumentRejected':
                $this->procesarDocumentRejected($eventoOriginal);
                break;
            case 'EventContratacionStatusChanged':
                $this->procesarContratacionStatusChanged($eventoOriginal);
                break;
            case 'EventContratacionCompleted':
                $this->procesarContratacionCompleted($eventoOriginal);
                break;
            case 'EventContratacionCierreRechazada':
                $this->procesarContratacionCierreRechazada($eventoOriginal);
                break;
            case 'EventContratacionCierreResolucion':
                $this->procesarContratacionCierreResolucion($eventoOriginal);
                break;
            case 'EventConcesionRegistrada':
                $this->procesarConcesionRegistrada($eventoOriginal);
                break;
            case 'EventPagoRegistrado':
                $this->procesarPagoRegistrado($eventoOriginal);
                break;
            case 'EventCobroRealizado':
                $this->procesarCobroRealizado($eventoOriginal);
                break;
            default:
                Log::warning('[HubspotService] Tipo de evento no manejado', [
                    'tipo' => $tipoEvento,
                ]);
                break;
        }
    }

    // *********************PIPELINE VENTAS************************************************

    /**
     * Procesa el evento EventUserRegistered y crea un contacto en HubSpot
     * Además crea un Deal en el pipeline de Ventas en la fase Registro
     */
    protected function procesarUserRegistered($evento): void
    {
        $user = $evento->user ?? null;
        if (! $user) {
            Log::warning('[HubspotService] EventUserRegistered sin usuario', [
                'event' => get_class($evento),
            ]);

            return;
        }

        // Preparar datos del contacto
        $contactData = $this->prepareContactData($user, $evento->data ?? []);

        // Añadir lifecyclestage para nuevos registros
        $contactData['lifecyclestage'] = 'lead';

        // Crear contacto en HubSpot
        $result = $this->createContact($contactData);

        if ($result['success'] && isset($result['contact_id'])) {
            // Crear Deal en pipeline de Ventas en fase Registro
            $contactId = $result['contact_id'];
            $dealName = "LEAD-{$user->email}";

            $dealData = [
                'dealname' => $dealName,
                'dealstage' => self::VENTAS_REGISTRO,
                'pipeline' => 'default', // Pipeline Ventas por defecto
            ];

            $dealResult = $this->createDeal($dealData, $contactId);

            if (! $dealResult['success']) {
                Log::error('[HubspotService] Error al crear deal de registro', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $dealResult['error'] ?? 'Error desconocido',
                ]);
            }
        } else {
            Log::error('[HubspotService] Error al crear contacto', [
                'user_id' => $user->id,
                'error' => $result['error'] ?? 'Error desconocido',
            ]);
        }
    }

    /**
     * Procesa el evento EventUserUpdated (cuando un user realiza el collector).
     * Busca el negocio LEAD-{email} en fase Registro: si existe lo mueve a COLECCTOR;
     * si no existe, crea un nuevo negocio en fase COLECCTOR.
     */
    protected function procesarUserUpdated($evento): void
    {
        $user = $evento->user ?? null;
        if (! $user) {
            Log::warning('[HubspotService] EventUserUpdated sin usuario', [
                'event' => get_class($evento),
            ]);

            return;
        }

        // Preparar datos del contacto
        $contactData = $this->prepareContactData($user, $evento->data ?? []);

        // Actualizar contacto en HubSpot (o crearlo si no existe)
        $result = $this->updateContact($contactData);

        if (! $result['success']) {
            Log::error('[HubspotService] Error al actualizar contacto', [
                'user_id' => $user->id,
                'error' => $result['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $result['contact_id'] ?? null;
        if (! $contactId) {
            Log::warning('[HubspotService] EventUserUpdated: no se obtuvo contact_id', ['user_id' => $user->id]);

            return;
        }

        $dealName = "LEAD-{$user->email}";
        $deal = $this->findDealByContactAndName($contactId, $dealName);

        if ($deal) {
            $dealId = $deal['id'] ?? null;
            $currentStage = $deal['properties']['dealstage'] ?? null;
            if ($dealId && (string) $currentStage === (string) self::VENTAS_REGISTRO) {
                $updateResult = $this->updateDeal($dealId, [
                    'dealstage' => self::VENTAS_COLECCTOR,
                    'pipeline' => 'default',
                ]);
                if (! $updateResult['success']) {
                    Log::error('[HubspotService] Error al actualizar deal a COLECCTOR', [
                        'user_id' => $user->id,
                        'deal_id' => $dealId,
                        'error' => $updateResult['error'] ?? 'Error desconocido',
                    ]);
                }
            }

            return;
        }

        // No existe deal en Registro: crear nuevo en fase COLECCTOR
        $dealData = [
            'dealname' => $dealName,
            'dealstage' => self::VENTAS_COLECCTOR,
            'pipeline' => 'default',
        ];
        $dealResult = $this->createDeal($dealData, $contactId);
        if (! $dealResult['success']) {
            Log::error('[HubspotService] Error al crear deal COLECCTOR', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $dealResult['error'] ?? 'Error desconocido',
            ]);
        }
    }

    /**
     * Procesa el evento EventUserIsBeneficiary.
     * Caso 1: Si el user tiene un negocio en fase Registro o COLECCTOR (LEAD-email), es la primera vez
     *         que completa un formulario de una ayuda → actualizamos ese negocio a Beneficiario y renombramos.
     * Caso 2: No tiene negocio en Registro/COLECCTOR (ya hizo un formulario antes) → creamos un nuevo
     *         negocio en Beneficiario (un contacto puede tener varios negocios de distintas ayudas).
     */
    protected function procesarUserIsBeneficiary($evento): void
    {
        $user = $evento->user ?? null;
        $ayuda = $evento->ayuda ?? null;

        if (! $user || ! $ayuda) {
            Log::warning('[HubspotService] EventUserIsBeneficiary sin usuario o ayuda', [
                'user_id' => $user->id ?? null,
                'ayuda_id' => $ayuda->id ?? null,
            ]);

            return;
        }

        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para crear deal', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        $ccaaNombre = null;
        try {
            $ccaaNombre = $ayuda->getCcaa();
        } catch (\Exception $e) {
            Log::warning('[HubspotService] Error al obtener CCAA de la ayuda', [
                'ayuda_id' => $ayuda->id,
                'error' => $e->getMessage(),
            ]);
        }

        $amount = $ayuda->cuantia_usuario ?? null;
        $dealNameVentas = 'VENTAS-'.$this->getDealNameSuffix($ayuda);

        $leadDealName = "LEAD-{$user->email}";
        $existingLeadDeal = $this->findDealByContactAndName($contactId, $leadDealName);

        $stagesLead = [(string) self::VENTAS_REGISTRO, (string) self::VENTAS_COLECCTOR];
        $currentStage = $existingLeadDeal['properties']['dealstage'] ?? null;
        $isFirstTimeForm = $existingLeadDeal && $currentStage !== null && in_array((string) $currentStage, $stagesLead, true);

        if ($isFirstTimeForm) {
            // Caso 1: Negocio en Registro/COLECCTOR → pasar a Beneficiario y renombrar
            $dealId = $existingLeadDeal['id'] ?? null;
            if (! $dealId) {
                Log::error('[HubspotService] Deal LEAD sin id en EventUserIsBeneficiary', ['user_id' => $user->id]);

                return;
            }

            $updateData = [
                'dealname' => $dealNameVentas,
                'dealstage' => self::VENTAS_BENEFICIARIO,
                'pipeline' => 'default',
            ];
            if ($amount !== null && $amount !== '') {
                $updateData['amount'] = (string) floatval($amount);
            }
            if ($ccaaNombre) {
                $updateData['ccaa'] = $this->mapCcaaToHubspot($ccaaNombre);
            }
            if ($ayuda->fecha_fin) {
                $updateData['closedate'] = (string) ($ayuda->fecha_fin->timestamp * 1000);
            }

            $updateResult = $this->updateDeal($dealId, $updateData);
            if (! $updateResult['success']) {
                Log::error('[HubspotService] Error al actualizar deal a Beneficiario', [
                    'user_id' => $user->id,
                    'deal_id' => $dealId,
                    'error' => $updateResult['error'] ?? 'Error desconocido',
                ]);
            }

            return;
        }

        // Caso 2: Crear nuevo negocio en Beneficiario (varias ayudas por contacto)
        $dealData = [
            'dealname' => $dealNameVentas,
            'amount' => $amount,
            'dealstage' => self::VENTAS_BENEFICIARIO,
            'pipeline' => 'default',
            'Is Deal Closed?' => false,
        ];
        if ($ccaaNombre) {
            $dealData['ccaa'] = $this->mapCcaaToHubspot($ccaaNombre);
        }
        if ($ayuda->fecha_fin) {
            $dealData['closedate'] = $ayuda->fecha_fin->timestamp * 1000;
        }

        $dealResult = $this->createDeal($dealData, $contactId);
        if (! $dealResult['success']) {
            Log::error('[HubspotService] Error al crear deal para beneficiario', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'error' => $dealResult['error'] ?? 'Error desconocido',
            ]);
        }
    }

    /**
     * Procesa el evento EventUserIsNotBeneficiary.
     */
    protected function procesarUserIsNotBeneficiary($evento): void
    {
        $user = $evento->user ?? null;
        $ayuda = $evento->ayuda ?? null;

        if (! $user || ! $ayuda) {
            Log::warning('[HubspotService] EventUserIsNotBeneficiary sin usuario o ayuda', [
                'user_id' => $user->id ?? null,
                'ayuda_id' => $ayuda->id ?? null,
            ]);

            return;
        }

        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para crear deal', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        $ccaaNombre = null;
        try {
            $ccaaNombre = $ayuda->getCcaa();
        } catch (\Exception $e) {
            Log::warning('[HubspotService] Error al obtener CCAA de la ayuda', [
                'ayuda_id' => $ayuda->id,
                'error' => $e->getMessage(),
            ]);
        }

        $razonesNoCumple = $evento->razones_no_cumple ?? null;
        if (is_array($razonesNoCumple)) {
            $razonesNoCumple = implode(', ', $razonesNoCumple);
        }

        $dealNameVentas = 'VENTAS-'.$this->getDealNameSuffix($ayuda);
        $leadDealName = "LEAD-{$user->email}";
        $existingLeadDeal = $this->findDealByContactAndName($contactId, $leadDealName);

        $stagesLead = [(string) self::VENTAS_REGISTRO, (string) self::VENTAS_COLECCTOR];
        $currentStage = $existingLeadDeal['properties']['dealstage'] ?? null;
        $isFirstTimeForm = $existingLeadDeal && $currentStage !== null && in_array((string) $currentStage, $stagesLead, true);

        if ($isFirstTimeForm) {
            // Caso 1: Negocio en Registro/COLECCTOR → pasar a No beneficiario y renombrar
            $dealId = $existingLeadDeal['id'] ?? null;
            if (! $dealId) {
                Log::error('[HubspotService] Deal LEAD sin id en EventUserIsNotBeneficiary', ['user_id' => $user->id]);

                return;
            }

            $updateData = [
                'dealname' => $dealNameVentas,
                'dealstage' => self::VENTAS_NO_BENEFICIARIO,
                'pipeline' => 'default',
                'amount' => '0',
                'razones_de_no_cualificacion' => $razonesNoCumple ?? '',
            ];
            if ($ccaaNombre) {
                $updateData['ccaa'] = $this->mapCcaaToHubspot($ccaaNombre);
            }
            if ($ayuda->fecha_fin) {
                $updateData['closedate'] = (string) ($ayuda->fecha_fin->timestamp * 1000);
            }

            $updateResult = $this->updateDeal($dealId, $updateData);
            if (! $updateResult['success']) {
                Log::error('[HubspotService] Error al actualizar deal a No beneficiario', [
                    'user_id' => $user->id,
                    'deal_id' => $dealId,
                    'error' => $updateResult['error'] ?? 'Error desconocido',
                ]);
            }

            return;
        }

        // Caso 2: Crear nuevo negocio en No beneficiario (varias ayudas por contacto)
        $dealData = [
            'dealname' => $dealNameVentas,
            'amount' => 0,
            'dealstage' => self::VENTAS_NO_BENEFICIARIO,
            'pipeline' => 'default',
            'Is Deal Closed?' => false,
            'razones_de_no_cualificacion' => $razonesNoCumple,
        ];
        if ($ccaaNombre) {
            $dealData['ccaa'] = $this->mapCcaaToHubspot($ccaaNombre);
        }
        if ($ayuda->fecha_fin) {
            $dealData['closedate'] = $ayuda->fecha_fin->timestamp * 1000;
        }

        $dealResult = $this->createDeal($dealData, $contactId);
        if (! $dealResult['success']) {
            Log::error('[HubspotService] Error al crear deal para no beneficiario', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'error' => $dealResult['error'] ?? 'Error desconocido',
            ]);
        }
    }

    // *********************PIPELINE OPERATIVA************************************************
    // !No se usa en la actualidad
    /**
     * Procesa el evento EventContratacionCompleted
     * Cuando un usuario completa el formulario de una ayuda, se actualiza el dealstage a Tramitación.
     */
    protected function procesarContratacionCompleted($evento): void
    {
        $contratacion = $evento->contratacion ?? null;

        if (! $contratacion) {
            Log::warning('[HubspotService] EventContratacionCompleted sin contratación', [
                'event' => get_class($evento),
            ]);

            return;
        }

        $user = $contratacion->user;
        $ayuda = $contratacion->ayuda;

        if (! $user || ! $ayuda) {
            Log::error('[HubspotService] No se pudo obtener usuario o ayuda para EventContratacionCompleted', [
                'contratacion_id' => $contratacion->id,
                'user_id' => $contratacion->user_id,
                'ayuda_id' => $contratacion->ayuda_id,
            ]);

            return;
        }

        // Obtener o crear el contacto en HubSpot
        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para actualizar deal', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        $dealName = 'OP1-'.$this->getDealNameSuffix($ayuda);

        // Buscar el deal por nombre y contacto
        $deal = $this->findDealByContactAndName($contactId, $dealName);

        if (! $deal) {
            Log::warning('[HubspotService] No se encontró deal para actualizar en EventContratacionCompleted', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'dealname' => $dealName,
                'contact_id' => $contactId,
            ]);

            return;
        }

        $dealId = $deal['id'] ?? null;
        if (! $dealId) {
            Log::error('[HubspotService] Deal encontrado pero sin ID en EventContratacionCompleted', [
                'deal' => $deal,
            ]);

            return;
        }

        // Verificar que la ayuda tenga el período abierto (fecha_inicio <= fecha actual)
        $hoy = \Carbon\Carbon::today();
        $fechaInicio = $ayuda->fecha_inicio ? \Carbon\Carbon::parse($ayuda->fecha_inicio) : null;

        if (! $fechaInicio || $fechaInicio->gt($hoy)) {
            return;
        }

        // Actualizar el dealstage en HubSpot
        $updateData = [
            'pipeline' => self::OP1_PIPELINE,
            'dealstage' => self::OP1_TRAMITACION,

        ];

        $updateResult = $this->updateDeal($dealId, $updateData);
        if (! $updateResult['success']) {
            Log::error('[HubspotService] Error al actualizar deal en EventContratacionCompleted', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'deal_id' => $dealId,
                'error' => $updateResult['error'] ?? 'Error desconocido',
                'status' => $updateResult['status'] ?? null,
            ]);
        }
    }

    /**
     * Procesa el evento EventUserContracted
     */
    protected function procesarUserContracted($evento): void
    {
        $user = $evento->user ?? null;
        $ayuda = $evento->ayuda ?? null;

        if (! $user || ! $ayuda) {
            Log::warning('[HubspotService] EventUserContracted sin usuario o ayuda', [
                'user_id' => $user->id ?? null,
                'ayuda_id' => $ayuda->id ?? null,
            ]);

            return;
        }

        // Obtener o crear el contacto en HubSpot
        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para actualizar deal', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        // Nombre del deal en pipeline VENTAS (creado al marcar como beneficiario)
        $suffix = $this->getDealNameSuffix($ayuda);
        $dealNameVentas = 'VENTAS-'.$suffix;
        // Nombre del deal en pipeline OP1 (operativa), para cuando creamos nuevo negocio
        $dealNameOp1 = 'OP1-'.$suffix;

        // 1) Buscar si ya existe un negocio VENTAS para este contacto y ayuda (cualquier fase: Beneficiario, No beneficiario, etc.)
        $dealVentas = $this->findDealByContactAndName($contactId, $dealNameVentas);

        if ($dealVentas !== null && ! empty($dealVentas['id'])) {
            // 2a) Existe deal VENTAS (Beneficiario, No beneficiario u otro): actualizarlo a Contratada y asegurar OP1
            $dealIdVentas = $dealVentas['id'];

            $contratacion = Contratacion::where('user_id', $user->id)
                ->where('ayuda_id', $ayuda->id)
                ->first();
            $productoNombre = null;
            $comision = null;
            $precio = null;
            if ($contratacion && $contratacion->product_id) {
                $producto = Product::find($contratacion->product_id);
                if ($producto) {
                    $productoNombre = $producto->product_name;
                    $comision = $producto->commission_pct;
                    $precio = $producto->price;
                }
            }

            $updateDataVentas = [
                'dealstage' => (string) self::VENTAS_CONTRATADA,
                'nombre_del_producto_seleccionado' => $productoNombre,
                'comision' => $comision,
                'precio_producto' => $precio,
                'Is Deal Closed?' => true,
            ];
            $this->updateDeal($dealIdVentas, $updateDataVentas);

            // Asegurar que exista deal OP1 (crear si no existe, actualizar si existe)
            $dealOp1 = $this->findDealByContactAndName($contactId, $dealNameOp1);
            $amount = $ayuda->cuantia_usuario ?? null;
            if ($dealOp1 && ! empty($dealOp1['id'])) {
                $this->updateDeal($dealOp1['id'], array_merge($updateDataVentas, [
                    'dealname' => $dealNameOp1,
                    'pipeline' => (string) self::OP1_PIPELINE,
                    'dealstage' => (string) self::OP1_DOCUMENTACION,
                ]));
            } else {
                $dealDataOp1 = [
                    'dealname' => $dealNameOp1,
                    'pipeline' => (string) self::OP1_PIPELINE,
                    'dealstage' => (string) self::OP1_DOCUMENTACION,
                    'Is Deal Closed?' => false,
                ];
                if ($amount !== null && $amount !== '') {
                    $dealDataOp1['amount'] = (string) floatval($amount);
                }
                $createOp1 = $this->createDeal($dealDataOp1, $contactId);
                if ($createOp1['success'] && ! empty($createOp1['deal_id'])) {
                    $this->updateDeal($createOp1['deal_id'], [
                        'nombre_del_producto_seleccionado' => $productoNombre,
                        'comision' => $comision,
                        'precio_producto' => $precio,
                        'Is Deal Closed?' => true,
                    ]);
                }
            }

            return;
        }

        // 2b) No existe deal VENTAS: crear nuevo negocio en OP1-Documentación y en VENTAS-Contratada
        $amount = $ayuda->cuantia_usuario ?? null;

        // Crear negocio en OP1-Documentación
        $dealDataOp1 = [
            'dealname' => $dealNameOp1,
            'pipeline' => (string) self::OP1_PIPELINE,
            'dealstage' => (string) self::OP1_DOCUMENTACION,
            'Is Deal Closed?' => false,
        ];
        if ($amount !== null && $amount !== '') {
            $dealDataOp1['amount'] = (string) floatval($amount);
        }
        $dealResult = $this->createDeal($dealDataOp1, $contactId);
        if (! $dealResult['success'] || empty($dealResult['deal_id'])) {
            Log::error('[HubspotService] Error al crear deal OP1 en EventUserContracted', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'error' => $dealResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }
        $dealId = $dealResult['deal_id'];

        // Crear también el negocio en VENTAS en la fase Contratada
        $dealDataVentas = [
            'dealname' => $dealNameVentas,
            'pipeline' => 'default',
            'dealstage' => (string) self::VENTAS_CONTRATADA,
            'Is Deal Closed?' => true,
        ];
        if ($amount !== null && $amount !== '') {
            $dealDataVentas['amount'] = (string) floatval($amount);
        }
        $dealResultVentas = $this->createDeal($dealDataVentas, $contactId);
        $dealIdVentas = $dealResultVentas['deal_id'] ?? null;
        if (! $dealResultVentas['success']) {
            Log::warning('[HubspotService] No se pudo crear deal VENTAS-Contratada (OP1 creado correctamente)', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'error' => $dealResultVentas['error'] ?? 'Error desconocido',
            ]);
        }

        $contratacion = Contratacion::where('user_id', $user->id)
            ->where('ayuda_id', $ayuda->id)
            ->first();
        $productoNombre = null;
        $comision = null;
        $precio = null;
        if ($contratacion && $contratacion->product_id) {
            $producto = Product::find($contratacion->product_id);
            if ($producto) {
                $productoNombre = $producto->product_name;
                $comision = $producto->commission_pct;
                $precio = $producto->price;
            }
        }

        $updateData = [
            'nombre_del_producto_seleccionado' => $productoNombre,
            'comision' => $comision,
            'precio_producto' => $precio,
            'Is Deal Closed?' => true,
        ];
        $this->updateDeal($dealId, $updateData);
        if ($dealIdVentas) {
            $this->updateDeal($dealIdVentas, $updateData);
        }
    }

    // !No se usa en la actualidad
    /**
     * Procesa el evento EventContratacionCierreRechazada
     */
    protected function procesarContratacionCierreRechazada($evento): void
    {
        $contratacion = $evento->contratacion ?? null;

        if (! $contratacion) {
            Log::warning('[HubspotService] EventContratacionCierreRechazada sin contratación', [
                'event' => get_class($evento),
            ]);

            return;
        }

        $user = $contratacion->user;
        $ayuda = $contratacion->ayuda;

        if (! $user || ! $ayuda) {
            Log::error('[HubspotService] No se pudo obtener usuario o ayuda para EventContratacionCierreRechazada', [
                'contratacion_id' => $contratacion->id,
                'user_id' => $contratacion->user_id,
                'ayuda_id' => $contratacion->ayuda_id,
            ]);

            return;
        }

        // Obtener o crear el contacto en HubSpot
        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para actualizar deal', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        // El nombre del deal es el nombre de la ayuda
        $dealName = "OPERATIVA-{$ayuda->nombre_ayuda}";

        // Buscar el deal por nombre y contacto
        $deal = $this->findDealByContactAndName($contactId, $dealName);

        if (! $deal) {
            Log::warning('[HubspotService] No se encontró deal para actualizar en EventContratacionCierreRechazada', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'dealname' => $dealName,
                'contact_id' => $contactId,
            ]);

            return;
        }

        $dealId = $deal['id'] ?? null;
        if (! $dealId) {
            Log::error('[HubspotService] Deal encontrado pero sin ID en EventContratacionCierreRechazada', [
                'deal' => $deal,
            ]);

            return;
        }

        // Actualizar el dealstage en HubSpot a CIERRE
        $updateData = [
            'pipeline' => self::OP1_PIPELINE, // OPERATIVA
            'dealstage' => self::OP1_CIERRE, // CIERRE
            'Is Deal Closed?' => true,
        ];

        $updateResult = $this->updateDeal($dealId, $updateData);
        if (! $updateResult['success']) {
            Log::error('[HubspotService] Error al actualizar deal en EventContratacionCierreRechazada', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'deal_id' => $dealId,
                'error' => $updateResult['error'] ?? 'Error desconocido',
                'status' => $updateResult['status'] ?? null,
            ]);
        }
    }

    // !No se usa en la actualidad
    /**
     * Procesa el evento EventContratacionCierreResolucion
     */
    protected function procesarContratacionCierreResolucion($evento): void
    {
        $contratacion = $evento->contratacion ?? null;

        if (! $contratacion) {
            Log::warning('[HubspotService] EventContratacionCierreResolucion sin contratación', [
                'event' => get_class($evento),
            ]);

            return;
        }

        $user = $contratacion->user;
        $ayuda = $contratacion->ayuda;

        if (! $user || ! $ayuda) {
            Log::error('[HubspotService] No se pudo obtener usuario o ayuda para EventContratacionCierreResolucion', [
                'contratacion_id' => $contratacion->id,
                'user_id' => $contratacion->user_id,
                'ayuda_id' => $contratacion->ayuda_id,
            ]);

            return;
        }

        // Obtener o crear el contacto en HubSpot
        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para actualizar deal', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        // El nombre del deal es el nombre de la ayuda
        $dealName = "OPERATIVA-{$ayuda->nombre_ayuda}";

        // Buscar el deal por nombre y contacto
        $deal = $this->findDealByContactAndName($contactId, $dealName);

        if (! $deal) {
            Log::warning('[HubspotService] No se encontró deal para actualizar en EventContratacionCierreResolucion', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'dealname' => $dealName,
                'contact_id' => $contactId,
            ]);

            return;
        }

        $dealId = $deal['id'] ?? null;
        if (! $dealId) {
            Log::error('[HubspotService] Deal encontrado pero sin ID en EventContratacionCierreResolucion', [
                'deal' => $deal,
            ]);

            return;
        }

        // Actualizar el dealstage en HubSpot a RESOLUCION
        $updateData = [
            'pipeline' => self::OP1_PIPELINE, // OPERATIVA
            'dealstage' => self::OP1_CIERRE, // CIERRE
            'Is Deal Closed?' => true,
        ];

        $updateResult = $this->updateDeal($dealId, $updateData);
        if (! $updateResult['success']) {
            Log::error('[HubspotService] Error al actualizar deal en EventContratacionCierreResolucion', [
                'user_id' => $user->id,
                'ayuda_id' => $ayuda->id,
                'deal_id' => $dealId,
                'error' => $updateResult['error'] ?? 'Error desconocido',
                'status' => $updateResult['status'] ?? null,
            ]);
        }
    }

    /**
     * Procesa el evento EventConcesionRegistrada cuando se registra una concesión.
     * En HubSpot:
     * - OP1: actualiza el deal OPERATIVA a fase Cierre (OP1-Cierre).
     * - OP2: crea o actualiza el negocio en pipeline OP2 en fase Documentación (OP2-Documentacion).
     * - OP4: crea o actualiza el deal en pipeline OP4 en fase Cobrando (OP4-Cobrando).
     * Los estados en BD (OP1-Cierre, OP2-Documentacion, OP4-Cobrando) referencian estas fases en HubSpot.
     */
    protected function procesarConcesionRegistrada($evento): void
    {
        $contratacion = $evento->contratacion ?? null;

        if (! $contratacion) {
            Log::warning('[HubspotService] EventConcesionRegistrada sin contratación', [
                'event' => get_class($evento),
            ]);

            return;
        }

        $user = $contratacion->user;
        $ayuda = $contratacion->ayuda;

        if (! $user || ! $ayuda) {
            Log::error('[HubspotService] No se pudo obtener usuario o ayuda para EventConcesionRegistrada', [
                'contratacion_id' => $contratacion->id,
            ]);

            return;
        }

        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para EventConcesionRegistrada', [
                'user_id' => $user->id,
                'error' => $contactResult['error'] ?? 'Error desconocido',
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];

        // 1) OP1: actualizar deal OPERATIVA a Cierre
        $dealNameOp1 = 'OP1-'.$this->getDealNameSuffix($ayuda);
        $dealOp1 = $this->findDealByContactAndName($contactId, $dealNameOp1);
        if ($dealOp1 && ! empty($dealOp1['id'])) {
            $updateOp1 = [
                'pipeline' => self::OP1_PIPELINE,
                'dealstage' => self::OP1_CIERRE,
            ];
            $resOp1 = $this->updateDeal($dealOp1['id'], $updateOp1);
            if (! $resOp1['success']) {
                Log::error('[HubspotService] EventConcesionRegistrada: error actualizando OP1', [
                    'deal_id' => $dealOp1['id'],
                    'error' => $resOp1['error'] ?? null,
                ]);
            }
        } else {
            Log::warning('[HubspotService] EventConcesionRegistrada: no se encontró deal OP1', [
                'dealname' => $dealNameOp1,
            ]);
        }

        // 2) OP2: negocio en pipeline OP2, fase Documentación
        $dealNameOp2 = 'OP2-'.$this->getDealNameSuffix($ayuda);
        $dealOp2 = $this->findDealByContactAndName($contactId, $dealNameOp2);
        $amount = $contratacion->monto_total_ayuda ?? $contratacion->monto_ayuda_original ?? null;
        if ($dealOp2 && ! empty($dealOp2['id'])) {
            $this->updateDeal($dealOp2['id'], [
                'pipeline' => self::OP2_PIPELINE,
                'dealstage' => self::OP2_DOCUMENTACION,
                'amount' => $amount !== null ? (string) (float) $amount : null,
            ]);
        } else {
            $dealDataOp2 = [
                'dealname' => $dealNameOp2,
                'pipeline' => (string) self::OP2_PIPELINE,
                'dealstage' => (string) self::OP2_DOCUMENTACION,
                'amount' => $amount !== null ? (string) (float) $amount : '',
            ];
            $createOp2 = $this->createDeal($dealDataOp2, $contactId);
            if (! $createOp2['success']) {
                Log::error('[HubspotService] EventConcesionRegistrada: error creando deal OP2', [
                    'error' => $createOp2['error'] ?? null,
                ]);
            }
        }

        // 3) OP4: evento/negocio en pipeline OP4, fase Cobrando
        $dealNameOp4 = 'OP4-'.$this->getDealNameSuffix($ayuda);
        $dealOp4 = $this->findDealByContactAndName($contactId, $dealNameOp4);
        if ($dealOp4 && ! empty($dealOp4['id'])) {
            $this->updateDeal($dealOp4['id'], [
                'pipeline' => self::OP4_PIPELINE,
                'dealstage' => self::OP4_COBRANDO,
            ]);
        } else {
            $dealDataOp4 = [
                'dealname' => $dealNameOp4,
                'pipeline' => (string) self::OP4_PIPELINE,
                'dealstage' => (string) self::OP4_COBRANDO,
                'amount' => $amount !== null ? (string) (float) $amount : '',
            ];
            $createOp4 = $this->createDeal($dealDataOp4, $contactId);
            if (! $createOp4['success']) {
                Log::error('[HubspotService] EventConcesionRegistrada: error creando deal OP4', [
                    'error' => $createOp4['error'] ?? null,
                ]);
            }
        }
    }

    /**
     * Procesa el evento EventPagoRegistrado: actualiza el deal OP4 de Cobrando a Pagando,
     * o a Morosos si la contratación tiene más de un pago con estado_cobro = 'pendiente'.
     */
    protected function procesarPagoRegistrado($evento): void
    {
        $contratacion = $evento->contratacion ?? null;

        if (! $contratacion) {
            Log::warning('[HubspotService] EventPagoRegistrado sin contratación', [
                'event' => get_class($evento),
            ]);

            return;
        }

        $user = $contratacion->user;
        $ayuda = $contratacion->ayuda;

        if (! $user || ! $ayuda) {
            Log::error('[HubspotService] No se pudo obtener usuario o ayuda para EventPagoRegistrado', [
                'contratacion_id' => $contratacion->id,
            ]);

            return;
        }

        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para EventPagoRegistrado', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];
        $dealNameOp4 = 'OP4-'.$this->getDealNameSuffix($ayuda);
        $dealOp4 = $this->findDealByContactAndName($contactId, $dealNameOp4);

        if (! $dealOp4 || empty($dealOp4['id'])) {
            Log::warning('[HubspotService] EventPagoRegistrado: no se encontró deal OP4', [
                'dealname' => $dealNameOp4,
            ]);

            return;
        }

        $pagosPendientes = (int) DB::table('pagos_administracion')
            ->where('contratacion_id', $contratacion->id)
            ->where('estado_cobro', 'pendiente')
            ->count();

        $nuevaFase = $pagosPendientes > 1 ? self::OP4_MOROSOS : self::OP4_PAGANDO;

        $updateResult = $this->updateDeal($dealOp4['id'], [
            'pipeline' => self::OP4_PIPELINE,
            'dealstage' => $nuevaFase,
        ]);

        if (! $updateResult['success']) {
            Log::error('[HubspotService] EventPagoRegistrado: error actualizando deal OP4', [
                'deal_id' => $dealOp4['id'],
                'error' => $updateResult['error'] ?? null,
            ]);
        }
    }

    /**
     * Procesa el evento EventCobroRealizado: actualiza el deal OP4 cuando operativa marca un pago como cobrado.
     * Sincroniza la fase OP4 en HubSpot con el estado actual (OP4-Cobrado u OP4-Cobrando).
     */
    protected function procesarCobroRealizado($evento): void
    {
        $contratacion = $evento->contratacion ?? null;

        if (! $contratacion) {
            Log::warning('[HubspotService] EventCobroRealizado sin contratación', [
                'event' => get_class($evento),
            ]);

            return;
        }

        $contratacion->load('user', 'ayuda', 'estadosContratacion');

        $user = $contratacion->user;
        $ayuda = $contratacion->ayuda;

        if (! $user || ! $ayuda) {
            Log::error('[HubspotService] No se pudo obtener usuario o ayuda para EventCobroRealizado', [
                'contratacion_id' => $contratacion->id,
            ]);

            return;
        }

        $contactData = $this->prepareContactData($user);
        $contactResult = $this->updateContact($contactData);

        if (! $contactResult['success'] || ! isset($contactResult['contact_id'])) {
            Log::error('[HubspotService] No se pudo obtener/crear contacto para EventCobroRealizado', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $contactId = $contactResult['contact_id'];
        $dealNameOp4 = 'OP4-'.$this->getDealNameSuffix($ayuda);
        $dealOp4 = $this->findDealByContactAndName($contactId, $dealNameOp4);

        if (! $dealOp4 || empty($dealOp4['id'])) {
            Log::warning('[HubspotService] EventCobroRealizado: no se encontró deal OP4', [
                'dealname' => $dealNameOp4,
            ]);

            return;
        }

        $codigos = $contratacion->estadosContratacion->pluck('codigo')->all();
        $nuevaFase = in_array('OP4-Cobrado', $codigos, true) ? self::OP4_COBRADO : self::OP4_COBRANDO;

        $updateResult = $this->updateDeal($dealOp4['id'], [
            'pipeline' => self::OP4_PIPELINE,
            'dealstage' => $nuevaFase,
        ]);

        if (! $updateResult['success']) {
            Log::error('[HubspotService] EventCobroRealizado: error actualizando deal OP4', [
                'deal_id' => $dealOp4['id'],
                'error' => $updateResult['error'] ?? null,
            ]);
        }
    }

    // !No se usa en la actualidad
    /**
     * Procesa el evento EventDocumentUploaded
     */
    protected function procesarDocumentUploaded($evento): void
    {
        // TODO: Actualizar propiedades del contacto (ej: documentos_subidos_count)
    }

    // !No se usa en la actualidad
    /**
     * Procesa el evento EventDocumentValidated
     */
    protected function procesarDocumentValidated($evento): void
    {
        // TODO: Actualizar propiedades del contacto (ej: documentos_validados_count)
    }

    // !No se usa en la actualidad
    /**
     * Procesa el evento EventDocumentRejected
     */
    protected function procesarDocumentRejected($evento): void
    {
        // TODO: Registrar evento en HubSpot y actualizar propiedades (ej: documentos_rechazados_count)
    }

    // !No se usa en la actualidad
    /**
     * Procesa el evento EventContratacionStatusChanged
     */
    protected function procesarContratacionStatusChanged($evento): void
    {
        // TODO: Actualizar deal/contacto en HubSpot con el nuevo estado y fase
    }

    // *********************FUNCIONES AUXILIARES************************************************
    /**
     * Devuelve un sufijo único para nombres de deal en HubSpot.
     * codigo_hubspot es nullable; si es null se usa slug o id para evitar colisiones.
     */
    protected function getDealNameSuffix(Ayuda $ayuda): string
    {
        return $ayuda->codigo_hubspot ?? $ayuda->slug ?? (string) $ayuda->id;
    }

    /**
     * Busca un contacto en HubSpot por email
     *
     * @return array|null Retorna los datos del contacto o null si no existe
     */
    protected function getContactByEmail(string $email): ?array
    {
        try {
            $response = $this->request('get', '/crm/v3/objects/contacts/'.urlencode($email), [
                'idProperty' => 'email',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->status() === 404) {
                return null; // Contacto no existe
            }

            Log::warning('[HubspotService] Error al buscar contacto por email', [
                'email' => $email,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('[HubspotService] Excepción al buscar contacto', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Crea un nuevo contacto en HubSpot
     *
     * @param  array  $data  Debe contener al menos 'email' y opcionalmente otras propiedades
     * @return array ['success' => bool, 'contact_id' => string|null, 'error' => string|null]
     */
    public function createContact(array $data): array
    {
        $email = $data['email'] ?? null;
        if (! $email) {
            Log::error('[HubspotService] createContact: email requerido', ['data' => $data]);

            return [
                'success' => false,
                'error' => 'Email es requerido para crear un contacto',
            ];
        }

        // Verificar si el contacto ya existe
        $existing = $this->getContactByEmail($email);
        if ($existing) {
            return $this->updateContact($data);
        }

        $properties = $this->mapContactProperties($data);
        $payload = [
            'properties' => $properties,
        ];

        try {
            $response = $this->request('post', '/crm/v3/objects/contacts', $payload);

            if ($response->successful()) {
                $result = $response->json();
                $contactId = $result['id'] ?? null;

                return [
                    'success' => true,
                    'contact_id' => $contactId,
                ];
            }

            $error = $response->json();
            Log::error('[HubspotService] Error al crear contacto', [
                'email' => $email,
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['message'] ?? 'Error desconocido al crear contacto',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('[HubspotService] Excepción al crear contacto', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Actualiza un contacto existente en HubSpot
     * Si no existe, lo crea
     *
     * @param  array  $data  Debe contener 'email' y otras propiedades a actualizar
     * @return array ['success' => bool, 'contact_id' => string|null, 'error' => string|null]
     */
    public function updateContact(array $data): array
    {
        $email = $data['email'] ?? null;
        if (! $email) {
            Log::error('[HubspotService] updateContact: email requerido', ['data' => $data]);

            return [
                'success' => false,
                'error' => 'Email es requerido para actualizar un contacto',
            ];
        }

        // Buscar contacto existente
        $existing = $this->getContactByEmail($email);

        if (! $existing) {
            return $this->createContact($data);
        }

        $contactId = $existing['id'] ?? null;
        if (! $contactId) {
            Log::error('[HubspotService] Contacto encontrado pero sin ID', [
                'email' => $email,
                'existing' => $existing,
            ]);

            return [
                'success' => false,
                'error' => 'No se pudo obtener el ID del contacto',
            ];
        }

        // Preparar propiedades a actualizar
        $properties = $this->mapContactProperties($data);

        $payload = [
            'properties' => $properties,
        ];

        try {
            $response = $this->request('patch', '/crm/v3/objects/contacts/'.$contactId, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'contact_id' => $contactId,
                ];
            }

            $error = $response->json();
            Log::error('[HubspotService] Error al actualizar contacto', [
                'email' => $email,
                'contact_id' => $contactId,
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['message'] ?? 'Error desconocido al actualizar contacto',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('[HubspotService] Excepción al actualizar contacto', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Crea un nuevo Deal (Negocio) en HubSpot asociado a un contacto
     *
     * @param  array  $data  Propiedades del deal (dealname, amount, dealstage, etc.)
     * @param  string  $contactId  ID del contacto en HubSpot al que asociar el deal
     * @return array ['success' => bool, 'deal_id' => string|null, 'error' => string|null]
     */
    public function createDeal(array $data, string $contactId): array
    {
        $dealName = $data['dealname'] ?? null;
        if (! $dealName) {
            Log::error('[HubspotService] createDeal: dealname requerido', ['data' => $data]);

            return [
                'success' => false,
                'error' => 'dealname es requerido para crear un deal',
            ];
        }

        // Preparar propiedades del deal
        $properties = [];
        $dealProperties = ['dealname', 'amount', 'dealstage', 'pipeline', 'sector', 'ccaa', 'razones_de_no_cualificacion', 'Is Deal Closed?'];

        foreach ($dealProperties as $prop) {
            if (! array_key_exists($prop, $data)) {
                continue;
            }
            $value = $data[$prop];
            if ($value === null || ($value === '' && $prop !== 'Is Deal Closed?')) {
                continue;
            }
            if ($prop === 'Is Deal Closed?' && is_bool($value)) {
                $properties[$prop] = $value ? 'true' : 'false';
            } else {
                $properties[$prop] = (string) $value;
            }
        }

        // Si hay amount, asegurarse de que sea numérico
        if (isset($properties['amount'])) {
            $properties['amount'] = (string) floatval($properties['amount']);
        }

        // Añadir closedate si está disponible (debe ser timestamp en milisegundos)
        if (isset($data['closedate']) && $data['closedate'] !== null) {
            $properties['closedate'] = (string) $data['closedate'];
        }

        $payload = [
            'properties' => $properties,
            'associations' => [
                [
                    'to' => [
                        'id' => $contactId,
                    ],
                    'types' => [
                        [
                            'associationCategory' => 'HUBSPOT_DEFINED',
                            'associationTypeId' => 3, // 3 = Contact to Deal association
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = $this->request('post', '/crm/v3/objects/deals', $payload);

            if ($response->successful()) {
                $result = $response->json();
                $dealId = $result['id'] ?? null;

                return [
                    'success' => true,
                    'deal_id' => $dealId,
                ];
            }

            $error = $response->json();
            Log::error('[HubspotService] Error al crear deal', [
                'dealname' => $dealName,
                'contact_id' => $contactId,
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['message'] ?? 'Error desconocido al crear deal',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('[HubspotService] Excepción al crear deal', [
                'dealname' => $dealName,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Busca un deal existente por contacto y nombre. Y si no existe, crea uno nuevo.
     *
     * @param  string  $contactId  ID del contacto en HubSpot
     * @param  string  $dealName  Nombre del deal
     * @return array|null Retorna el deal si existe, null si no existe
     */
    protected function findDealByContactAndName(string $contactId, string $dealName): ?array
    {
        try {
            // Buscar deals asociados al contacto usando la API de asociaciones v4
            $url = "/crm/v4/objects/contacts/{$contactId}/associations/deals?limit=100";
            $response = $this->request('get', $url);

            if ($response->successful()) {
                $results = $response->json();
                $dealIds = $results['results'] ?? [];

                // Para cada deal asociado, verificar si tiene el mismo nombre
                foreach ($dealIds as $dealAssociation) {
                    $dealId = $dealAssociation['id'] ?? $dealAssociation['toObjectId'] ?? null;
                    if (! $dealId) {
                        continue;
                    }

                    // Obtener el deal completo con propiedades (dealstage para saber la fase)
                    $dealUrl = "/crm/v3/objects/deals/{$dealId}?properties=dealname,dealstage";
                    $dealResponse = $this->request('get', $dealUrl);

                    if ($dealResponse->successful()) {
                        $deal = $dealResponse->json();
                        $properties = $deal['properties'] ?? [];
                        if (($properties['dealname'] ?? '') === $dealName) {
                            return $deal;
                        }
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('[HubspotService] Error al buscar deal existente', [
                'contact_id' => $contactId,
                'dealname' => $dealName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Actualiza un deal existente en HubSpot
     *
     * @param  string  $dealId  ID del deal en HubSpot
     * @param  array  $data  Propiedades a actualizar (pipeline, dealstage, etc.)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function updateDeal(string $dealId, array $data): array
    {
        // Preparar propiedades del deal (incluye custom: producto, comisión, Is Deal Closed?)
        $properties = [];
        $dealProperties = [
            'dealname', 'pipeline', 'dealstage', 'amount', 'sector', 'ccaa', 'razones_de_no_cualificacion',
            'nombre_del_producto_seleccionado', 'comision', 'precio_producto', 'Is Deal Closed?',
        ];

        foreach ($dealProperties as $prop) {
            if (! array_key_exists($prop, $data)) {
                continue;
            }
            $value = $data[$prop];
            if ($value === null || ($value === '' && $prop !== 'Is Deal Closed?')) {
                continue;
            }
            if ($prop === 'Is Deal Closed?' && is_bool($value)) {
                $properties[$prop] = $value ? 'true' : 'false';
            } else {
                $properties[$prop] = (string) $value;
            }
        }

        if (isset($data['closedate']) && $data['closedate'] !== null) {
            $properties['closedate'] = (string) $data['closedate'];
        }

        $payload = [
            'properties' => $properties,
        ];

        try {
            $response = $this->request('patch', "/crm/v3/objects/deals/{$dealId}", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                ];
            }

            $error = $response->json();
            Log::error('[HubspotService] Error al actualizar deal', [
                'deal_id' => $dealId,
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['message'] ?? 'Error desconocido al actualizar deal',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('[HubspotService] Excepción al actualizar deal', [
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepara los datos del contacto desde un User y datos adicionales
     *
     * @param  \App\Models\User  $user
     */
    protected function prepareContactData($user, array $additionalData = []): array
    {
        // Excluir 'id' de los datos adicionales ya que no es una propiedad válida en HubSpot
        $additionalData = array_filter($additionalData, fn ($key) => $key !== 'id', ARRAY_FILTER_USE_KEY);

        $data = array_merge([
            'email' => $user->email,
            'firstname' => $user->nombrePila() ?? $user->name ?? null,
            'lastname' => trim(collect([$user->apellido1 ?? null, $user->apellido2 ?? null])->filter()->implode(' ')) ?: null,
            'phone' => $user->telefono ?? null,
            'external_user_id' => (string) $user->id,
        ], $additionalData);

        // Filtrar valores nulos
        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Mapea los datos del contacto a las propiedades de HubSpot
     */
    protected function mapContactProperties(array $data): array
    {
        $properties = [];

        // Mapeo directo de campos comunes
        $mapping = [
            'email' => 'email',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'phone' => 'phone',
            'external_user_id' => 'external_user_id', // Mapear a la propiedad personalizada creada en HubSpot
            'name' => 'firstname',
            'lifecyclestage' => 'lifecyclestage',
            // Propiedades personalizadas - mapeo desde nombres en mayúsculas a nombres de HubSpot
            'TELEFONO' => 'phone',
            'NOMBRE' => 'firstname',
            'APELLIDOS' => 'lastname',
            'PROVINCIA' => 'provincia',
            'CCAA' => 'ccaa',
        ];

        foreach ($mapping as $sourceKey => $hubspotKey) {
            if (isset($data[$sourceKey]) && $data[$sourceKey] !== null && $data[$sourceKey] !== '') {
                $properties[$hubspotKey] = (string) $data[$sourceKey];
            }
        }

        // Excluir propiedades que no deben enviarse a HubSpot
        $excludedProperties = ['id', 'external_user_id', 'user_id', 'whatsapp', 'ayudas_posibles', 'ciudad'];
        // Añadir propiedades personalizadas si existen
        foreach ($data as $key => $value) {
            if (
                ! isset($mapping[$key]) &&
                ! in_array($key, $excludedProperties) &&
                $value !== null &&
                $value !== ''
            ) {
                $properties[$key] = (string) $value;
            }
        }

        // Mapear valores de provincia y CCAA a los valores que espera HubSpot
        if (isset($properties['provincia']) && ! empty($properties['provincia'])) {
            $properties['provincia'] = $this->mapProvinciaToHubspot($properties['provincia']);
        }

        if (isset($properties['ccaa']) && ! empty($properties['ccaa'])) {
            $properties['ccaa'] = $this->mapCcaaToHubspot($properties['ccaa']);
        }

        return $properties;
    }

    /**
     * Mapea el nombre de provincia de nuestra BD al valor que espera HubSpot
     */
    protected function mapProvinciaToHubspot(string $provincia): string
    {
        $mapping = [
            'Álava' => 'alava',
            'Albacete' => 'albacete',
            'Alicante' => 'alicante',
            'Almería' => 'almeria',
            'Ávila' => 'avila',
            'Badajoz' => 'badajoz',
            'Islas Baleares' => 'islas_baleares',
            'Barcelona' => 'barcelona',
            'Burgos' => 'burgos',
            'Cáceres' => 'caceres',
            'Cádiz' => 'cadiz',
            'Castellón' => 'castellon',
            'Ciudad Real' => 'ciudad_real',
            'Córdoba' => 'cordoba',
            'A Coruña' => 'a_coruna',
            'Cuenca' => 'cuenca',
            'Girona' => 'girona',
            'Granada' => 'granada',
            'Guadalajara' => 'guadalajara',
            'Guipúzcoa' => 'guipuzcoa',
            'Huelva' => 'huelva',
            'Huesca' => 'huesca',
            'Jaén' => 'jaen',
            'León' => 'leon',
            'Lleida' => 'lleida',
            'La Rioja' => 'la_rioja',
            'Lugo' => 'lugo',
            'Madrid' => 'madrid',
            'Málaga' => 'malaga',
            'Murcia' => 'murcia',
            'Navarra' => 'navarra',
            'Ourense' => 'ourense',
            'Asturias' => 'asturias',
            'Palencia' => 'palencia',
            'Las Palmas' => 'las_palmas',
            'Pontevedra' => 'pontevedra',
            'Salamanca' => 'salamanca',
            'Santa Cruz de Tenerife' => 'santa_cruz_de_tenerife',
            'Cantabria' => 'cantabria',
            'Segovia' => 'segovia',
            'Sevilla' => 'sevilla',
            'Soria' => 'soria',
            'Tarragona' => 'tarragona',
            'Teruel' => 'teruel',
            'Toledo' => 'toledo',
            'Valencia' => 'valencia',
            'Valladolid' => 'valladolid',
            'Vizcaya' => 'vizcaya',
            'Zamora' => 'zamora',
            'Zaragoza' => 'zaragoza',
            'Ceuta' => 'ceuta',
            'Melilla' => 'melilla',
        ];

        // Buscar coincidencia exacta (case-insensitive)
        $provinciaLower = mb_strtolower(trim($provincia), 'UTF-8');
        foreach ($mapping as $key => $value) {
            if (mb_strtolower($key, 'UTF-8') === $provinciaLower) {
                return $value;
            }
        }

        // Si no hay coincidencia exacta, intentar normalización genérica como fallback
        $normalized = mb_strtolower($provincia, 'UTF-8');
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $normalized);
        $normalized = str_replace(' ', '_', $normalized);

        Log::warning('[HubspotService] Provincia no encontrada en mapeo, usando normalización genérica', [
            'original' => $provincia,
            'normalized' => $normalized,
        ]);

        return $normalized;
    }

    /**
     * Mapea el nombre de CCAA de nuestra BD al valor que espera HubSpot
     */
    protected function mapCcaaToHubspot(string $ccaa): string
    {
        $mapping = [
            'Andalucía' => 'andalucia',
            'Cataluña' => 'cataluna',
            'Madrid' => 'madrid',
            'Comunidad Valenciana' => 'comunidad_valenciana',
            'Castilla y León' => 'castilla_y_leon',
            'Galicia' => 'galicia',
            'Castilla-La Mancha' => 'castillala_mancha',
            'País Vasco' => 'pais_vasco',
            'Canarias' => 'canarias',
            'Extremadura' => 'extremadura',
            'Aragón' => 'aragon',
            'Murcia' => 'murcia',
            'Baleares' => 'baleares',
            'Cantabria' => 'cantabria',
            'Asturias' => 'asturias',
            'Navarra' => 'navarra',
            'La Rioja' => 'la_rioja',
            'Ceuta' => 'ceuta',
            'Melilla' => 'melilla',
        ];

        // Buscar coincidencia exacta (case-insensitive)
        $ccaaLower = mb_strtolower(trim($ccaa), 'UTF-8');
        foreach ($mapping as $key => $value) {
            if (mb_strtolower($key, 'UTF-8') === $ccaaLower) {
                return $value;
            }
        }

        // Si no hay coincidencia exacta, intentar normalización genérica como fallback
        $normalized = mb_strtolower($ccaa, 'UTF-8');
        $normalized = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $normalized);
        $normalized = str_replace(' ', '_', $normalized);
        // Caso especial: Castilla-La Mancha tiene guión que se convierte en nada
        $normalized = str_replace('-', '', $normalized);

        Log::warning('[HubspotService] CCAA no encontrada en mapeo, usando normalización genérica', [
            'original' => $ccaa,
            'normalized' => $normalized,
        ]);

        return $normalized;
    }

    /**
     * Realiza una petición HTTP a la API de HubSpot, desactivando la verificación SSL en desarrollo local.
     * Porque en windows no funciona el certificado de HubSpot.
     */
    protected function request(string $method, string $endpoint, array $data = [])
    {
        $url = $this->baseUrl.$endpoint;

        // Configurar cliente HTTP
        // En desarrollo local en Windows, desactivar verificación SSL
        // ⚠️ NUNCA usar esto en producción
        $isProduction = app()->environment('production');

        $authHeader = 'Bearer '.$this->apiKey;

        if (! $isProduction) {
            // En desarrollo, desactivar verificación SSL para evitar problemas con certificados
            $client = Http::withoutVerifying()->withHeaders([
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json',
            ]);
        } else {
            // En producción, usar verificación SSL completa
            $client = Http::withHeaders([
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json',
            ]);
        }

        switch (strtolower($method)) {
            case 'post':
                return $client->post($url, $data);
            case 'put':
                return $client->put($url, $data);
            case 'patch':
                return $client->patch($url, $data);
            case 'delete':
                return $client->delete($url);
            case 'get':
                return $client->get($url, $data);
            default:
                throw new \InvalidArgumentException("Método HTTP no soportado: {$method}");
        }
    }
}
