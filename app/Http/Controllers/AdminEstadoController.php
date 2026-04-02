<?php

namespace App\Http\Controllers;

use App\Models\Contratacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminEstadoController extends Controller
{
    public function show($id)
    {
        $contratacion = Contratacion::with(['ayuda', 'producto'])->findOrFail($id);

        // Obtener los valores ENUM posibles del campo estado
        $enumRaw = DB::selectOne("SHOW COLUMNS FROM contrataciones WHERE Field = 'estado'")->Type;
        preg_match('/enum\((.*)\)/', $enumRaw, $matches);
        $estados = isset($matches[1])
            ? array_map(fn ($val) => trim($val, "'"), explode(',', $matches[1]))
            : [];

        return view('admin.actualizar-tramite', compact('contratacion', 'estados'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|string',
        ]);

        $contratacion = Contratacion::with(['user', 'ayuda'])->findOrFail($id);
        $contratacion->estado = $request->input('estado');
        $contratacion->save(); // Usamos Eloquent para actualizar y permitir observers para el email

        return redirect()->route('admin.estado.show', $id)
            ->with('success', 'Estado actualizado correctamente y notificación enviada al usuario.');
    }
}
