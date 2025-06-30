<?php

namespace App\Services;

use App\Models\Agentes;
use App\Models\LeadQuarkions;
use App\Models\HistoricoConversas;
use App\Models\ConfiguracoesCliente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIAgentService
{
    private $openaiApiKey;
    private $elevenlabsApiKey;
    private $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->openaiApiKey = config('whatsapp.openai_api_key');
        $this->elevenlabsApiKey = config('whatsapp.elevenlabs_api_key');
        $this->whatsappService = $whatsappService;
    }

    /**
     * Processar mensagem recebida com agente IA
     */
    public function processMessage($leadId, $message, $agentType = 'isis')
    {
        try {
            $lead = LeadQuarkions::find($leadId);
            if (!$lead) {
                Log::error('Lead not found', ['lead_id' => $leadId]);
                return false;
            }

            $agente = Agentes::where('tipo', $agentType)
                ->where('ativo', true)
                ->first();

            if (!$agente) {
                Log::error('Active agent not found', ['type' => $agentType]);
                return false;
            }

            // Obter contexto da conversa
            $contexto = $this->getConversationContext($leadId);
            
            // Obter configurações do cliente
            $configuracoes = ConfiguracoesCliente::where('cliente_id', $lead->cliente_id)->first();
            
            // Gerar resposta com IA
            $resposta = $this->generateAIResponse($message, $contexto, $agente, $configuracoes);
            
            if ($resposta) {
                // Salvar mensagem no histórico
                $this->saveMessage($leadId, $resposta, 'enviada');
                
                // Enviar via WhatsApp
                $this->whatsappService->sendTextMessage($lead->telefone, $resposta);
                
                // Verificar se precisa de follow-up
                $this->scheduleFollowupIfNeeded($leadId, $agente);
                
                return $resposta;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('AI Agent processing error', [
                'error' => $e->getMessage(),
                'lead_id' => $leadId,
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Gerar resposta com OpenAI
     */
    private function generateAIResponse($message, $contexto, $agente, $configuracoes)
    {
        try {
            $prompt = $this->buildPrompt($message, $contexto, $agente, $configuracoes);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('whatsapp.openai_model', 'gpt-3.5-turbo'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            } else {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('OpenAI generation error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            return null;
        }
    }

    /**
     * Construir prompt para o agente
     */
    private function buildPrompt($message, $contexto, $agente, $configuracoes)
    {
        $basePrompt = $this->getAgentBasePrompt($agente->tipo);
        
        $prompt = $basePrompt . "\n\n";
        
        if ($configuracoes) {
            $prompt .= "CONFIGURAÇÕES PERSONALIZADAS:\n";
            $prompt .= "- Prompt de Qualificação: " . $configuracoes->prompt_qualificacao . "\n";
            $prompt .= "- Mensagem de Boas-vindas: " . $configuracoes->mensagem_boas_vindas . "\n\n";
        }
        
        if (!empty($contexto)) {
            $prompt .= "CONTEXTO DA CONVERSA:\n";
            foreach ($contexto as $msg) {
                $tipo = $msg->tipo == 'enviada' ? 'Você' : 'Cliente';
                $prompt .= "- {$tipo}: {$msg->mensagem}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "INSTRUÇÕES:\n";
        $prompt .= "- Responda de forma natural e humanizada\n";
        $prompt .= "- Use técnicas de SPIN Selling quando apropriado\n";
        $prompt .= "- Mantenha o foco no agendamento de consultas\n";
        $prompt .= "- Seja empático e profissional\n";
        $prompt .= "- Limite a resposta a 2-3 frases\n\n";
        
        return $prompt;
    }

    /**
     * Obter prompt base do agente
     */
    private function getAgentBasePrompt($agentType)
    {
        switch ($agentType) {
            case 'isis':
                return "Você é Isis, uma agente de vendas de alta performance especializada em SPIN Selling. " .
                       "Seu objetivo é qualificar leads e agendar consultas usando técnicas de vendas consultivas. " .
                       "Você é empática, profissional e focada em entender as necessidades do cliente.";
                       
            case 'bruna':
                return "Você é Bruna, especialista em BDR e follow-up automatizado. " .
                       "Seu foco é manter o engajamento com leads através de mensagens variadas e follow-ups estratégicos. " .
                       "Você nunca repete a mesma mensagem e sempre busca converter leads em agendamentos.";
                       
            default:
                return "Você é um agente especialista em atendimento ao cliente e vendas consultivas.";
        }
    }

    /**
     * Obter contexto da conversa
     */
    private function getConversationContext($leadId, $limit = 5)
    {
        return HistoricoConversas::where('lead_id', $leadId)
            ->orderBy('criado_em', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }

    /**
     * Salvar mensagem no histórico
     */
    private function saveMessage($leadId, $message, $tipo)
    {
        $lead = LeadQuarkions::find($leadId);
        
        HistoricoConversas::create([
            'id' => Str::uuid(),
            'cliente_id' => $lead->cliente_id,
            'lead_id' => $leadId,
            'mensagem' => $message,
            'tipo' => $tipo
        ]);
    }

    /**
     * Agendar follow-up se necessário
     */
    private function scheduleFollowupIfNeeded($leadId, $agente)
    {
        // Lógica para determinar se precisa de follow-up
        // Por exemplo, se o lead não respondeu em X horas
        
        $ultimaResposta = HistoricoConversas::where('lead_id', $leadId)
            ->where('tipo', 'recebida')
            ->orderBy('criado_em', 'desc')
            ->first();
            
        if (!$ultimaResposta || $ultimaResposta->criado_em->diffInHours(now()) > 24) {
            // Agendar follow-up para 24h
            $this->scheduleFollowup($leadId, $agente, now()->addHours(24));
        }
    }

    /**
     * Agendar follow-up
     */
    private function scheduleFollowup($leadId, $agente, $agendadoPara)
    {
        // Implementar lógica de agendamento de follow-up
        // Pode usar jobs do Laravel ou sistema de filas
        Log::info('Follow-up scheduled', [
            'lead_id' => $leadId,
            'agent' => $agente->nome,
            'scheduled_for' => $agendadoPara
        ]);
    }

    /**
     * Gerar áudio com ElevenLabs
     */
    public function generateAudio($text, $voiceId = null)
    {
        if (!$this->elevenlabsApiKey) {
            Log::warning('ElevenLabs API key not configured');
            return null;
        }

        try {
            $voiceId = $voiceId ?: config('whatsapp.elevenlabs_voice_id');
            
            $response = Http::withHeaders([
                'xi-api-key' => $this->elevenlabsApiKey,
                'Content-Type' => 'application/json'
            ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => 'eleven_monolingual_v1',
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.5
                ]
            ]);

            if ($response->successful()) {
                // Salvar áudio em storage e retornar URL
                $audioContent = $response->body();
                $filename = 'audio_' . time() . '.mp3';
                $path = storage_path('app/public/audio/' . $filename);
                
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                
                file_put_contents($path, $audioContent);
                
                return asset('storage/audio/' . $filename);
            } else {
                Log::error('ElevenLabs API error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Audio generation error', [
                'error' => $e->getMessage(),
                'text' => $text
            ]);
            return null;
        }
    }
}

