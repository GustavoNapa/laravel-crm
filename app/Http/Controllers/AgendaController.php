<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\LeadQuarkions;
use App\Services\GoogleCalendarSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarSyncService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agendamentos = Agenda::with('lead')->orderBy('data', 'desc')->paginate(15);

        if (request()->wantsJson()) {
            return response()->json($agendamentos);
        }

        return view('agenda.index', compact('agendamentos'));
    }

    /**
     * Retorna eventos para o FullCalendar
     */
    public function events(Request $request): JsonResponse
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $query = Agenda::query();

        if ($start && $end) {
            $query->whereBetween('data', [
                Carbon::parse($start)->format('Y-m-d'),
                Carbon::parse($end)->format('Y-m-d'),
            ]);
        }

        $agendamentos = $query->with(['lead'])->get();

        $events = $agendamentos->map(function ($agenda) {
            return [
                'id'            => $agenda->id,
                'title'         => $agenda->titulo ?? ($agenda->lead->nome ?? 'Agendamento'),
                'start'         => $agenda->data.'T'.$agenda->horario,
                'className'     => 'fc-event-'.$agenda->status,
                'extendedProps' => [
                    'status'           => $agenda->status,
                    'observacoes'      => $agenda->observacoes,
                    'lead_id'          => $agenda->lead_id,
                    'sync_with_google' => $agenda->sync_with_google,
                    'google_event_id'  => $agenda->google_event_id,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leads = LeadQuarkions::all();

        return response()->json(['leads' => $leads]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titulo'           => 'nullable|string|max:255',
            'data'             => 'required|date',
            'horario'          => 'required|date_format:H:i',
            'status'           => 'required|in:agendado,confirmado,realizado,cancelado',
            'observacoes'      => 'nullable|string',
            'lead_id'          => 'nullable|exists:leads_quarkions,id',
            'sync_with_google' => 'boolean',
        ]);

        $agenda = Agenda::create([
            'cliente_id'       => 'default', // Ajustar conforme necessário
            'lead_id'          => $request->lead_id,
            'data'             => $request->data,
            'horario'          => $request->horario.':00',
            'status'           => $request->status,
            'observacoes'      => $request->observacoes,
            'titulo'           => $request->titulo,
            'sync_with_google' => $request->boolean('sync_with_google', false),
        ]);

        // Sincronizar com Google Calendar se solicitado
        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento criado com sucesso!',
            'agenda'  => $agenda,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agenda $agenda)
    {
        $agenda->load('lead');

        return response()->json($agenda);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agenda $agenda)
    {
        $leads = LeadQuarkions::all();

        return response()->json(['agenda' => $agenda, 'leads' => $leads]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agenda $agenda): JsonResponse
    {
        $request->validate([
            'titulo'           => 'nullable|string|max:255',
            'data'             => 'required|date',
            'horario'          => 'required|date_format:H:i',
            'status'           => 'required|in:agendado,confirmado,realizado,cancelado',
            'observacoes'      => 'nullable|string',
            'lead_id'          => 'nullable|exists:leads_quarkions,id',
            'sync_with_google' => 'boolean',
        ]);

        $agenda->update([
            'lead_id'          => $request->lead_id,
            'data'             => $request->data,
            'horario'          => $request->horario.':00',
            'status'           => $request->status,
            'observacoes'      => $request->observacoes,
            'titulo'           => $request->titulo,
            'sync_with_google' => $request->boolean('sync_with_google', $agenda->sync_with_google),
        ]);

        // Sincronizar com Google Calendar se solicitado
        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        } elseif ($agenda->google_event_id) {
            // Se não deve mais sincronizar, remover do Google Calendar
            $this->googleCalendarService->deleteFromGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento atualizado com sucesso!',
            'agenda'  => $agenda,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agenda $agenda): JsonResponse
    {
        // Remover do Google Calendar se estiver sincronizado
        if ($agenda->google_event_id) {
            $this->googleCalendarService->deleteFromGoogle($agenda);
        }

        $agenda->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agendamento excluído com sucesso!',
        ]);
    }

    /**
     * Confirmar agendamento
     */
    public function confirmar(Agenda $agenda): JsonResponse
    {
        $agenda->update(['status' => 'confirmado']);

        // Sincronizar com Google Calendar se estiver habilitado
        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento confirmado com sucesso!',
        ]);
    }

    /**
     * Cancelar agendamento
     */
    public function cancelar(Agenda $agenda): JsonResponse
    {
        $agenda->update(['status' => 'cancelado']);

        // Sincronizar com Google Calendar se estiver habilitado
        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento cancelado com sucesso!',
        ]);
    }

    /**
     * Sincronizar com Google Calendar
     */
    public function syncGoogle(): JsonResponse
    {
        try {
            $synced = $this->googleCalendarService->syncPendingEvents();

            return response()->json([
                'success' => true,
                'message' => 'Sincronização concluída com sucesso!',
                'synced'  => $synced,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na sincronização: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Importar eventos do Google Calendar
     */
    public function importGoogle(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $imported = $this->googleCalendarService->importFromGoogle($startDate, $endDate);

            return response()->json([
                'success'  => true,
                'message'  => 'Importação concluída com sucesso!',
                'imported' => $imported,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na importação: '.$e->getMessage(),
            ], 500);
        }
    }
}
