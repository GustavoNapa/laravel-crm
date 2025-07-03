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
        return view('admin::quarkions.whatsapp.whatsapp-web-paginated');
    }

    /**
     * WhatsApp Web interface original (sem paginação)
     */
    public function whatsappWebOriginal()
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
     * Listar conversas WhatsApp usando Evolution API com paginação otimizada
     */
    public function whatsappConversations(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $cursor = $request->get('cursor'); // Para cursor pagination
            
            // Primeiro tentar buscar da Evolution API
            try {
                $evolutionService = new \App\Services\EvolutionChatService;

                // Buscar chats da Evolution API
                $chats = $evolutionService->findChats();

                // Formatar dados para o frontend
                $allConversations = $evolutionService->formatConversationData($chats);

                // Aplicar filtros se necessário
                if ($search) {
                    $searchLower = strtolower($search);
                    $allConversations = array_filter($allConversations, function ($conv) use ($searchLower) {
                        return str_contains(strtolower($conv['name']), $searchLower) ||
                               str_contains(strtolower($conv['lastMessage']), $searchLower);
                    });
                }

                // Implementar paginação manual com cursor se disponível
                $total = count($allConversations);
                
                // Cursor pagination para melhor performance
                if ($cursor) {
                    // Encontrar índice do cursor
                    $cursorIndex = 0;
                    foreach ($allConversations as $index => $conv) {
                        if (($conv['id'] ?? $conv['remoteJid']) == $cursor) {
                            $cursorIndex = $index + 1;
                            break;
                        }
                    }
                    $conversations = array_slice($allConversations, $cursorIndex, $perPage);
                    $nextCursor = end($conversations)['id'] ?? end($conversations)['remoteJid'] ?? null;
                } else {
                    // Paginação tradicional
                    $offset = ($page - 1) * $perPage;
                    $conversations = array_slice($allConversations, $offset, $perPage);
                    $nextCursor = end($conversations)['id'] ?? end($conversations)['remoteJid'] ?? null;
                }

                return response()->json([
                    'success'       => true,
                    'conversations' => array_values($conversations),
                    'pagination'    => [
                        'current_page' => (int) $page,
                        'per_page'     => (int) $perPage,
                        'total'        => $total,
                        'last_page'    => ceil($total / $perPage),
                        'has_more'     => count($conversations) >= $perPage,
                        'next_cursor'  => $nextCursor
                    ],
                    'total'         => $total,
                ]);
            } catch (\Exception $evolutionError) {
                Log::warning('Evolution API failed, falling back to local data: '.$evolutionError->getMessage());
                
                // Fallback: buscar dados locais com paginação
                $repository = new \App\Repositories\WhatsappConversationRepository();
                $conversations = $repository->getConversations(
                    [
                        'search' => $search,
                        'page' => $page,
                        'cursor' => $cursor
                    ], 
                    $perPage
                );

                $formattedConversations = [];
                foreach ($conversations->items() as $conversation) {
                    $lead = $conversation->lead ?? new \stdClass();
                    $formattedConversations[] = [
                        'id'              => $lead->id ?? $conversation->lead_id,
                        'name'            => $lead->nome ?? 'Sem nome',
                        'avatar'          => null,
                        'lastMessage'     => $conversation->mensagem ?? '',
                        'lastMessageTime' => $conversation->criado_em ? $conversation->criado_em->timestamp : null,
                        'unreadCount'     => 0,
                        'isGroup'         => false,
                        'remoteJid'       => $lead->telefone ?? '',
                    ];
                }

                return response()->json([
                    'success'       => true,
                    'conversations' => $formattedConversations,
                    'pagination'    => [
                        'current_page' => $conversations->currentPage(),
                        'per_page'     => $conversations->perPage(),
                        'total'        => $conversations->total(),
                        'last_page'    => $conversations->lastPage(),
                        'has_more'     => $conversations->hasMorePages(),
                        'next_cursor'  => null
                    ],
                    'total'         => $conversations->total(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp conversations error: '.$e->getMessage());

            return response()->json([
                'success'       => false,
                'message'       => 'Erro ao carregar conversas: '.$e->getMessage(),
                'conversations' => [],
                'pagination'    => [
                    'current_page' => 1,
                    'per_page'     => $perPage,
                    'total'        => 0,
                    'last_page'    => 1,
                    'has_more'     => false,
                    'next_cursor'  => null
                ],
            ], 500);
        }
    }

    /**
     * Obter histórico de uma conversa específica usando Evolution API com paginação otimizada
     */
    public function whatsappConversationHistory($id, Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $cursor = $request->get('cursor');
            $loadOlder = $request->get('load_older', false); // Para carregar mensagens mais antigas
            
            $evolutionService = new \App\Services\EvolutionChatService;

            // Buscar mensagens da conversa com paginação
            $params = [
                'cursor' => $cursor,
                'limit' => $perPage,
                'page' => $page
            ];

            // Se está carregando mensagens mais antigas, ajustar parâmetros
            if ($loadOlder && $cursor) {
                $params['before'] = $cursor;
            }

            $messages = $evolutionService->findMessages($id, $params);

            // Formatar mensagens para o frontend
            $formattedMessages = $evolutionService->formatMessageData($messages);
            
            // Calcular informações de paginação
            $total = count($formattedMessages);
            $hasMore = $total >= $perPage; // Se retornou o máximo, provavelmente há mais
            
            // Próximo cursor para paginação (timestamp da mensagem mais antiga)
            $nextCursor = null;
            if ($hasMore && !empty($formattedMessages)) {
                $lastMessage = end($formattedMessages);
                $nextCursor = $lastMessage['timestamp'] ?? $lastMessage['messageTimestamp'] ?? null;
            }

            return response()->json([
                'success'      => true,
                'messages'     => $formattedMessages,
                'pagination'   => [
                    'current_page' => (int) $page,
                    'per_page'     => (int) $perPage,
                    'has_more'     => $hasMore,
                    'total'        => $total,
                    'next_cursor'  => $nextCursor,
                    'load_older'   => $loadOlder
                ],
                'conversation' => [
                    'id'        => $id,
                    'name'      => $request->get('name', 'Contato'),
                    'remoteJid' => $id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp conversation history error: '.$e->getMessage());

            return response()->json([
                'success'  => false,
                'message'  => 'Erro ao carregar histórico: '.$e->getMessage(),
                'messages' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page'     => $perPage,
                    'has_more'     => false,
                    'total'        => 0,
                    'next_cursor'  => null,
                    'load_older'   => false
                ],
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
            Log::error('WhatsApp send message error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter metadados de conversas (contadores, estatísticas) para performance
     */
    public function whatsappMetadata(Request $request)
    {
        try {
            $stats = [
                'total_conversations' => 0,
                'total_unread' => 0,
                'total_messages_today' => 0,
                'active_chats' => 0
            ];

            // Tentar buscar da Evolution API primeiro
            try {
                $evolutionService = new \App\Services\EvolutionChatService;
                $chats = $evolutionService->findChats();
                
                if ($chats) {
                    $stats['total_conversations'] = count($chats);
                    $stats['active_chats'] = count(array_filter($chats, function($chat) {
                        return isset($chat['lastMessage']) && 
                               time() - ($chat['lastMessage']['messageTimestamp'] ?? 0) < 86400; // 24h
                    }));
                }
            } catch (\Exception $e) {
                Log::warning('Evolution API metadata failed: '.$e->getMessage());
                
                // Fallback local
                $repository = new \App\Repositories\WhatsappConversationRepository();
                $conversations = $repository->getConversations([], 999); // Buscar todos para contar
                $stats['total_conversations'] = $conversations->total();
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp metadata error: '.$e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar metadados',
                'stats' => []
            ], 500);
        }
    }

    /**
     * Obter status da conexão WhatsApp
     */
    public function whatsappGetStatus()
    {
        try {
            $result = $this->evolutionService->getStatus();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erro ao obter status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Testar webhook do WhatsApp
     */
    public function whatsappTestWebhook(Request $request)
    {
        try {
            // Processar webhook de teste
            $data = $request->all();
            Log::info('Teste de webhook WhatsApp:', $data);

            return response()->json([
                'success' => true,
                'message' => 'Webhook testado com sucesso',
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no teste de webhook WhatsApp: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro no teste de webhook: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marcar conversa como lida
     */
    public function whatsappMarkAsRead($id)
    {
        try {
            // Implementar lógica para marcar como lida
            // Por enquanto simular sucesso
            return response()->json([
                'success' => true,
                'message' => 'Conversa marcada como lida'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar como lida: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Atualizar status da conversa
     */
    public function whatsappUpdateStatus($id, Request $request)
    {
        try {
            $status = $request->get('status');
            
            // Implementar lógica para atualizar status
            // Por enquanto simular sucesso
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'status'  => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: '.$e->getMessage(),
            ], 500);
        }
    }
}
