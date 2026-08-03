<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AdminUnidad\AdminUnidadController;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Prioridad;
use App\Models\Rol;
use App\Models\Ticket;
use App\Models\TipoSolicitud;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresión para el fix de C2: actualizarTecnico/actualizarPrioridad deben
 * releer y bloquear la fila del ticket (lockForUpdate) en vez de confiar en
 * la instancia que entregó el route-model binding, para no pisar cambios
 * hechos por otro usuario entre la lectura y el update.
 *
 * PHPUnit es de un solo hilo, así que no se puede reproducir una carrera real
 * con dos peticiones HTTP simultáneas (la segunda petición siempre re-resuelve
 * el binding ya con los datos frescos). En su lugar, se invoca el método del
 * controlador directamente con una instancia de Ticket "vieja" para simular
 * exactamente la ventana de la carrera: lo que el controlador tenía en mano
 * justo antes de que otro usuario modificara la fila.
 */
class TicketAssignmentConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private array $estados = [];

    private function crearEscenarioBase(): array
    {
        $rolAdmin = Rol::create(['nombre_rol' => 'Admin']);
        $unidad = Unidad::create(['nombre_unidad' => 'USTS']);
        $categoria = Categoria::create(['nombre_categoria' => 'USTS', 'unidad_id' => $unidad->id]);
        $tipoSolicitud = TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'HARDWARE',
            'descripcion_solicitud' => 'Prueba',
            'categoria_id' => $categoria->id,
        ]);
        $prioridad = Prioridad::create(['nombre_prioridad' => 'Alta']);
        $prioridadBaja = Prioridad::create(['nombre_prioridad' => 'Baja']);

        //-----El controlador hardcodea IDs de estado (2 = procesando al asignar, [3,4,5] = cerrados),
        //-----igual que DatabaseSeeder. RefreshDatabase hace rollback por test pero MySQL NO reinicia
        //----AUTO_INCREMENT en un rollback, así que forzamos IDs 1-5 explícitos en cada test para que
        //---coincidan con lo que el controlador espera (si no, la 2da/3da prueba fallaría por FK).
        $crearEstado = function (int $id, string $nombre) {
            $estado = new Estado();
            $estado->forceFill(['id' => $id, 'nombre_estado' => $nombre])->save();
            return $estado;
        };
        $this->estados['abierto'] = $crearEstado(1, 'abierto');
        $this->estados['procesando'] = $crearEstado(2, 'procesando');
        $this->estados['resuelto'] = $crearEstado(3, 'resuelto');
        $this->estados['equivocado'] = $crearEstado(4, 'equivocado');
        $this->estados['no_corresponde'] = $crearEstado(5, 'no corresponde');

        $cliente = User::create([
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@test.com',
            'password' => Hash::make('secret'),
            'rol_id' => $rolAdmin->id,
            'unidad_id' => $unidad->id,
            'activo' => true,
        ]);

        $tecnico = User::create([
            'name' => 'Tecnico de Prueba',
            'email' => 'tecnico@test.com',
            'password' => Hash::make('secret'),
            'rol_id' => $rolAdmin->id,
            'unidad_id' => $unidad->id,
            'activo' => true,
        ]);

        $ticket = Ticket::create([
            'user_id' => $cliente->id,
            'estado_id' => $this->estados['abierto']->id,
            'categoria_id' => $categoria->id,
            'tipo_solicitud_id' => $tipoSolicitud->id,
            'prioridad_id' => $prioridad->id,
            'tecnico_id' => null,
            'asunto' => 'Ticket de prueba de concurrencia',
            'descripcion' => 'Descripción de prueba',
        ]);

        return compact('ticket', 'tecnico', 'prioridadBaja');
    }

    private function construirRequest(int $tecnicoId): Request
    {
        return Request::create(
            '/tickets/actualizar-tecnico',
            'PATCH',
            ['tecnico_id' => $tecnicoId],
            server: ['HTTP_ACCEPT' => 'application/json']
        );
    }

    private function construirRequestPrioridad(int $prioridadId): Request
    {
        return Request::create(
            '/tickets/actualizar-prioridad',
            'PATCH',
            ['prioridad_id' => $prioridadId],
            server: ['HTTP_ACCEPT' => 'application/json']
        );
    }

    public function test_admin_controller_rechaza_asignacion_si_otro_usuario_cerro_el_ticket_concurrentemente(): void
    {
        ['ticket' => $ticket, 'tecnico' => $tecnico] = $this->crearEscenarioBase();

        //----simula lo que el controlador tenía "en mano" (route-model binding) ANTES de la carrera
        $ticketViejo = Ticket::find($ticket->id);

        //---otro usuario cierra el ticket concurrentemente, después de que se leyó $ticketViejo
        $ticket->fresh()->update(['estado_id' => $this->estados['resuelto']->id]);

        $response = (new AdminController())->actualizarTecnico($this->construirRequest($tecnico->id), $ticketViejo);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);

        //----el ticket sigue sin técnico: el intento de asignación NO debe haber sobrescrito el cierre concurrente
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'tecnico_id' => null,
            'estado_id' => $this->estados['resuelto']->id,
        ]);
    }

    public function test_admin_unidad_controller_rechaza_asignacion_si_otro_usuario_cerro_el_ticket_concurrentemente(): void
    {
        ['ticket' => $ticket, 'tecnico' => $tecnico] = $this->crearEscenarioBase();

        $ticketViejo = Ticket::find($ticket->id);

        $ticket->fresh()->update(['estado_id' => $this->estados['equivocado']->id]);

        $response = (new AdminUnidadController())->actualizarTecnico($this->construirRequest($tecnico->id), $ticketViejo);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'tecnico_id' => null,
            'estado_id' => $this->estados['equivocado']->id,
        ]);
    }

    public function test_asignacion_normal_sin_conflicto_sigue_funcionando(): void
    {
        ['ticket' => $ticket, 'tecnico' => $tecnico] = $this->crearEscenarioBase();

        $response = (new AdminController())->actualizarTecnico($this->construirRequest($tecnico->id), $ticket);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'tecnico_id' => $tecnico->id,
            'estado_id' => $this->estados['procesando']->id,
        ]);
    }

    public function test_admin_controller_rechaza_cambio_de_prioridad_si_otro_usuario_cerro_el_ticket_concurrentemente(): void
    {
        ['ticket' => $ticket, 'prioridadBaja' => $prioridadBaja] = $this->crearEscenarioBase();

        //---simula lo que el controlador tenía "en mano" (route-model binding) ANTES de la carrera
        $ticketViejo = Ticket::find($ticket->id);

        //---otro usuario cierra el ticket concurrentemente, después de que se leyó $ticketViejo
        $ticket->fresh()->update(['estado_id' => $this->estados['resuelto']->id]);

        $response = (new AdminController())->actualizarPrioridad($this->construirRequestPrioridad($prioridadBaja->id), $ticketViejo);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);

        //---la prioridad original no debe haber sido sobrescrita tras el cierre concurrente
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'prioridad_id' => $ticket->prioridad_id,
            'estado_id' => $this->estados['resuelto']->id,
        ]);
    }

    public function test_admin_unidad_controller_rechaza_cambio_de_prioridad_si_otro_usuario_cerro_el_ticket_concurrentemente(): void
    {
        ['ticket' => $ticket, 'prioridadBaja' => $prioridadBaja] = $this->crearEscenarioBase();

        $ticketViejo = Ticket::find($ticket->id);

        $ticket->fresh()->update(['estado_id' => $this->estados['no_corresponde']->id]);

        $response = (new AdminUnidadController())->actualizarPrioridad($this->construirRequestPrioridad($prioridadBaja->id), $ticketViejo);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'prioridad_id' => $ticket->prioridad_id,
            'estado_id' => $this->estados['no_corresponde']->id,
        ]);
    }

    public function test_cambio_de_prioridad_normal_sin_conflicto_sigue_funcionando(): void
    {
        ['ticket' => $ticket, 'prioridadBaja' => $prioridadBaja] = $this->crearEscenarioBase();

        $response = (new AdminController())->actualizarPrioridad($this->construirRequestPrioridad($prioridadBaja->id), $ticket);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'prioridad_id' => $prioridadBaja->id,
        ]);
    }
}
