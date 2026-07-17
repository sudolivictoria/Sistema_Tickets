<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    // Declaramos las propiedades explícitas que usará la vista
    public $subjectEmail;
    public $titulo;
    public $subtitulo;
    public $ticketCodigo;
    public $contenido;
    public $esResolucion;

    /**
     * Pasamos los campos directos en lugar de un arreglo genérico
     */
    public function __construct(
        string $subjectEmail, 
        string $titulo, 
        string $subtitulo, 
        string $ticketCodigo, 
        string $contenido, 
        bool $esResolucion = false
    ) {
        $this->subjectEmail = $subjectEmail;
        $this->titulo = $titulo;
        $this->subtitulo = $subtitulo;
        $this->ticketCodigo = $ticketCodigo;
        $this->contenido = $contenido;
        $this->esResolucion = $esResolucion;
    }

    /**
     * El sobre del correo
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectEmail,
        );
    }

    /**
     * Definición del contenido
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notificacion-ticket',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}