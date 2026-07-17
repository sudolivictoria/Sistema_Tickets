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
use Illuminate\Support\Facades\DB;
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
        $unidadRRHH = Unidad::create(['nombre_unidad' => 'Recursos Humanos']);

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

        User::create([
            'name' => 'Leonardo Jonás Álvarez Cruz',
            'email' => 'ljalvarez@istu.gob.sv',
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
            'cargo' => 'Jefe de Unidad',
            'telefono' => '',
        ]);


        User::create([
            'name' => 'Monica Natalia Rodriguez Piche',
            'email' => 'mnrodriguez@istu.gob.sv',
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
            'cargo' => 'Técnico',
            'telefono' => '',
        ]);

        User::create([
            'name' => 'Mario Alberto Ardón Torres',
            'email' => 'matorres@istu.gob.sv',
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
            'cargo' => 'Técnico',
            'telefono' => '',
        ]);

        User::create([
            'name' => 'José Javier Ramírez Gomez',
            'email' => 'jjramirez@istu.gob.sv',
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
            'cargo' => 'Técnico',
            'telefono' => '',
        ]);

        //------Estados
        $abierto = Estado::create(['nombre_estado' => 'abierto']);
        $procesando = Estado::create(['nombre_estado' => 'procesando']);
        $resuelto = Estado::create(['nombre_estado' => 'resuelto']);
        $equivocado = Estado::create(['nombre_estado' => 'equivocado']);
        $noCorresponde = Estado::create(['nombre_estado' => 'no corresponde']);


        //----Prioridades básicas (sin el campo horas_sla directo)
        $critica = Prioridad::create(['nombre_prioridad' => 'Critica']);
        $alta = Prioridad::create(['nombre_prioridad' => 'Alta']);
        $media = Prioridad::create(['nombre_prioridad' => 'Media']);
        $baja = Prioridad::create(['nombre_prioridad' => 'Baja']);


        //----prioridades para cada unidad
        DB::table('prioridad_unidad')->insert([
            ['unidad_id' => $unidadIT->id, 'prioridad_id' => $critica->id, 'horas_sla' => 2],   
            ['unidad_id' => $unidadIT->id, 'prioridad_id' => $alta->id, 'horas_sla' => 6],      
            ['unidad_id' => $unidadIT->id, 'prioridad_id' => $media->id, 'horas_sla' => 12], 
            ['unidad_id' => $unidadIT->id, 'prioridad_id' => $baja->id, 'horas_sla' => 24],     
        ]);

        DB::table('prioridad_unidad')->insert([
            ['unidad_id' => $unidadRRHH->id, 'prioridad_id' => $critica->id, 'horas_sla' => 12], 
            ['unidad_id' => $unidadRRHH->id, 'prioridad_id' => $alta->id, 'horas_sla' => 24],    
            ['unidad_id' => $unidadRRHH->id, 'prioridad_id' => $media->id, 'horas_sla' => 48],    
            ['unidad_id' => $unidadRRHH->id, 'prioridad_id' => $baja->id, 'horas_sla' => 72],   
        ]);

        //----tipos de solicitudes

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'HARDWARE',
            'descripcion_solicitud' => 'Instalación de equipos, prestamos o cambios de equipos o daño de equipos: (Monitor, Teclado, Mouse, Laptop, UPS, Impresoras, Radios, Celulares, Telefonos y Otros Equipos)',
            'ruta_manual' => '',
            'categoria_id' => $categoriaIT->id,
        ]);

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'SOFTWARE',
            'descripcion_solicitud' => 'Instalación y/o configuración de aplicaciones, sistemas o programas o reportes de errores.',
            'ruta_manual' => '',
            'categoria_id' => $categoriaIT->id,
        ]);

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'RED E INFRAESTRUCTURA',
            'descripcion_solicitud' => 'Problemas de conectividad, VPN, internet caído, servidores, firewall/seguridad.',
            'ruta_manual' => '',
            'categoria_id' => $categoriaIT->id,
        ]);

        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'ACCESOS Y/O CREDENCIALES',
            'descripcion_solicitud' => 'Reset de contraseñas, permisos en aplicativos, sistemas o programas, creación de usuarios o cuentas.',
            'ruta_manual' => '',
            'categoria_id' => $categoriaIT->id,
        ]);


        TipoSolicitud::create([
            'nombre_tipo_solicitud' => 'SOLICITUDES DE SERVICIOS',
            'descripcion_solicitud' => 'Impresiones a color, altas / bajas de usuarios, requerimientos de mejora, otras solicitudes.',
            'ruta_manual' => '',
            'categoria_id' => $categoriaIT->id,
        ]);
    }
}
