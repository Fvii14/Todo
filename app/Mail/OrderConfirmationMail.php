<?php

namespace App\Mail;

use App\Models\Contratacion;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contratacion;

    /**
     * Crear una nueva instancia del mensaje.
     *
     * @param  Contratacion  $contratacion  La contratación
     */
    public function __construct(Contratacion $contratacion)
    {
        $this->contratacion = $contratacion;
    }

    /**
     * Obtener el sobre del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de tramitación de ayuda',
        );
    }

    /**
     * Obtener la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        // Tomamos la fecha de nacimiento de UserTaxInfo y entonces calculamos la edad con el carbon
        // $birthdate =$this->contratacion->user->UsertaxInfo->fecha_nacimiento;
        // $edad = $birthdate ? Carbon::parse($birthdate)->age : null;

        // Tomamos el valor de "activo" en la tabla ayudas
        $activo = $this->contratacion->ayuda->activo;
        $view = $activo
            ? 'emails.plantilla-order'       // Si está activa, se le manda este mail
            : 'emails.plantilla-order-inactiva'; // Si está inactiva, se le manda ete mail

        return new Content(
            view: $view,  // Plantilla del correo que se mandará dependiendo de si la ayuda está activa  o no
            with: [
                'name' => $this->contratacion->user->name,  // Nombre del usuario
                'total' => $this->contratacion->producto->price,  // Precio del producto
                'nombre_ayuda' => $this->contratacion->ayuda->nombre_ayuda,  // Nombre de la ayuda
                // 'edad' => $edad, //Edad del usuario
            ]
        );
    }

    /**
     * Obtener los archivos adjuntos para el mensaje.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
