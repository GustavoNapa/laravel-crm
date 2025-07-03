<?php

namespace App\Http\Controllers;

use App\Models\HistoricoConversas;
use App\Models\LeadQuarkions;
use App\Services\AIAgentService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    private $whatsappService;

    private $aiAgentService;

    public function __construct(WhatsAppService $whatsappService, AIAgentService $aiAgentService)
    {
        $this->whatsappService = $whatsappService;
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

    public function sendMessage(Request $request)
    {
        $request->validate([
            'lead_id'  => 'required|exists:leads_quarkions,id',
            'mensagem' => 'required|string',
        ]);

        $lead = LeadQuarkions::find($request->lead_id);

        // Salvar mensagem enviada
        HistoricoConversas::create([
            'id'         => Str::uuid(),
            'cliente_id' => 'default_client',
            'lead_id'    => $request->lead_id,
            'mensagem'   => $request->mensagem,
            'tipo'       => 'enviada',
        ]);

        // Enviar via WhatsApp
        $result = $this->whatsappService->sendTextMessage($lead->telefone, $request->mensagem);

        if ($result) {
            return response()->json(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Erro ao enviar mensagem'], 500);
        }
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        // Processar webhook
        $processed = $this->whatsappService->processWebhook($data);

        if ($processed && isset($data['event']) && $data['event'] === 'messages.upsert') {
            // Processar mensagem com agente IA
            $this->processIncomingMessageWithAI($data);
        }

        return response()->json(['status' => 'ok']);
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
        $result = $this->whatsappService->setWebhook($webhookUrl);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook configurado com sucesso!',
                'data'    => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao configurar webhook',
            ], 500);
        }
    }

    public function getStatus()
    {
        $status = $this->whatsappService->getInstanceStatus();

        return response()->json($status);
    }

    private function processIncomingMessageWithAI($webhookData)
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

                // Salvar mensagem recebida
                HistoricoConversas::create([
                    'id'         => Str::uuid(),
                    'cliente_id' => 'default_client',
                    'lead_id'    => $lead->id,
                    'mensagem'   => $text,
                    'tipo'       => 'recebida',
                ]);

                // Processar com agente IA (com delay para parecer mais humano)
                $delay = config('whatsapp.message_delay', 15);

                // Em produção, usar job com delay
                // ProcessMessageWithAI::dispatch($lead->id, $text)->delay(now()->addSeconds($delay));

                // Por enquanto, processar imediatamente
                sleep($delay);
                $this->aiAgentService->processMessage($lead->id, $text, 'isis');
            }
        }
    }
}
