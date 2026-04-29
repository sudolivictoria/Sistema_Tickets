<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function toggleStatus(int $id)
    {

        //---busca el usuario por su ID
        $user = User::findOrFail($id);
        //---cambia el estado del usuario (activo/inactivo)
        $user->activo = !$user->activo;

        //--guarda el cambio en la base de datos
        $user->save();
        return back()->with('success', 'El estado del usuario ha sido actualizado.');
    }

    public function index()
    {
        $usuarios = User::with(['rol', 'unidad'])->get();
        $roles = Rol::all();
        $unidades = Unidad::all();
        return view('admin.gestion-usuarios', compact('usuarios', 'roles', 'unidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'rol_id' => 'required|exists:roles,id',
            'unidad_id' => 'required|exists:unidades,id',
            'cargo' => 'required|string|max:255',
        ]);

        $validated['password'] = bcrypt($request->password);
        $validated['activo'] = 1;

        User::create($validated);

        // En lugar de response()->json, hacemos esto:
        return back()->with('success', 'Usuario creado exitosamente.');
    }



    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'rol_id' => 'required|exists:roles,id',
            'unidad_id' => 'required|exists:unidades,id',
            'cargo' => 'required|string|max:255',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return back()->with('success', 'Usuario actualizado.');
    }
}
