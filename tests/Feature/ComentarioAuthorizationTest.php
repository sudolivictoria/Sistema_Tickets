<?php

namespace Tests\Feature;

use App\Http\Controllers\ComentarioController;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresión de seguridad: antes del fix, cualquier usuario autenticado (rol "Usuario")
 * podía leer o comentar en el ticket de OTRO cliente con solo cambiar el ID en la URL
 * (obtenerComentarios/store no verificaban propiedad del ticket). Ver ComentarioController::autorizarAccesoTicket.
 */
class ComentarioAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(): array
    {
        $rolUsuario = Rol::create(['nombre_rol' => 'Usuario']);
        $unidad = Unidad::create(['nombre_unidad' => 'USTS']);
        $categoria = Categoria::create(['nombre_categoria' => 'USTS', 'unidad_id' => $unidad->id]);
        $tipoSolicitud = TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'HARDWARE',
            'descripcion_solicitud' => 'Prueba',
            'categoria_id' => $categoria->id,
        ]);
        $prioridad = Prioridad::create(['nombre_prioridad' => 'Alta']);
        $estadoAbierto = tap(new Estado())->forceFill(['id' => 1, 'nombre_estado' => 'abierto'])->save() ? Estado::first() : null;

        $propietario = User::create([
            'name' => 'Dueño del Ticket', 'email' => 'dueno@test.com', 'password' => Hash::make('secret'),
            'rol_id' => $rolUsuario->id, 'unidad_id' => $unidad->id, 'activo' => true,
        ]);

        $intruso = User::create([
            'name' => 'Otro Cliente', 'email' => 'intruso@test.com', 'password' => Hash::make('secret'),
            'rol_id' => $rolUsuario->id, 'unidad_id' => $unidad->id, 'activo' => true,
        ]);

        $ticket = Ticket::create([
            'user_id' => $propietario->id,
            'estado_id' => $estadoAbierto->id,
            'categoria_id' => $categoria->id,
            'tipo_solicitud_id' => $tipoSolicitud->id,
            'prioridad_id' => $prioridad->id,
            'tecnico_id' => null,
            'asunto' => 'Ticket privado del dueño',
            'descripcion' => 'Descripción de prueba',
        ]);

        return compact('ticket', 'propietario', 'intruso');
    }

    public function test_un_cliente_no_puede_leer_comentarios_de_un_ticket_ajeno(): void
    {
        ['ticket' => $ticket, 'intruso' => $intruso] = $this->crearEscenario();

        Auth::login($intruso);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new ComentarioController())->obtenerComentarios($ticket->id);
    }

    public function test_un_cliente_no_puede_comentar_en_un_ticket_ajeno(): void
    {
        ['ticket' => $ticket, 'intruso' => $intruso] = $this->crearEscenario();

        Auth::login($intruso);
        $request = Request::create("/tickets/{$ticket->id}/comentarios", 'POST', ['contenido' => 'Intento de intrusión']);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new ComentarioController())->store($request, $ticket->id);

        $this->assertDatabaseMissing('comentarios', ['ticket_id' => $ticket->id]);
    }

    public function test_el_dueno_del_ticket_si_puede_leer_sus_propios_comentarios(): void
    {
        ['ticket' => $ticket, 'propietario' => $propietario] = $this->crearEscenario();

        Auth::login($propietario);
        $response = (new ComentarioController())->obtenerComentarios($ticket->id);

        $this->assertSame(200, $response->getStatusCode());
    }
}
