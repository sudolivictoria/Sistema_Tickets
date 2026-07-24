<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreadoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    //----------------declaracion variable
    public $ticket;
    //------------------inicializacion
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
          $id = '#TK' . str_pad($this->ticket->id, 5, '0', STR_PAD_LEFT);

        return $this->subject($id . ' - Confirmación: Su ticket ha sido creado')
                    ->view('emails.ticket-creado');

    }
}
