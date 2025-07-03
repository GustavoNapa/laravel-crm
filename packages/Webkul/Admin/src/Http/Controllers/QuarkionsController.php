<?php

namespace Webkul\Admin\Http\Controllers;

use App\Models\Agenda;
use App\Repositories\WhatsappConversationRepository;
use App\Services\EvolutionSessionService;
use App\Services\GoogleCalendarSyncService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuarkionsController extends Controller
{
    protected $whatsappService;

    protected $googleCalendarService;

    protected $conversationRepository;

    protected $evolutionService;

    public function __construct(
        WhatsAppService $whatsappService,
        GoogleCalendarSyncService $googleCalendarService,
        WhatsappConversationRepository $conversationRepository,
        EvolutionSessionService $evolutionService
    ) {
        $this->whatsappService = $whatsappService;
        $this->googleCalendarService = $googleCalendarService;
        $this->conversationRepository = $conversationRepository;
        $this->evolutionService = $evolutionService;
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
            'titulo'           => 'nullable|string|max:255',
            'data'             => 'required|date',
            'horario'          => 'required|date_format:H:i',
            'status'           => 'required|in:agendado,confirmado,realizado,cancelado',
            'observacoes'      => 'nullable|string',
            'lead_id'          => 'nullable|exists:leads_quarkions,id',
            'sync_with_google' => 'boolean',
        ]);

        $agenda = Agenda::create([
            'cliente_id'       => 'default',
            'lead_id'          => $request->lead_id,
            'data'             => $request->data,
            'horario'          => $request->horario.':00',
            'status'           => $request->status,
            'observacoes'      => $request->observacoes,
            'titulo'           => $request->titulo,
            'sync_with_google' => $request->boolean('sync_with_google', false),
        ]);

        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento criado com sucesso!',
            'agenda'  => $agenda,
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

        if ($agenda->sync_with_google) {
            $this->googleCalendarService->syncToGoogle($agenda);
        } elseif ($agenda->google_event_id) {
            $this->googleCalendarService->deleteFromGoogle($agenda);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento atualizado com sucesso!',
            'agenda'  => $agenda,
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
            'message' => 'Agendamento excluído com sucesso!',
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

    public function agendaSyncGoogle(Request $request)
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

    public function agendaImportGoogle(Request $request)
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

    /**
     * WhatsApp methods
     */
    public function whatsappIndex()
    {
        return view('admin::quarkions.whatsapp.basic-inbox');
    }

    public function whatsappWeb()
    {
        return view('admin::quarkions.whatsapp.whatsapp-web');
    }

    public function whatsappChat($leadId)
    {
        return view('admin::quarkions.whatsapp.chat', ['leadId' => $leadId]);
    }

    public function whatsappQrCode()
    {
        return view('admin::quarkions.whatsapp.qrcode');
    }

    public function whatsappWebhook(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('WhatsApp Webhook recebido:', $data);

            $result = $this->evolutionService->processWebhook($data);

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Erro no webhook WhatsApp: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno',
            ], 500);
        }
    }

    public function whatsappCreateInstance(Request $request)
    {
        try {
            $result = $this->evolutionService->createInstance();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function whatsappSetWebhook(Request $request)
    {
        try {
            $webhookUrl = $request->get('webhook_url');
            $result = $this->evolutionService->setWebhook($webhookUrl);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
        try {
            $agente = \App\Models\Agentes::create($request->all());

            return response()->json(['success' => true, 'agente' => $agente]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
        try {
            $result = $this->evolutionService->testConnection();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro ao testar conexão: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar conversas WhatsApp usando Evolution API
     */
    public function whatsappConversations(Request $request)
    {
        try {
            $evolutionService = new \App\Services\EvolutionChatService;

            // Buscar chats da Evolution API
            $chats = $evolutionService->findChats();

            // Formatar dados para o frontend
            $conversations = $evolutionService->formatConversationData($chats);

            // Aplicar filtros se necessário
            if ($request->get('search')) {
                $search = strtolower($request->get('search'));
                $conversations = array_filter($conversations, function ($conv) use ($search) {
                    return str_contains(strtolower($conv['name']), $search) ||
                           str_contains(strtolower($conv['lastMessage']), $search);
                });
            }

            return response()->json([
                'success'       => true,
                'conversations' => array_values($conversations),
                'total'         => count($conversations),
            ]);
        } catch (\Exception $e) {
            \Log::error('WhatsApp conversations error: '.$e->getMessage());

            return response()->json([
                'success'       => false,
                'message'       => 'Erro ao carregar conversas: '.$e->getMessage(),
                'conversations' => [],
            ], 500);
        }
    }

    /**
     * Obter histórico de uma conversa específica usando Evolution API
     */
    public function whatsappConversationHistory($id, Request $request)
    {
        try {
            $evolutionService = new \App\Services\EvolutionChatService;

            // Buscar mensagens da conversa
            $messages = $evolutionService->findMessages($id, $request->get('cursor'));

            // Formatar mensagens para o frontend
            $formattedMessages = $evolutionService->formatMessageData($messages);

            return response()->json([
                'success'      => true,
                'messages'     => $formattedMessages,
                'conversation' => [
                    'id'        => $id,
                    'name'      => $request->get('name', 'Contato'),
                    'remoteJid' => $id,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('WhatsApp conversation history error: '.$e->getMessage());

            return response()->json([
                'success'  => false,
                'message'  => 'Erro ao carregar histórico: '.$e->getMessage(),
                'messages' => [],
            ], 500);
        }
    }

    /**
     * Obter mensagens de uma conversa específica (alias para whatsappConversationHistory)
     */
    public function whatsappMessages($id, Request $request)
    {
        return $this->whatsappConversationHistory($id, $request);
    }

    /**
     * Enviar mensagem WhatsApp usando Evolution API
     */
    public function whatsappSendMessage(Request $request)
    {
        try {
            $request->validate([
                'remoteJid' => 'required|string',
                'message'   => 'required|string',
            ]);

            $evolutionService = new \App\Services\EvolutionChatService;

            // Enviar mensagem via Evolution API
            $result = $evolutionService->sendMessage(
                $request->get('remoteJid'),
                $request->get('message')
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso',
                    'data'    => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar mensagem',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('WhatsApp send message error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem: '.$e->getMessage(),
            ], 500);
        }
    }
}
