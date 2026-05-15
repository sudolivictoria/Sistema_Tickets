<?php

namespace App\Http\Controllers;

use App\Models\CategoriaManual;
use App\Models\Manual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManualController extends Controller
{
    public function index()
    {
        $categorias = CategoriaManual::all();
        $manuales = Manual::with('categoria')->latest()->get();

        return view('admin.manuales.index', compact('categorias', 'manuales'));
    }

    //--guardar nuevo manual
    public function store(Request $request)

    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria_id' => 'required',
            'archivo' => 'required|file|mimes:pdf,mp4|max:51200',
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/manuales'), $nombreArchivo);
            $path = 'manuales/' . $nombreArchivo;
            Manual::create([
                'titulo' => $request->titulo,
                'archivo_path' => $path,
                'categoria_id' => $request->categoria_id,

            ]);

            return redirect()->back()->with('success', 'Recurso guardado con éxito!');
        }
    }

    //--actualizar manual existente
    public function update(Request $request, $id)
    {
        $manual = Manual::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categoria_manuales,id',
            'archivo' => 'nullable|file|mimes:pdf,mp4|max:51200',
        ]);

        $manual->titulo = $request->titulo;
        $manual->categoria_id = $request->categoria_id;

        if ($request->hasFile('archivo')) {
            if ($manual->archivo_path) {
                //--ruta real fisica del archivo anterior
                $rutaFisicaCajaFuerte = base_path('storage/app/public/' . $manual->archivo_path);

                if (file_exists($rutaFisicaCajaFuerte)) {
                    unlink($rutaFisicaCajaFuerte); //--limpiar el archivo anterior
                }
            }

            //--nuevo archivo
            $file = $request->file('archivo');
            $nombreArchivo = time() . '_' . $file->getClientOriginalName();

            //--mover el nuevo archivo a la carpeta de almacenamiento
            $file->move(storage_path('app/public/manuales'), $nombreArchivo);

            //..actualizar archivo_path en la base
            $manual->archivo_path = 'manuales/' . $nombreArchivo;
        }

        $manual->save();

        return redirect()->back()->with('success', 'Recurso actualizado y archivo viejo eliminado.');
    }

    //--eliminar manual y su archivo
    public function destroy($id)
    {
        $manual = Manual::findOrFail($id);
        $rutaArchivo = storage_path('app/public/' . $manual->archivo_path);
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
        $manual->delete();
        return redirect()->back()->with('success', 'El recurso y su archivo han sido eliminados.');
    }
}
