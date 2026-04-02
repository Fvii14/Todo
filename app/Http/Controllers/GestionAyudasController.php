<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Organo;
use App\Models\Questionnaire;

class GestionAyudasController extends Controller
{
    public function index()
    {
        $ayudas = Ayuda::with('organo')->get();
        $organos = Organo::all();
        $questionnaires = Questionnaire::all();

        return view('admin.gestion_ayudas.index', compact('ayudas', 'organos', 'questionnaires'));
    }
}
