<?php

namespace Webkul\Admin\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\LeadQuarkions;
use App\Services\GoogleCalendarSyncService;
use Carbon\Carbon;

class QuarkionsController extends Controller
{
    protected $whatsappService;
    protected $googleCalendarService;

    public function __construct(
        WhatsAppService $whatsappService,
        GoogleCalendarSyncService $googleCalendarService
    ) {
        $this->whatsappService = $whatsappService;
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Agenda methods
     */
    public function agendaIndex()
    {
        return view('admin::quarkions.agenda.index');
    }

    public function agendaCreate()
    {
        return view('admin::quarkions.agenda.create');
    }

    public function agendaStore(Request $request)
    {
        $request->validate([
            'titulo' => 'nullable|string|max:255',
            'data' => 'required|date',
            'horario' => 'required|date_format:H:i',
            'status' => 'required|in:agendado,confirmado,realizado,cancelado',
            'observacoes' => 'nullable|string',
            'lead_id' => 'nullable|exists:leads_quarkions,id',
            'sync_with_google' => 'boolean'
        ]);

        $agenda = Agenda::create([
            'cliente_id' => 'default',
            'lead_id' => $request->lead_id,
            'data' => $request->data,
            'horario' => $request->horario . ':00',
            'status' => $request->status,
            'observacoes' => $request->observacoes,
            'titulo' => $request->titulo,
            'sync_with_google' => $request->boolean('sync_with_google', false),
        ]);

        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento criado com sucesso!',
            'agenda' => $agenda
        ]);
    }

    public function agendaShow($id)
    {
        return view('admin::quarkions.agenda.show', ['id' => $id]);
    }

    public function agendaEdit($id)
    {
        return view('admin::quarkions.agenda.edit', ['id' => $id]);
    }

    public function agendaUpdate(Request $request, $id)
    {
        $agenda = Agenda::findOrFail($id);
        
        $request->validate([
            'titulo' => 'nullable|string|max:255',
            'data' => 'required|date',
            'horario' => 'required|date_format:H:i',
            'status' => 'required|in:agendado,confirmado,realizado,cancelado',
            'observacoes' => 'nullable|string',
            'lead_id' => 'nullable|exists:leads_quarkions,id',
            'sync_with_google' => 'boolean'
        ]);

        $agenda->update([
            'lead_id' => $request->lead_id,
            'data' => $request->data,
            'horario' => $request->horario . ':00',
            'status' => $request->status,
            'observacoes' => $request->observacoes,
            'titulo' => $request->titulo,
            'sync_with_google' => $request->boolean('sync_with_google', $agenda->sync_with_google),
        ]);

        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        } elseif ($agenda->google_event_id) {
            $this->googleCalendarService->deleteFromGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento atualizado com sucesso!',
            'agenda' => $agenda
        ]);
    }

    public function agendaDestroy($id)
    {
        $agenda = Agenda::findOrFail($id);
        
        if ($agenda->google_event_id) {
            $this->googleCalendarService->deleteFromGoogle($agenda);
        }

        $agenda->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agendamento excluído com sucesso!'
        ]);
    }

    public function agendaEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $query = Agenda::query();

        if ($start && $end) {
            $query->whereBetween('data', [
                Carbon::parse($start)->format('Y-m-d'),
                Carbon::parse($end)->format('Y-m-d')
            ]);
        }

        $agendamentos = $query->with(['lead'])->get();

        $events = $agendamentos->map(function ($agenda) {
            return [
                'id' => $agenda->id,
                'title' => $agenda->titulo ?? ($agenda->lead->nome ?? 'Agendamento'),
                'start' => $agenda->data . 'T' . $agenda->horario,
                'className' => 'fc-event-' . $agenda->status,
                'extendedProps' => [
                    'status' => $agenda->status,
                    'observacoes' => $agenda->observacoes,
                    'lead_id' => $agenda->lead_id,
                    'sync_with_google' => $agenda->sync_with_google,
                    'google_event_id' => $agenda->google_event_id,
                ]
            ];
        });

        return response()->json($events);
    }

    public function agendaSyncGoogle(Request $request)
    {
        try {
            $synced = $this->googleCalendarService->syncPendingEvents();
            
            return response()->json([
                'success' => true,
                'message' => 'Sincronização concluída com sucesso!',
                'synced' => $synced
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na sincronização: ' . $e->getMessage()
            ], 500);
        }
    }

    public function agendaImportGoogle(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $imported = $this->googleCalendarService->importFromGoogle($startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'message' => 'Importação concluída com sucesso!',
                'imported' => $imported
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na importação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * WhatsApp methods
     */
    public function whatsappIndex()
    {
        return view('admin::quarkions.whatsapp.index');
    }

    public function whatsappChat($leadId)
    {
        return view('admin::quarkions.whatsapp.chat', ['leadId' => $leadId]);
    }

    public function whatsappSendMessage(Request $request)
    {
        return $this->whatsappController->sendMessage($request);
    }

    public function whatsappQrCode()
    {
        return view('admin::quarkions.whatsapp.qrcode');
    }

    public function whatsappWebhook(Request $request)
    {
        return $this->whatsappController->webhook($request);
    }

    public function whatsappCreateInstance(Request $request)
    {
        return $this->whatsappController->createInstance($request);
    }

    public function whatsappSetWebhook(Request $request)
    {
        return $this->whatsappController->setWebhook($request);
    }

    public function whatsappGetStatus()
    {
        return $this->whatsappController->getStatus();
    }

    /**
     * Agentes IA methods
     */
    public function agentesIndex()
    {
        return view('admin::quarkions.agentes.index');
    }

    public function agentesCreate()
    {
        return view('admin::quarkions.agentes.create');
    }

    public function agentesStore(Request $request)
    {
        return $this->agentesController->store($request);
    }

    public function agentesDashboard()
    {
        return view('admin::quarkions.agentes.dashboard');
    }

    public function agentesShow($id)
    {
        return view('admin::quarkions.agentes.show', ['id' => $id]);
    }

    public function agentesEdit($id)
    {
        return view('admin::quarkions.agentes.edit', ['id' => $id]);
    }

    public function agentesUpdate(Request $request, $id)
    {
        return $this->agentesController->update($request, $id);
    }

    public function agentesDestroy($id)
    {
        return $this->agentesController->destroy($id);
    }

    /**
     * WhatsApp Configuration
     */
    public function whatsappConfiguration()
    {
        return view('admin::quarkions.whatsapp.configuration');
    }

    /**
     * Testar conexão WhatsApp Evolution API
     */
    public function whatsappTestConnection()
    {
        $result = $this->whatsappService->testConnection();
        return response()->json($result);
    }
}
