<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionSessionService
{
    private $baseUrl;
    private $token;
    private $instanceName;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.evolution_base_url');
        $this->token = config('whatsapp.evolution_token');
        $this->instanceName = config('whatsapp.instance_name');
    }

    /**
     * Testa a conexão com a Evolution API
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => 'connected',
                    'message' => $data['message'] ?? 'Conexão estabelecida com sucesso',
                    'version' => $data['version'] ?? 'unknown'
                ];
            }

            return [
                'success' => false,
                'status' => 'error',
                'message' => 'Falha na conexão: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Evolution API connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'message' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cria uma nova instância
     */
    public function createInstance($instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/instance/create', [
                'instanceName' => $instanceName,
                'token' => $this->token,
                'qrcode' => true,
                'integration' => 'WHATSAPP-BAILEYS'
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao criar instância: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Evolution instance: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao criar instância: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtém o status da instância
     */
    public function getInstanceStatus($instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/instance/connectionState/' . $instanceName);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'state' => $data['instance']['state'] ?? 'unknown',
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'state' => 'error',
                'message' => 'Falha ao obter status: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get Evolution instance status: ' . $e->getMessage());
            return [
                'success' => false,
                'state' => 'error',
                'message' => 'Erro ao obter status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtém o QR Code da instância
     */
    public function getQrCode($instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/instance/connect/' . $instanceName);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'qrcode' => $data['qrcode'] ?? null,
                    'base64' => $data['base64'] ?? null
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao obter QR Code: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get QR Code: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao obter QR Code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envia uma mensagem de texto
     */
    public function sendTextMessage($number, $message, $instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/message/sendText/' . $instanceName, [
                'number' => $number,
                'text' => $message
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao enviar mensagem: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Configura webhook para a instância
     */
    public function setWebhook($webhookUrl, $instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/webhook/set/' . $instanceName, [
                'url' => $webhookUrl,
                'enabled' => true,
                'events' => [
                    'APPLICATION_STARTUP',
                    'QRCODE_UPDATED',
                    'MESSAGES_UPSERT',
                    'MESSAGES_UPDATE',
                    'MESSAGES_DELETE',
                    'SEND_MESSAGE',
                    'CONTACTS_SET',
                    'CONTACTS_UPSERT',
                    'CONTACTS_UPDATE',
                    'PRESENCE_UPDATE',
                    'CHATS_SET',
                    'CHATS_UPSERT',
                    'CHATS_UPDATE',
                    'CHATS_DELETE',
                    'GROUPS_UPSERT',
                    'GROUP_UPDATE',
                    'GROUP_PARTICIPANTS_UPDATE',
                    'CONNECTION_UPDATE'
                ]
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao configurar webhook: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to set webhook: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao configurar webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Deleta uma instância
     */
    public function deleteInstance($instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->delete($this->baseUrl . '/instance/delete/' . $instanceName);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao deletar instância: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete instance: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao deletar instância: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lista todas as instâncias
     */
    public function listInstances()
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/instance/fetchInstances');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao listar instâncias: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to list instances: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao listar instâncias: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Método de compatibilidade - alias para getInstanceStatus
     */
    public function getSessionStatus($instanceName = null)
    {
        return $this->getInstanceStatus($instanceName);
    }

    /**
     * Método de compatibilidade - reconexão automática
     */
    public function reconnectIfNeeded($instanceName = null)
    {
        $status = $this->getInstanceStatus($instanceName);
        
        if (!$status['success'] || $status['state'] === 'close') {
            // Tentar reconectar
            return $this->getQrCode($instanceName);
        }
        
        return $status;
    }

    /**
     * Força reconexão da instância
     */
    public function forceReconnect($instanceName = null)
    {
        $instanceName = $instanceName ?? $this->instanceName;
        
        try {
            $response = Http::withHeaders([
                'apikey' => $this->token,
                'Content-Type' => 'application/json'
            ])->put($this->baseUrl . '/instance/restart/' . $instanceName);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao reconectar: ' . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to force reconnect: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao reconectar: ' . $e->getMessage()
            ];
        }
    }
}

