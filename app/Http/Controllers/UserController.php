<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public static function hasContrataciones()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->contrataciones()->exists()) {
            return redirect()->route('user.home')->with('error', 'No tienes contrataciones activas.');
        }

        $contrataciones = $user->contrataciones()
            ->with(['ayuda.recursos', 'producto'])
            ->orderBy('fecha_contratacion', 'desc')
            ->get();

        return view('user.recursos', compact('contrataciones'));
    }

    public function ayudaDetalle($contratacion_id)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $contratacion = $user->contrataciones()
            ->with(['ayuda.recursos', 'ayuda.organo'])
            ->findOrFail($contratacion_id);

        return view('user.ayuda-recurso', compact('contratacion'));
    }

    /*public function deleteUser(User $user)
    {
        if (! auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
        }

        Log::info(Auth::user()->id);
        if (Auth::user()->id != 4 && Auth::user()->id != 1) {
            return response()->json(['success' => false, 'message' => '¡Hey! Solo Pablo y Fran pueden borrar usuarios']);
        }
        $user->delete();

        return response()->json(['success' => true]);
    }*/
}
