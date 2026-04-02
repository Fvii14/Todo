<?php

namespace App\Http\Controllers;

use App\Models\MotivoRechazo;

class MotivoRechazoController extends Controller
{
    public function index()
    {
        $motivos = MotivoRechazo::orderBy('nombre')->get(['id', 'nombre']);

        return response()->json(['motivos' => $motivos]);
    }
}
