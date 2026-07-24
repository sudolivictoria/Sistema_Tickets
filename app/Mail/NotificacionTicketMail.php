<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    //--------------DECLARACION VARIABLES---------------------------------------------
    public $asuntoCorreo;
    public $titulo;
    public $subtitulo;
    public $ticketCodigo;
    public $contenido;
    public $esResolucion;
    
    public $ticketAsunto;
    public $nombreUsuario;
    public $nombreUnidad;

    //---------------------INICIALIZACION VARIABLES--------------------------------------
    public function __construct(
        $asuntoCorreo, 
        $titulo, 
        $subtitulo, 
        $ticketCodigo, 
        $contenido, 
        $esResolucion = false, 
        $ticketAsunto = null,
        $nombreUsuario = null,
        $nombreUnidad = null
    ) {
        $this->asuntoCorreo = $asuntoCorreo;
        $this->titulo = $titulo;
        $this->subtitulo = $subtitulo;
        $this->ticketCodigo = $ticketCodigo;
        $this->contenido = $contenido;
        $this->esResolucion = $esResolucion;
        $this->ticketAsunto = $ticketAsunto;
        $this->nombreUsuario = $nombreUsuario;
        $this->nombreUnidad = $nombreUnidad;
    }

    //----------------------CREAR LA NOTIFICACION-------------------------------------
    public function build()
    {
        return $this->subject($this->asuntoCorreo)
                    ->view('emails.notificacion-ticket');
    }
}