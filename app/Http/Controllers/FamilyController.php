<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyController extends Controller
{
    public function showMembers()
    {
        // Obtener al usuario autenticado
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login'); // Si no está autenticado, redirigir al login
        }
        $ref_code_user = $user->ref_code;

        // Obtener todos los miembros de la unidad familiar del usuario autenticado
        $familyMembers = User::where('id_unidad_familiar', $user->id_unidad_familiar)->get();

        // Pasar los miembros a la vista
        return view('user.family_members', compact('familyMembers', 'ref_code_user', 'user'));
    }

    public function updateUnidadFamiliar(Request $request)
    {
        // Asegúrate de que el usuario esté autenticado
        $user = Auth::user();

        if ($user) {
            // Generar un nuevo id_unidad_familiar único y numérico
            do {
                $newUnidadFamiliarId = mt_rand(100000000, 999999999); // Genera un número aleatorio de 9 dígitos
            } while (User::where('id_unidad_familiar', $newUnidadFamiliarId)->exists());

            // Actualizar el id_unidad_familiar
            $user->id_unidad_familiar = $newUnidadFamiliarId;
            $user->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
