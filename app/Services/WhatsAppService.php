<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $baseUrl;

    private $token;

    private $instanceName;

    public function __construct()
    {
        // Usar configurações do Krayin se disponíveis, senão usar config/whatsapp.php
        $this->baseUrl = core()->getConfigData('general.whatsapp.evolution_api.base_url')
            ?? config('whatsapp.evolution_base_url', 'https://evolution-api.com');

        $this->token = core()->getConfigData('general.whatsapp.evolution_api.token')
            ?? config('whatsapp.evolution_token', '');

        $this->instanceName = core()->getConfigData('general.whatsapp.evolution_api.instance_name')
            ?? config('whatsapp.instance_name', 'quarkions_instance');
    }

    /**
     * Enviar mensagem de texto
     */
    public function sendTextMessage($to, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl.'/message/sendText/'.$this->instanceName, [
                'number' => $this->formatPhoneNumber($to),
                'text'   => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to'       => $to,
                    'message'  => $message,
                    'response' => $response->json(),
                ]);

                return $response->json();
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'to'       => $to,
                    'message'  => $message,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', [
                'error'   => $e->getMessage(),
                'to'      => $to,
                'message' => $message,
            ]);

            return false;
        }
    }

    /**
     * Enviar mensagem de áudio
     */
    public function sendAudioMessage($to, $audioUrl)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl.'/message/sendWhatsAppAudio/'.$this->instanceName, [
                'number'   => $this->formatPhoneNumber($to),
                'audioUrl' => $audioUrl,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp audio sent successfully', [
                    'to'       => $to,
                    'audioUrl' => $audioUrl,
                    'response' => $response->json(),
                ]);

                return $response->json();
            } else {
                Log::error('Failed to send WhatsApp audio', [
                    'to'       => $to,
                    'audioUrl' => $audioUrl,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp audio service error', [
                'error'    => $e->getMessage(),
                'to'       => $to,
                'audioUrl' => $audioUrl,
            ]);

            return false;
        }
    }

    /**
     * Obter QR Code para conectar WhatsApp
     */
    public function getQRCode()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])->get($this->baseUrl.'/instance/connect/'.$this->instanceName);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Failed to get QR Code', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('QR Code service error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verificar status da instância
     */
    public function getInstanceStatus()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])->get($this->baseUrl.'/instance/connectionState/'.$this->instanceName);

            if ($response->successful()) {
                return $response->json();
            } else {
                return ['state' => 'disconnected'];
            }
        } catch (\Exception $e) {
            Log::error('Instance status error', [
                'error' => $e->getMessage(),
            ]);

            return ['state' => 'error'];
        }
    }

    /**
     * Criar instância do WhatsApp
     */
    public function createInstance()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl.'/instance/create', [
                'instanceName' => $this->instanceName,
                'token'        => $this->token,
                'qrcode'       => true,
                'webhook'      => config('app.url').'/whatsapp/webhook',
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp instance created successfully', [
                    'instance' => $this->instanceName,
                    'response' => $response->json(),
                ]);

                return $response->json();
            } else {
                Log::error('Failed to create WhatsApp instance', [
                    'instance' => $this->instanceName,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Create instance error', [
                'error'    => $e->getMessage(),
                'instance' => $this->instanceName,
            ]);

            return false;
        }
    }

    /**
     * Configurar webhook
     */
    public function setWebhook($webhookUrl = null)
    {
        $webhookUrl = $webhookUrl ?: config('app.url').'/whatsapp/webhook';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl.'/webhook/set/'.$this->instanceName, [
                'url'    => $webhookUrl,
                'events' => [
                    'APPLICATION_STARTUP',
                    'QRCODE_UPDATED',
                    'MESSAGES_UPSERT',
                    'MESSAGES_UPDATE',
                    'SEND_MESSAGE',
                ],
            ]);

            if ($response->successful()) {
                Log::info('Webhook configured successfully', [
                    'webhook_url' => $webhookUrl,
                    'response'    => $response->json(),
                ]);

                return $response->json();
            } else {
                Log::error('Failed to configure webhook', [
                    'webhook_url' => $webhookUrl,
                    'status'      => $response->status(),
                    'response'    => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Webhook configuration error', [
                'error'       => $e->getMessage(),
                'webhook_url' => $webhookUrl,
            ]);

            return false;
        }
    }

    /**
     * Formatar número de telefone
     */
    private function formatPhoneNumber($phone)
    {
        // Remove todos os caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);

        // Se não começar com código do país, adiciona o código do Brasil
        if (strlen($phone) == 11 && ! str_starts_with($phone, '55')) {
            $phone = '55'.$phone;
        }

        return $phone;
    }

    /**
     * Processar webhook recebido
     */
    public function processWebhook($data)
    {
        try {
            Log::info('Processing WhatsApp webhook', ['data' => $data]);

            if (isset($data['event']) && $data['event'] === 'messages.upsert') {
                return $this->processIncomingMessage($data);
            }

            if (isset($data['event']) && $data['event'] === 'qrcode.updated') {
                return $this->processQRCodeUpdate($data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);

            return false;
        }
    }

    /**
     * Processar mensagem recebida
     */
    private function processIncomingMessage($data)
    {
        if (! isset($data['data']['messages']) || empty($data['data']['messages'])) {
            return false;
        }

        foreach ($data['data']['messages'] as $message) {
            if ($message['messageType'] === 'conversation' || $message['messageType'] === 'extendedTextMessage') {
                $from = $message['key']['remoteJid'];
                $text = $message['message']['conversation'] ?? $message['message']['extendedTextMessage']['text'] ?? '';

                // Processar a mensagem (salvar no banco, acionar agentes IA, etc.)
                $this->handleIncomingMessage($from, $text, $message);
            }
        }

        return true;
    }

    /**
     * Processar atualização do QR Code
     */
    private function processQRCodeUpdate($data)
    {
        if (isset($data['data']['qrcode'])) {
            // Salvar QR Code em cache ou sessão para exibir na interface
            cache(['whatsapp_qrcode' => $data['data']['qrcode']], now()->addMinutes(5));
            Log::info('QR Code updated', ['qrcode_length' => strlen($data['data']['qrcode'])]);
        }

        return true;
    }

    /**
     * Lidar com mensagem recebida
     */
    private function handleIncomingMessage($from, $text, $messageData)
    {
        // Aqui seria implementada a lógica dos agentes IA
        // Por enquanto, apenas log
        Log::info('Incoming WhatsApp message', [
            'from'      => $from,
            'text'      => $text,
            'timestamp' => $messageData['messageTimestamp'] ?? null,
        ]);

        // TODO: Implementar lógica dos agentes IA (Isis, Bruna, etc.)
        // - Identificar ou criar lead
        // - Processar mensagem com IA
        // - Gerar resposta automática
        // - Salvar no histórico de conversas
    }

    /**
     * Testar conexão com a Evolution API
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type'  => 'application/json',
            ])->get($this->baseUrl.'/instance/connectionState/'.$this->instanceName);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Evolution API connection test successful', $data);

                return [
                    'success' => true,
                    'status'  => $data['instance']['state'] ?? 'unknown',
                    'data'    => $data,
                ];
            } else {
                Log::error('Evolution API connection test failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error'   => 'Connection failed: '.$response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Evolution API connection test exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Obter configurações atuais
     */
    public function getConfig()
    {
        return [
            'base_url'      => $this->baseUrl,
            'instance_name' => $this->instanceName,
            'has_token'     => ! empty($this->token),
        ];
    }
}
