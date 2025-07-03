<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionClient
{
    private $baseUrl;
    private $token;
    private $instanceName;

    public function __construct()
    {
        $this->baseUrl = core()->getConfigData('general.whatsapp.evolution_api.base_url')
            ?? config('whatsapp.evolution_base_url', 'https://evolution-api.com');

        $this->token = core()->getConfigData('general.whatsapp.evolution_api.token')
            ?? config('whatsapp.evolution_token', '');

        $this->instanceName = core()->getConfigData('general.whatsapp.evolution_api.instance_name')
            ?? config('whatsapp.instance_name', 'quarkions_instance');
    }

    /**
     * Buscar URL da foto de perfil de um contato
     * 
     * @param string $number Número do telefone
     * @return array|false
     */
    public function profilePicture($number)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/fetchProfilePictureUrl/' . $this->instanceName, [
                'number' => $this->formatPhoneNumber($number)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Profile picture fetched successfully', [
                    'number' => $number,
                    'response' => $data,
                ]);

                return [
                    'success' => true,
                    'wuid' => $data['wuid'] ?? null,
                    'profilePictureUrl' => $data['profilePictureUrl'] ?? null,
                ];
            } else {
                Log::error('Failed to fetch profile picture', [
                    'number' => $number,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to fetch profile picture: ' . $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Profile picture service error', [
                'error' => $e->getMessage(),
                'number' => $number,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Buscar a última mensagem de uma conversa
     * 
     * @param string $remoteJid ID remoto da conversa
     * @return array|false
     */
    public function lastMessage($remoteJid)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/findMessages/' . $this->instanceName, [
                'where' => [
                    'key' => [
                        'remoteJid' => $remoteJid
                    ]
                ],
                'limit' => 1,
                'sort' => [
                    'messageTimestamp' => 'desc'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Last message fetched successfully', [
                    'remoteJid' => $remoteJid,
                    'response' => $data,
                ]);

                $messages = $data['messages'] ?? [];
                $lastMessage = !empty($messages) ? $messages[0] : null;

                return [
                    'success' => true,
                    'message' => $lastMessage,
                    'text' => $lastMessage ? $this->extractMessageText($lastMessage) : null,
                    'timestamp' => $lastMessage['messageTimestamp'] ?? null,
                    'fromMe' => $lastMessage['key']['fromMe'] ?? false,
                ];
            } else {
                Log::error('Failed to fetch last message', [
                    'remoteJid' => $remoteJid,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to fetch last message: ' . $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Last message service error', [
                'error' => $e->getMessage(),
                'remoteJid' => $remoteJid,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Configurar webhook com eventos específicos
     * 
     * @param string $url URL do webhook
     * @param array $events Lista de eventos
     * @return array|false
     */
    public function setWebhook($url, $events = ['MESSAGE_RECEIVED', 'MESSAGE_ACK'])
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/webhook/set/' . $this->instanceName, [
                'url' => $url,
                'events' => $events,
                'webhook_by_events' => true,
                'webhook_base64' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Webhook configured successfully', [
                    'url' => $url,
                    'events' => $events,
                    'response' => $data,
                ]);

                return [
                    'success' => true,
                    'webhook' => $data['webhook'] ?? null,
                ];
            } else {
                Log::error('Failed to configure webhook', [
                    'url' => $url,
                    'events' => $events,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Failed to configure webhook: ' . $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Webhook configuration error', [
                'error' => $e->getMessage(),
                'url' => $url,
                'events' => $events,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extrair texto da mensagem baseado no tipo
     * 
     * @param array $message
     * @return string|null
     */
    private function extractMessageText($message)
    {
        if (!isset($message['message'])) {
            return null;
        }

        $messageContent = $message['message'];

        // Diferentes tipos de mensagem
        if (isset($messageContent['conversation'])) {
            return $messageContent['conversation'];
        }

        if (isset($messageContent['extendedTextMessage']['text'])) {
            return $messageContent['extendedTextMessage']['text'];
        }

        if (isset($messageContent['imageMessage']['caption'])) {
            return $messageContent['imageMessage']['caption'];
        }

        if (isset($messageContent['videoMessage']['caption'])) {
            return $messageContent['videoMessage']['caption'];
        }

        if (isset($messageContent['documentMessage']['caption'])) {
            return $messageContent['documentMessage']['caption'];
        }

        // Para outros tipos de mensagem
        if (isset($messageContent['imageMessage'])) {
            return '[Imagem]';
        }

        if (isset($messageContent['videoMessage'])) {
            return '[Vídeo]';
        }

        if (isset($messageContent['audioMessage'])) {
            return '[Áudio]';
        }

        if (isset($messageContent['documentMessage'])) {
            return '[Documento]';
        }

        if (isset($messageContent['stickerMessage'])) {
            return '[Sticker]';
        }

        if (isset($messageContent['locationMessage'])) {
            return '[Localização]';
        }

        if (isset($messageContent['contactMessage'])) {
            return '[Contato]';
        }

        return '[Mensagem não suportada]';
    }

    /**
     * Formatar número de telefone
     * 
     * @param string $phone
     * @return string
     */
    private function formatPhoneNumber($phone)
    {
        // Remove todos os caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);

        // Se não começar com código do país, adiciona o código do Brasil
        if (strlen($phone) == 11 && !str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        return $phone;
    }

    /**
     * Obter configurações atuais
     * 
     * @return array
     */
    public function getConfig()
    {
        return [
            'base_url' => $this->baseUrl,
            'instance_name' => $this->instanceName,
            'has_token' => !empty($this->token),
        ];
    }
}

