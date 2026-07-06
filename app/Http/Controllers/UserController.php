<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function toggleStatus(int $id)
    {

        //---busca el usuario por su ID
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.gestion-usuarios')->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        //---cambia el estado del usuario (activo/inactivo)
        $user->activo = !$user->activo;

        //--guarda el cambio en la base de datos
        $user->save();
        return redirect()->route('admin.gestion-usuarios')->with('success', 'El estado del usuario ha sido actualizado.');
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|exists:roles,id',
            'unidad_id' => 'required|exists:unidades,id',
            'cargo' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:15',
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'rol_id.required' => 'Debe seleccionar un rol válido.',
            'unidad_id.required' => 'Debe seleccionar una unidad válida.',
            'cargo.required' => 'El cargo es obligatorio.',
            'telefono.max' => 'El teléfono no puede tener más de 15 caracteres.',
        ]);

        $validated['password'] = Hash::make($request->password);
        $validated['activo'] = 1;

        User::create($validated);

        return redirect()->route('admin.gestion-usuarios')->with('success', 'Usuario creado exitosamente.');
    }



    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'rol_id' => 'required|exists:roles,id',
            'unidad_id' => 'required|exists:unidades,id',
            'cargo' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:15',
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'rol_id.required' => 'Debe seleccionar un rol válido.',
            'unidad_id.required' => 'Debe seleccionar una unidad válida.',
            'cargo.required' => 'El cargo es obligatorio.',
            'telefono.max' => 'El teléfono no puede tener más de 15 caracteres.',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return redirect()->route('admin.gestion-usuarios')->with('success', 'Usuario actualizado.');
    }
}
