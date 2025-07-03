<?php

namespace App\Jobs;

use App\Models\HistoricoConversas;
use App\Models\LeadQuarkions;
use App\Services\EvolutionClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncConversationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting conversation sync job');

        try {
            $evolutionClient = new EvolutionClient();

            // Buscar todos os leads que têm telefone
            $leads = LeadQuarkions::whereNotNull('telefone')
                ->limit(100) // Processar em lotes
                ->get();

            Log::info('Found leads to sync conversations', ['count' => $leads->count()]);

            foreach ($leads as $lead) {
                $this->syncConversationData($lead, $evolutionClient);
                
                // Pequeno delay para evitar rate limiting
                sleep(1);
            }

            Log::info('Conversation sync job completed successfully');

        } catch (\Exception $e) {
            Log::error('Conversation sync job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Sincronizar dados da conversa para um lead
     */
    private function syncConversationData(LeadQuarkions $lead, EvolutionClient $evolutionClient): void
    {
        try {
            Log::info('Syncing conversation data for lead', [
                'lead_id' => $lead->id,
                'telefone' => $lead->telefone,
            ]);

            // Formatar o remoteJid (número + @s.whatsapp.net)
            $remoteJid = $this->formatRemoteJid($lead->telefone);

            // Buscar última mensagem via Evolution API
            $result = $evolutionClient->lastMessage($remoteJid);

            if ($result['success'] && !empty($result['message'])) {
                $lastMessageText = $result['text'];
                $lastMessageTimestamp = $result['timestamp'];
                $fromMe = $result['fromMe'];

                // Atualizar dados da conversa no lead
                $lead->update([
                    'last_message' => $lastMessageText,
                    'last_message_timestamp' => $lastMessageTimestamp ? 
                        \Carbon\Carbon::createFromTimestamp($lastMessageTimestamp) : null,
                    'last_message_from_me' => $fromMe,
                ]);

                // Calcular contagem de mensagens não lidas
                $unreadCount = $this->calculateUnreadCount($lead, $lastMessageTimestamp, $fromMe);
                
                $lead->update([
                    'unread_count' => $unreadCount,
                ]);

                Log::info('Conversation data updated successfully', [
                    'lead_id' => $lead->id,
                    'last_message' => $lastMessageText,
                    'unread_count' => $unreadCount,
                ]);

            } else {
                Log::warning('No messages found for lead', [
                    'lead_id' => $lead->id,
                    'telefone' => $lead->telefone,
                    'remote_jid' => $remoteJid,
                    'error' => $result['error'] ?? 'No messages',
                ]);

                // Zerar contadores se não há mensagens
                $lead->update([
                    'last_message' => null,
                    'last_message_timestamp' => null,
                    'last_message_from_me' => false,
                    'unread_count' => 0,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing conversation data for lead', [
                'lead_id' => $lead->id,
                'telefone' => $lead->telefone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calcular contagem de mensagens não lidas
     */
    private function calculateUnreadCount(LeadQuarkions $lead, ?int $lastMessageTimestamp, bool $fromMe): int
    {
        try {
            // Se a última mensagem foi enviada por nós, não há mensagens não lidas
            if ($fromMe) {
                return 0;
            }

            // Contar mensagens recebidas após a última mensagem que enviamos
            $lastSentMessage = HistoricoConversas::where('lead_id', $lead->id)
                ->where('tipo', 'enviada')
                ->orderBy('criado_em', 'desc')
                ->first();

            $query = HistoricoConversas::where('lead_id', $lead->id)
                ->where('tipo', 'recebida');

            if ($lastSentMessage) {
                $query->where('criado_em', '>', $lastSentMessage->criado_em);
            }

            $unreadCount = $query->count();

            Log::debug('Calculated unread count', [
                'lead_id' => $lead->id,
                'unread_count' => $unreadCount,
                'last_sent_message_date' => $lastSentMessage?->criado_em,
            ]);

            return $unreadCount;

        } catch (\Exception $e) {
            Log::error('Error calculating unread count', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Formatar número de telefone para remoteJid
     */
    private function formatRemoteJid(string $telefone): string
    {
        // Remove todos os caracteres não numéricos
        $phone = preg_replace('/\D/', '', $telefone);

        // Se não começar com código do país, adiciona o código do Brasil
        if (strlen($phone) == 11 && !str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        return $phone . '@s.whatsapp.net';
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncConversationJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

