<?php

namespace App\Http\Controllers;

use App\Models\CategoriaManual;
use Illuminate\Http\Request;

class CategoriaManualController extends Controller
{
    public function store(Request $request) 
    {
        $request->validate([
            'nombre_categoria_manual' => 'required|string|max:50|unique:categoria_manuales,nombre_categoria_manual'
        ]);

        CategoriaManual::create($request->all());

        return redirect()->back()->with('success', 'Nueva categoría creada correctamente.');
    }
}
