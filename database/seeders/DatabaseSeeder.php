<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
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
        $clienteRol = Rol::create(['nombre_rol' => 'Cliente']);

        //-----Unidades 
        $unidadIT = Unidad::create(['nombre_unidad' => 'USTS']);
        $unidadRRHH = Unidad::create(['nombre_unidad' => 'RRHH']);

        //-----Usuario Admin de prueba
        User::create([
            'nombre_completo' => 'Olivia Victoria Quintanilla',
            'email' => 'ovquintanilla@istu.gob.sv',
            'password' => Hash::make('admin123'), 
            'rol_id' => $adminRol->id,
            'unidad_id' => $unidadIT->id,
            'activo' => true,
        ]);

        //-----Usuario Cliente de prueba
        User::create([
            'nombre_completo' => 'Monica Rodriguez',
            'email' => 'mn.rodriguez@istu.gob.sv',
            'password' => Hash::make('cliente123'),
            'rol_id' => $clienteRol->id,
            'unidad_id' => $unidadRRHH->id,
            'activo' => true,
        ]);
    }
}
