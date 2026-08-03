<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Mismo criterio de visibilidad que ComentarioController::autorizarAccesoTicket:
// solo staff (Admin/Gestor), el dueño del ticket o el tecnico asignado.
Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    $esStaff = $user->tieneRol('Admin') || $user->tieneRol('Gestor');
    $esPropietario = $ticket->user_id === $user->id;
    $esTecnicoAsignado = $ticket->tecnico_id !== null && $ticket->tecnico_id === $user->id;

    return $esStaff || $esPropietario || $esTecnicoAsignado;
});
