<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaSolicitudUnidadMail extends Mailable
{
   public $ticket;

   public function __construct($ticket)
   {
       $this->ticket = $ticket;
   }

    public function build()
    {
        return $this->subject('Nueva Solicitud para su Unidad!')
                ->view('emails.nueva-solicitud-unidad');
    }
}
