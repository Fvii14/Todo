<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSimulationController extends Controller
{
    public function index()
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $users = User::where('is_admin', false)
            ->with(['answers' => function ($query) {
                $query->whereNull('conviviente_id');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $simulatedUserId = session('simulating_user_id');
        $simulatedUser = $simulatedUserId ? User::find($simulatedUserId) : null;
        $search = null;

        return view('admin.user-simulation.index', compact('users', 'simulatedUser', 'search'));
    }

    public function startSimulation(Request $request, $userId)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $user = User::findOrFail($userId);

        if ($user->is_admin) {
            return redirect()->back()->with('error', 'No se puede simular un administrador');
        }

        session([
            'simulating_user_id' => $user->id,
            'original_admin_id' => Auth::user()->id,
            'is_simulating' => true,
        ]);

        Auth::setUser($user);

        return redirect()->route('user.home')->with('success', "Simulando como: {$user->name} ({$user->email})");
    }

    public function stopSimulation()
    {
        $originalAdminId = session('original_admin_id');

        if ($originalAdminId) {
            $originalAdmin = User::find($originalAdminId);
            if ($originalAdmin && $originalAdmin->is_admin) {
                Auth::setUser($originalAdmin);
            }
        }

        session()->forget(['simulating_user_id', 'original_admin_id', 'is_simulating']);

        return redirect()->route('admin.dashboardv2')->with('success', 'Simulación terminada');
    }

    public function search(Request $request)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $search = $request->get('search');

        $users = User::where('is_admin', false)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->with(['answers' => function ($query) {
                $query->whereNull('conviviente_id');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $users->getCollection()->transform(function ($user) {
            $user->answers_count = $user->answers->count();

            return $user;
        });

        return response()->json([
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function status()
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $simulatedUserId = session('simulating_user_id');
        $originalAdminId = session('original_admin_id');

        return response()->json([
            'simulatedUser' => $simulatedUserId ? User::find($simulatedUserId) : null,
            'originalAdmin' => $originalAdminId ? User::find($originalAdminId) : null,
            'isSimulating' => session('is_simulating', false),
        ]);
    }

    public function forceStopSimulation()
    {
        $originalAdminId = session('original_admin_id');

        if ($originalAdminId) {
            $originalAdmin = User::find($originalAdminId);
            if ($originalAdmin && $originalAdmin->is_admin) {
                Auth::setUser($originalAdmin);
            }
        }

        session()->forget(['simulating_user_id', 'original_admin_id', 'is_simulating']);

        return redirect()->route('dashboardv2')->with('success', 'Simulación forzada detenida');
    }
}
