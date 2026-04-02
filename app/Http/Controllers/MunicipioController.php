<?php

namespace App\Http\Controllers;

use App\Models\Municipio;

class MunicipioController extends Controller
{
    public function getByProvincia($provinciaId)
    {
        $municipios = Municipio::where('provincia_id', $provinciaId)
            ->orderBy('nombre_municipio')
            ->get(['id', 'nombre_municipio']);

        return response()->json($municipios);
    }
}
