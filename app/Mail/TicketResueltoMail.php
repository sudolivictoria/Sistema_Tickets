<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketResueltoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $comentarioTexto;

    /**
     * Create a new message instance.
     *
     * @param Ticket $ticket
     * @param string|null $comentarioTexto
     */
    public function __construct(Ticket $ticket, $comentarioTexto = null)
    {
        $this->ticket = $ticket;

        $this->comentarioTexto = $comentarioTexto ?? 'El ticket ha sido marcado como cerrado satisfactoriamente.';

    }

    public function build()
    {
          $id = '#TK' . str_pad($this->ticket->id, 5, '0', STR_PAD_LEFT);

        return $this->subject($id . ' - Su ticket ha sido cerrado')
            ->view('emails.ticket_resuelto');
    }
}
