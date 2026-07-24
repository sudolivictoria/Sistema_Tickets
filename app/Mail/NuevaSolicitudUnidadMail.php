<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaSolicitudUnidadMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    //---------------DECLARACION VARIABLE
    public $ticket;

    //---------------INICIALIZACION VARIABLE
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    //---------------CREAR EL CORREO DE NUEVA SOLICITUD---------------------
    public function build()
    {
        $id = '#TK' . str_pad($this->ticket->id, 5, '0', STR_PAD_LEFT);

        return $this->subject($id . ' - Nueva Solicitud para su Unidad!')
            ->view('emails.nueva-solicitud-unidad');
    }
}
