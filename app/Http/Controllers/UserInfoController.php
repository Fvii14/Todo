<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmailMail;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// Si usas la regla Unique para el email, también necesitarás:
// use Illuminate\Validation\Rule;

class UserInfoController extends Controller
{
    // ... (tus métodos index y editProfile que ya funcionan)

    public function index()
    {
        // Obtiene el usuario autenticado
        $user = Auth::user();

        // Si no hay usuario autenticado, devuelve un error
        if (! $user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $fieldToQuestionIdMap = [
            'telefono' => 45, // ID de pregunta para teléfono
            'domicilio' => 35, // ID de pregunta para domicilio
            'cuenta_banco' => 88, // ID de pregunta para cuenta bancaria
        ];

        // Obtener solo los IDs de las preguntas que nos interesan
        $questionIdsToFetch = array_values($fieldToQuestionIdMap);

        // Carga las respuestas del usuario para las preguntas específicas, indexadas por question_id
        $userAnswers = Answer::where('user_id', $user->id)
            ->whereNotNull('conviviente_id')
            ->whereIn('question_id', $questionIdsToFetch)
            ->pluck('answer', 'question_id');

        // Prepara los datos del usuario para la respuesta JSON
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'telefono' => $userAnswers->get($fieldToQuestionIdMap['telefono']),
            'domicilio' => $userAnswers->get($fieldToQuestionIdMap['domicilio']),
            'cuenta_banco' => $userAnswers->get($fieldToQuestionIdMap['cuenta_banco']),
        ];

        return response()->json($userData);
    }

    public function editProfile()
    {
        // Obtiene el usuario autenticado
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para editar tu perfil.');
        }

        $fieldToQuestionIdMap = [
            'telefono' => Question::where('slug', 'telefono')->first()->id,
            'domicilio' => Question::where('slug', 'domicilio')->first()->id,
            'cuenta_banco' => Question::where('slug', 'iban')->first()->id,
        ];

        $questionIdsToFetch = array_values($fieldToQuestionIdMap);

        $userAnswers = Answer::where('user_id', $user->id)
            ->whereIn('question_id', $questionIdsToFetch)
            ->pluck('answer', 'question_id');

        $profileData = [
            'telefono' => $userAnswers->get($fieldToQuestionIdMap['telefono']),
            'domicilio' => $userAnswers->get($fieldToQuestionIdMap['domicilio']),
            'cuenta_banco' => $userAnswers->get($fieldToQuestionIdMap['cuenta_banco']),
        ];
        $referredUsers = User::with(['contrataciones.ayuda'])
            ->where('ref_by', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Añadir campo "nombre_real" desde answers con question_id 33
        foreach ($referredUsers as $referredUser) {
            $nombre = Answer::where('user_id', $referredUser->id)
                ->where('question_id', 33)
                ->whereNull('conviviente_id')
                ->value('answer');

            $referredUser->nombre_real = $nombre;
        }

        return view('user.profile-update', [
            'user' => $user,
            'profileData' => $profileData,
            'referredUsers' => $referredUsers,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::id(); // Obtener el ID del usuario para la regla unique

        // Valida los datos de entrada
        $validated = $request->validate([
            // Email: requerido, formato email, y único en la tabla users excepto para el usuario actual.
            'email' => 'required|email|unique:users,email,'.$userId,
            'telefono' => 'nullable|string|max:255', // Es buena práctica añadir un max
            'domicilio' => 'nullable|string|max:255', // Es buena práctica añadir un max
            'cuenta_banco' => 'nullable|string|max:255', // Es buena práctica añadir un max
            'contrasena' => 'nullable|string|min:8|confirmed',
        ]);

        // Obtiene el usuario autenticado
        $user = Auth::user();

        $emailChanged = $user->email !== $validated['email'];

        // Actualiza los campos del usuario
        $user->email = $validated['email'];

        if ($emailChanged) {
            $user->email_verified_at = null;

            try {
                Mail::to($user->email)->send(new VerifyEmailMail($user));
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el correo: '.$e->getMessage());
            }
        }

        if ($request->filled('contrasena')) {
            // Usar Hash::make() para hashear la contraseña
            $user->password = Hash::make($validated['contrasena']);
        }

        $user->save();

        // Mapeo de campos a IDs de pregunta
        $fieldToQuestionIdMap = [
            'telefono' => 45,
            'domicilio' => 35,
            'cuenta_banco' => 88,
        ];

        // Itera sobre los campos y guarda/actualiza las respuestas
        foreach ($fieldToQuestionIdMap as $field_name => $question_id) {
            // Solo procesa si el campo está presente en los datos validados y no es null
            if (isset($validated[$field_name]) && $validated[$field_name] !== null) {
                Answer::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'question_id' => $question_id,
                        'conviviente_id' => null,
                    ],
                    [
                        'answer' => $validated[$field_name],
                    ]
                );
            }
            // Opcional: Si quisieras eliminar la respuesta si el campo viene vacío (actualmente no lo hace)
            // elseif (isset($validated[$field_name]) && $validated[$field_name] === null) {
            //     Answer::where('user_id', $user->id)
            //           ->where('question_id', $question_id)
            //           ->delete();
            // }
        }

        // Redirige a la vista anterior con un mensaje de éxito.
        // Este mensaje se mostrará en tu vista si tienes el código para ello (ej. @if(session('success')))
        return back()->with('success', '¡Perfil actualizado con éxito!');
    }
}
