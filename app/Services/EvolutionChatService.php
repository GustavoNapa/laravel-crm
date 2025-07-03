<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionChatService
{
    private $baseUrl;

    private $apiKey;

    private $instance;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.evolution_base_url');
        $this->apiKey = config('whatsapp.evolution_token');
        $this->instance = config('whatsapp.instance_name');
    }

    /**
     * Buscar todas as conversas/chats
     * POST /chat/findChats/{instance}
     */
    public function findChats()
    {
        try {
            $response = Http::withHeaders([
                'apikey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/findChats/{$this->instance}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Evolution API findChats failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('Evolution API findChats exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Buscar contatos específicos
     * POST /chat/findContacts/{instance}
     */
    public function findContacts($contactId = null)
    {
        try {
            $body = [];
            if ($contactId) {
                $body['where'] = ['id' => $contactId];
            }

            $response = Http::withHeaders([
                'apikey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/findContacts/{$this->instance}", $body);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Evolution API findContacts failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('Evolution API findContacts exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Buscar mensagens de uma conversa
     * POST /chat/findMessages/{instance}
     */
    public function findMessages($remoteJid, $cursor = null, $limit = 25)
    {
        try {
            $body = [
                'where' => [
                    'key' => [
                        'remoteJid' => $remoteJid,
                    ],
                ],
            ];

            // Adicionar cursor para paginação se fornecido
            if ($cursor) {
                $body['cursor'] = $cursor;
            }

            // Adicionar limite
            $body['limit'] = $limit;

            $response = Http::withHeaders([
                'apikey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/findMessages/{$this->instance}", $body);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Evolution API findMessages failed', [
                'status'    => $response->status(),
                'body'      => $response->body(),
                'remoteJid' => $remoteJid,
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('Evolution API findMessages exception', [
                'message'   => $e->getMessage(),
                'remoteJid' => $remoteJid,
            ]);

            return [];
        }
    }

    /**
     * Enviar mensagem
     * POST /message/sendText/{instance}
     */
    public function sendMessage($remoteJid, $message)
    {
        try {
            $body = [
                'number' => $remoteJid,
                'text'   => $message,
            ];

            $response = Http::withHeaders([
                'apikey'       => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/message/sendText/{$this->instance}", $body);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Evolution API sendMessage failed', [
                'status'    => $response->status(),
                'body'      => $response->body(),
                'remoteJid' => $remoteJid,
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Evolution API sendMessage exception', [
                'message'   => $e->getMessage(),
                'remoteJid' => $remoteJid,
            ]);

            return false;
        }
    }

    /**
     * Verificar status da instância
     * GET /instance/connectionState/{instance}
     */
    public function getConnectionStatus()
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'status' => $data['instance']['state'] ?? 'disconnected',
                    'data'   => $data,
                ];
            }

            return ['status' => 'disconnected', 'data' => null];
        } catch (Exception $e) {
            Log::error('Evolution API getConnectionStatus exception', [
                'message' => $e->getMessage(),
            ]);

            return ['status' => 'error', 'data' => null];
        }
    }

    /**
     * Formatar dados de conversa para o frontend
     */
    public function formatConversationData($chats)
    {
        $formatted = [];

        foreach ($chats as $chat) {
            $formatted[] = [
                'id'              => $chat['id'] ?? '',
                'name'            => $chat['name'] ?? $chat['pushName'] ?? 'Sem nome',
                'avatar'          => $chat['profilePictureUrl'] ?? null,
                'lastMessage'     => $chat['lastMessage']['message'] ?? '',
                'lastMessageTime' => $chat['lastMessage']['messageTimestamp'] ?? null,
                'unreadCount'     => $chat['unreadMessages'] ?? 0,
                'isGroup'         => str_contains($chat['id'] ?? '', '@g.us'),
                'remoteJid'       => $chat['id'] ?? '',
            ];
        }

        return $formatted;
    }

    /**
     * Formatar dados de mensagem para o frontend
     */
    public function formatMessageData($messages)
    {
        $formatted = [];

        foreach ($messages as $message) {
            $formatted[] = [
                'id'        => $message['key']['id'] ?? '',
                'remoteJid' => $message['key']['remoteJid'] ?? '',
                'fromMe'    => $message['key']['fromMe'] ?? false,
                'message'   => $message['message']['conversation'] ??
                           $message['message']['extendedTextMessage']['text'] ?? '',
                'timestamp' => $message['messageTimestamp'] ?? null,
                'status'    => $message['status'] ?? 'sent',
                'type'      => $this->getMessageType($message['message'] ?? []),
            ];
        }

        return array_reverse($formatted); // Mais recentes primeiro
    }

    /**
     * Determinar tipo da mensagem
     */
    private function getMessageType($messageContent)
    {
        if (isset($messageContent['conversation'])) {
            return 'text';
        }
        if (isset($messageContent['extendedTextMessage'])) {
            return 'text';
        }
        if (isset($messageContent['imageMessage'])) {
            return 'image';
        }
        if (isset($messageContent['videoMessage'])) {
            return 'video';
        }
        if (isset($messageContent['audioMessage'])) {
            return 'audio';
        }
        if (isset($messageContent['documentMessage'])) {
            return 'document';
        }

        return 'unknown';
    }
}
