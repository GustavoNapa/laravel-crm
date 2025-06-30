<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

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
     * Inicia uma sessão do WhatsApp
     */
    public function startSession()
    {
        try {
            // Primeiro verifica se já está conectado
            $currentStatus = $this->getSessionStatus();
            if (isset($currentStatus['state']) && $currentStatus['state'] === 'open') {
                Log::info('Evolution session already connected');
                return ['state' => 'ready', 'message' => 'Already connected'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/sessions/start', [
                'instanceName' => $this->instanceName,
                'qrcode' => true,
                'webhook' => [
                    'url' => url('/api/whatsapp/webhook'),
                    'events' => [
                        'messages.upsert',
                        'connection.update'
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Evolution session started', $data);
                
                // Aguardar até que o status seja 'ready'
                $maxWait = 30; // 30 segundos
                $waited = 0;
                
                while ($waited < $maxWait) {
                    sleep(2);
                    $waited += 2;
                    
                    $status = $this->getSessionStatus();
                    if (isset($status['state']) && $status['state'] === 'open') {
                        $data['state'] = 'ready';
                        break;
                    }
                }
                
                return $data;
            }

            throw new Exception('Failed to start session: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error starting Evolution session: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica o status da sessão
     */
    public function getSessionStatus()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->get($this->baseUrl . '/sessions/' . $this->instanceName . '/status');

            if ($response->successful()) {
                return $response->json();
            }

            return ['state' => 'disconnected'];
        } catch (Exception $e) {
            Log::error('Error getting session status: ' . $e->getMessage());
            return ['state' => 'error'];
        }
    }

    /**
     * Obtém o QR Code para conexão
     */
    public function getQRCode()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->get($this->baseUrl . '/sessions/' . $this->instanceName . '/qrcode');

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Error getting QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reconecta WebSocket se necessário
     */
    public function reconnectIfNeeded()
    {
        $status = $this->getSessionStatus();
        
        if (isset($status['state'])) {
            switch ($status['state']) {
                case 'connecting':
                    // Se está conectando por mais de 15 segundos, reconectar
                    if (isset($status['timestamp'])) {
                        $connectingTime = time() - strtotime($status['timestamp']);
                        if ($connectingTime > 15) {
                            Log::info('Reconnecting Evolution session due to long connecting state');
                            return $this->forceReconnect();
                        }
                    }
                    break;
                    
                case 'close':
                case 'disconnected':
                    Log::info('Reconnecting Evolution session due to disconnected state');
                    return $this->forceReconnect();
                    break;
                    
                case 'open':
                    Log::info('Evolution session is already connected');
                    return ['state' => 'ready', 'message' => 'Already connected'];
                    break;
            }
        }

        return $status;
    }

    /**
     * Força uma reconexão
     */
    private function forceReconnect()
    {
        try {
            // Primeiro tenta parar a sessão atual
            $this->stopSession();
            
            // Aguarda um pouco antes de reconectar
            sleep(3);
            
            // Inicia nova sessão
            return $this->startSession();
        } catch (Exception $e) {
            Log::error('Error forcing reconnection: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Para a sessão atual
     */
    public function stopSession()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->delete($this->baseUrl . '/sessions/' . $this->instanceName);

            if ($response->successful()) {
                Log::info('Evolution session stopped');
                return $response->json();
            }

            Log::warning('Failed to stop session: ' . $response->body());
            return null;
        } catch (Exception $e) {
            Log::error('Error stopping session: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envia uma mensagem
     */
    public function sendMessage($to, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/sessions/' . $this->instanceName . '/messages/text', [
                'number' => $to,
                'text' => $message
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Failed to send message: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Testa a conexão
     */
    public function testConnection()
    {
        try {
            $status = $this->getSessionStatus();
            
            if (isset($status['state']) && $status['state'] === 'open') {
                return [
                    'status' => 'connected',
                    'message' => 'WhatsApp Evolution API is connected and ready'
                ];
            }

            return [
                'status' => 'disconnected',
                'message' => 'WhatsApp Evolution API is not connected',
                'state' => $status['state'] ?? 'unknown'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error testing connection: ' . $e->getMessage()
            ];
        }
    }
}

