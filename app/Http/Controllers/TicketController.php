<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Crear un nuevo ticket desde una página de error
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url_error' => 'required|string|max:500',
            'navegador' => 'required|string|max:100',
            'version_navegador' => 'nullable|string|max:50',
            'so' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'url_error' => $request->url_error,
                'navegador' => $request->navegador,
                'version_navegador' => $request->version_navegador,
                'so' => $request->so,
                'descripcion' => $request->descripcion,
                'estado' => Ticket::ESTADO_PENDIENTE,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket creado correctamente',
                'ticket_id' => $ticket->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el ticket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar la lista de tickets (solo para administradores)
     */
    public function index(Request $request)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $estado = $request->get('estado');
        $query = Ticket::with('user')->latest();

        if ($estado && in_array($estado, [Ticket::ESTADO_PENDIENTE, Ticket::ESTADO_EN_REVISION, Ticket::ESTADO_RESUELTO])) {
            $query->where('estado', $estado);
        }

        $tickets = $query->paginate(20);

        return view('admin.tickets.index', compact('tickets', 'estado'));
    }

    /**
     * Mostrar un ticket específico
     */
    public function show(Ticket $ticket)
    {
        if (! Auth::user()->is_admin && Auth::id() !== $ticket->user_id) {
            abort(403, 'Acceso denegado');
        }

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Actualizar el estado de un ticket
     */
    public function updateEstado(Request $request, Ticket $ticket)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,en_revision,resuelto',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Estado inválido',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $ticket->cambiarEstado($request->estado);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'estado' => $ticket->estado,
                'estado_texto' => $ticket->estado_texto,
                'estado_clase' => $ticket->estado_clase,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un ticket
     */
    public function destroy(Ticket $ticket)
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        try {
            $ticket->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ticket eliminado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el ticket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de tickets
     */
    public function estadisticas()
    {
        if (! Auth::user()->is_admin) {
            abort(403, 'Acceso denegado');
        }

        $estadisticas = [
            'total' => Ticket::count(),
            'pendientes' => Ticket::pendientes()->count(),
            'en_revision' => Ticket::enRevision()->count(),
            'resueltos' => Ticket::resueltos()->count(),
        ];

        return response()->json($estadisticas);
    }
}
