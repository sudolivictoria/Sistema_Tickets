<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Prioridad;
use App\Models\User;
use App\Models\Rol;
use App\Models\TipoSolicitud;
use App\Models\Unidad;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //----Roles
        $adminRol = Rol::create(['nombre_rol' => 'Admin']);
        $clienteRol = Rol::create(['nombre_rol' => 'Usuario']);
        $adminUnidadRol = Rol::create(['nombre_rol' => 'Gestor']);

        //-----Unidades 
        $unidadIT = Unidad::create(['nombre_unidad' => 'USTS']);

        $categoriaIT = Categoria::create(['nombre_categoria' => 'USTS', 'unidad_id' => $unidadIT->id]);

        //-----Usuario Admin de prueba
        User::create([
            'name' => 'Olivia Victoria Amoss Quintanilla',
            'email' => 'ovquintanilla@istu.gob.sv',
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
            'cargo' => 'Técnico',
            'telefono' => '7949-9979',
        ]);


          //-----Usuario Admin de prueba
        User::create([
            'name' => 'Cliente Pruebas',
            'email' => 'oamossquintanilla@gmail.com',
            'password' => Hash::make('cliente123'),
            'rol_id' => $clienteRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
            'cargo' => 'Técnico',
            'telefono' => null,
        ]);


        //------estados
        Estado::create([
            'nombre_estado' => 'abierto'
        ]);

        Estado::create([
            'nombre_estado' => 'procesando'
        ]);

        Estado::create([
            'nombre_estado' => 'resuelto'
        ]);

        Estado::create([
            'nombre_estado' => 'equivocado'
        ]);

        Estado::create([
            'nombre_estado' => 'no corresponde'
        ]);


        //----prioridades

        Prioridad::create([
            'nombre_prioridad' => 'Critica'
        ]);

        Prioridad::create([
            'nombre_prioridad' => 'Alta'
        ]);

        Prioridad::create([
            'nombre_prioridad' => 'Media'
        ]);

        Prioridad::create([
            'nombre_prioridad' => 'Baja'
        ]);

        //----tipos de solicitudes

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'HARDWARE',
            'descripcion_solicitud' => 'Instalación de equipos, prestamos o cambios de equipos o daño de equipos: (Monitor, Teclado, Mouse, Laptop, UPS, Impresoras, Radios, Celulares, Telefonos y Otros Equipos)',
            'categoria_id' => $categoriaIT->id,
        ]);

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'SOFTWARE',
            'descripcion_solicitud' => 'Instalación y/o configuración de aplicaciones, sistemas o programas o reportes de errores.',
            'categoria_id' => $categoriaIT->id,
        ]);

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'RED E INFRAESTRUCTURA',
            'descripcion_solicitud' => 'Problemas de conectividad, VPN, internet caído, servidores, firewall/seguridad.',
            'categoria_id' => $categoriaIT->id,
        ]);

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'ACCESOS Y/O CREDENCIALES',
            'descripcion_solicitud' => 'Reset de contraseñas, permisos en aplicativos, sistemas o programas, creación de usuarios o cuentas.',
            'categoria_id' => $categoriaIT->id,
        ]);


        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'SOLICITUDES DE SERVICIOS',
            'descripcion_solicitud' => 'Impresiones a color, altas / bajas de usuarios, requerimientos de mejora, otras solicitudes.',
            'categoria_id' => $categoriaIT->id,
        ]);
    }
}
