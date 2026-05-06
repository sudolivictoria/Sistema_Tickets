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
        $unidadRRHH = Unidad::create(['nombre_unidad' => 'RRHH']);

        $categoriaIT = Categoria::create(['nombre_categoria' => 'USTS', 'unidad_id' => $unidadIT->id]);
        $categoriaRRHH = Categoria::create(['nombre_categoria' => 'RRHH', 'unidad_id' => $unidadRRHH->id]);

        //-----Usuario Admin de prueba
        User::create([
            'name' => 'Olivia Victoria Quintanilla',
            'email' => 'ovquintanilla@istu.gob.sv',
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
        ]);

        //-----Usuario Gestor de prueba
        User::create([
            'name' => 'Olivia Gestora',
            'email' => 'oliviavictoriaquintanilla@gmail.com',
            'password' => Hash::make('gestor123'),
            'rol_id' => $adminUnidadRol->id,
            'unidad_id' => $unidadRRHH->id,
            'activo' => true,
        ]);

        //-----Usuario Cliente de prueba
        User::create([
            'name' => 'Olivia Victoria Amoss',
            'email' => 'oamossquintanilla@gmail.com',
            'password' => Hash::make('cliente123'),
            'rol_id' => $clienteRol->id,
            'unidad_id' => $unidadRRHH->id,
            'activo' => true,
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
            'nombre_tipo_solicitud' => 'REQUERIMIENTO DE PERSONAL',
            'descripcion_solicitud' => null,
            'categoria_id' => $categoriaRRHH->id,
        ]);
    }
}
