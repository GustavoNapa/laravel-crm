<?php

namespace App\Http\Controllers;

use App\Events\MessageCreated;
use App\Models\HistoricoConversas;
use App\Models\LeadQuarkions;
use App\Services\AIAgentService;
use App\Services\EvolutionClient;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    private $whatsappService;

    private $evolutionClient;

    private $aiAgentService;

    public function __construct(WhatsAppService $whatsappService, EvolutionClient $evolutionClient, ?AIAgentService $aiAgentService = null)
    {
        $this->whatsappService = $whatsappService;
        $this->evolutionClient = $evolutionClient;
        $this->aiAgentService = $aiAgentService;
    }

    public function index()
    {
        $conversas = HistoricoConversas::with('lead')
            ->orderBy('criado_em', 'desc')
            ->paginate(20);

        if (request()->wantsJson()) {
            return response()->json($conversas);
        }

        return view('whatsapp.index', compact('conversas'));
    }

    public function chat($leadId)
    {
        $lead = LeadQuarkions::findOrFail($leadId);
        $conversas = HistoricoConversas::where('lead_id', $leadId)
            ->orderBy('criado_em', 'asc')
            ->get();

        return response()->json(['lead' => $lead, 'conversas' => $conversas]);
    }

    /**
     * Obter lista de conversas com informações atualizadas
     */
    public function getConversations()
    {
        try {
            $leads = LeadQuarkions::whereNotNull('telefone')
                ->with(['conversas' => function ($query) {
                    $query->orderBy('criado_em', 'desc')->limit(1);
                }])
                ->orderBy('last_message_timestamp', 'desc')
                ->get();

            $conversations = $leads->map(function ($lead) {
                return [
                    'id'                     => $lead->id,
                    'remoteJid'              => $this->formatRemoteJid($lead->telefone),
                    'name'                   => $lead->nome,
                    'telefone'               => $lead->telefone,
                    'profile_photo'          => $lead->profile_photo,
                    'last_message'           => $lead->last_message,
                    'last_message_timestamp' => $lead->last_message_timestamp,
                    'last_message_from_me'   => $lead->last_message_from_me,
                    'unread_count'           => $lead->unread_count ?? 0,
                    'timestamp'              => $lead->last_message_timestamp ?
                        $lead->last_message_timestamp->timestamp : null,
                ];
            });

            return response()->json([
                'success'       => true,
                'conversations' => $conversations,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching conversations', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter mensagens de uma conversa específica
     */
    public function getMessages($conversationId)
    {
        try {
            $lead = LeadQuarkions::findOrFail($conversationId);

            $messages = HistoricoConversas::where('lead_id', $lead->id)
                ->orderBy('criado_em', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'id'               => $message->id,
                        'body'             => $message->mensagem,
                        'fromMe'           => $message->tipo === 'enviada',
                        'messageTimestamp' => $message->criado_em->timestamp,
                        'message'          => [
                            'conversation' => $message->mensagem,
                        ],
                        'key' => [
                            'fromMe' => $message->tipo === 'enviada',
                        ],
                    ];
                });

            return response()->json([
                'success'  => true,
                'messages' => $messages,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching messages', [
                'conversation_id' => $conversationId,
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'to'      => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            // Buscar ou criar lead baseado no destinatário
            $lead = $this->findOrCreateLeadByPhone($request->to);

            // Salvar mensagem enviada
            $historicoMessage = HistoricoConversas::create([
                'id'         => Str::uuid(),
                'cliente_id' => 'default_client',
                'lead_id'    => $lead->id,
                'mensagem'   => $request->message,
                'tipo'       => 'enviada',
            ]);

            // Atualizar dados da conversa no lead
            $lead->update([
                'last_message'           => $request->message,
                'last_message_timestamp' => now(),
                'last_message_from_me'   => true,
                'unread_count'           => 0, // Zerar não lidas quando enviamos mensagem
            ]);

            // Enviar via WhatsApp
            $result = $this->whatsappService->sendTextMessage($lead->telefone, $request->message);

            if ($result) {
                // Disparar evento de mensagem criada
                event(new MessageCreated($historicoMessage, $lead));

                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem enviada com sucesso!',
                    'data'    => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar mensagem',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'to'      => $request->to,
                'message' => $request->message,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook para receber mensagens da Evolution API
     */
    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('WhatsApp webhook received', ['data' => $data]);

        try {
            // Processar webhook
            $processed = $this->whatsappService->processWebhook($data);

            if ($processed && isset($data['event']) && $data['event'] === 'messages.upsert') {
                // Processar mensagem com lógica personalizada
                $this->processIncomingMessageWithEvents($data);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function qrCode()
    {
        try {
            $maxAttempts = 18; // 3 minutos (18 * 10 segundos)
            $attempts = 0;

            while ($attempts < $maxAttempts) {
                $status = $this->whatsappService->getInstanceStatus();

                if (isset($status['state']) && $status['state'] === 'open') {
                    return response()->json([
                        'success'   => true,
                        'connected' => true,
                        'message'   => 'WhatsApp já está conectado',
                    ]);
                }

                $qrData = $this->whatsappService->getQRCode();

                if ($qrData && isset($qrData['qrcode'])) {
                    return response()->json([
                        'success'   => true,
                        'qrcode'    => $qrData['qrcode'],
                        'connected' => false,
                    ]);
                }

                $attempts++;
                sleep(10); // Aguarda 10 segundos antes de tentar novamente
            }

            // Timeout após 3 minutos
            return response()->json([
                'success' => false,
                'timeout' => true,
                'message' => 'Timeout ao gerar QR Code. Tente novamente.',
            ], 408);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter QR Code: '.$e->getMessage(),
            ], 500);
        }
    }

    public function createInstance()
    {
        $result = $this->whatsappService->createInstance();

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Instância criada com sucesso!',
                'data'    => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar instância',
            ], 500);
        }
    }

    public function setWebhook(Request $request)
    {
        $webhookUrl = $request->input('webhook_url');
        $result = $this->evolutionClient->setWebhook($webhookUrl, ['MESSAGE_RECEIVED', 'MESSAGE_ACK']);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook configurado com sucesso!',
                'data'    => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao configurar webhook: '.$result['error'],
            ], 500);
        }
    }

    public function getStatus()
    {
        $status = $this->whatsappService->getInstanceStatus();

        return response()->json($status);
    }

    /**
     * Processar mensagem recebida com eventos
     */
    private function processIncomingMessageWithEvents($webhookData)
    {
        if (! isset($webhookData['data']['messages']) || empty($webhookData['data']['messages'])) {
            return;
        }

        foreach ($webhookData['data']['messages'] as $message) {
            if ($message['messageType'] === 'conversation' || $message['messageType'] === 'extendedTextMessage') {
                $from = $message['key']['remoteJid'];
                $text = $message['message']['conversation'] ?? $message['message']['extendedTextMessage']['text'] ?? '';

                // Buscar ou criar lead
                $phoneNumber = preg_replace('/\D/', '', $from);
                $lead = $this->findOrCreateLeadByPhone($phoneNumber);

                // Salvar mensagem recebida
                $historicoMessage = HistoricoConversas::create([
                    'id'         => Str::uuid(),
                    'cliente_id' => 'default_client',
                    'lead_id'    => $lead->id,
                    'mensagem'   => $text,
                    'tipo'       => 'recebida',
                ]);

                // Atualizar dados da conversa no lead
                $unreadCount = $lead->unread_count + 1;
                $lead->update([
                    'last_message'           => $text,
                    'last_message_timestamp' => now(),
                    'last_message_from_me'   => false,
                    'unread_count'           => $unreadCount,
                ]);

                // Disparar evento de mensagem criada
                event(new MessageCreated($historicoMessage, $lead));

                // Processar com agente IA se disponível
                if ($this->aiAgentService) {
                    $delay = config('whatsapp.message_delay', 15);
                    sleep($delay);
                    $this->aiAgentService->processMessage($lead->id, $text, 'isis');
                }
            }
        }
    }

    /**
     * Buscar ou criar lead baseado no telefone
     */
    private function findOrCreateLeadByPhone($phone)
    {
        $phoneNumber = preg_replace('/\D/', '', $phone);

        $lead = LeadQuarkions::where('telefone', 'LIKE', '%'.substr($phoneNumber, -11))->first();

        if (! $lead) {
            $lead = LeadQuarkions::create([
                'id'         => Str::uuid(),
                'nome'       => 'Lead WhatsApp',
                'telefone'   => $phoneNumber,
                'status'     => 'novo',
                'origem'     => 'whatsapp',
                'cliente_id' => 'default_client',
            ]);
        }

        return $lead;
    }

    /**
     * Formatar número de telefone para remoteJid
     */
    private function formatRemoteJid($telefone)
    {
        $phone = preg_replace('/\D/', '', $telefone);

        if (strlen($phone) == 11 && ! str_starts_with($phone, '55')) {
            $phone = '55'.$phone;
        }

        return $phone.'@s.whatsapp.net';
    }
}
