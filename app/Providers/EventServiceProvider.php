<?php

namespace App\Providers;

use App\Events\EventBrevo;
use App\Events\EventCobroRealizado;
use App\Events\EventConcesionRegistrada;
use App\Events\EventContratacionCierreRechazada;
use App\Events\EventContratacionCierreResolucion;
use App\Events\EventContratacionCompleted;
use App\Events\EventHubspot;
use App\Events\EventPagoRegistrado;
use App\Events\EventStatusUpdated;
use App\Events\EventUserContracted;
use App\Events\EventUserIsBeneficiary;
use App\Events\EventUserIsNotBeneficiary;
use App\Events\EventUserRegistered;
use App\Events\EventUserUpdated;
use App\Listeners\ListenerBrevo;
use App\Listeners\ListenerCobroRealizado;
use App\Listeners\ListenerConcesionRegistrada;
use App\Listeners\ListenerContratacionCierreRechazada;
use App\Listeners\ListenerContratacionCierreResolucion;
use App\Listeners\ListenerContratacionCompleted;
use App\Listeners\ListenerCreateUser;
use App\Listeners\ListenerHubspot;
use App\Listeners\ListenerPagoRegistrado;
use App\Listeners\ListenerSendStatusUpdateMail;
use App\Listeners\ListenerUserContracted;
use App\Listeners\ListenerUserIsBeneficiary;
use App\Listeners\ListenerUserIsNotBeneficiary;
use App\Listeners\ListenerUserUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Los eventos y sus listeners.
     *
     * @var array
     */
    protected $listen = [
        EventStatusUpdated::class => [
            ListenerSendStatusUpdateMail::class,
        ],
        EventUserRegistered::class => [
            ListenerCreateUser::class,
        ],
        EventBrevo::class => [
            ListenerBrevo::class,
        ],
        EventHubspot::class => [
            ListenerHubspot::class,
        ],
        EventUserIsBeneficiary::class => [
            ListenerUserIsBeneficiary::class,
        ],
        EventUserIsNotBeneficiary::class => [
            ListenerUserIsNotBeneficiary::class,
        ],
        EventUserContracted::class => [
            ListenerUserContracted::class,
        ],
        EventUserUpdated::class => [
            ListenerUserUpdated::class,
        ],
        EventContratacionCompleted::class => [
            ListenerContratacionCompleted::class,
        ],
        EventContratacionCierreRechazada::class => [
            ListenerContratacionCierreRechazada::class,
        ],
        EventContratacionCierreResolucion::class => [
            ListenerContratacionCierreResolucion::class,
        ],
        EventConcesionRegistrada::class => [
            ListenerConcesionRegistrada::class,
        ],
        EventPagoRegistrado::class => [
            ListenerPagoRegistrado::class,
        ],
        EventCobroRealizado::class => [
            ListenerCobroRealizado::class,
        ],
    ];

    /**
     * Determinar si los eventos y listeners deben ser descubiertos automáticamente.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Deshabilitar descubrimiento automático para evitar duplicados
    }

    /**
     * Ejecutar cualquier servicio de arranque.
     */
    public function boot(): void
    {
        //
    }
}
