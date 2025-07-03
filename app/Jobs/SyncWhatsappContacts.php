<?php

namespace App\Jobs;

use App\Models\LeadQuarkions;
use App\Services\EvolutionClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncWhatsappContacts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting WhatsApp contacts sync job');

        try {
            $evolutionClient = new EvolutionClient();

            // Buscar todos os leads que têm telefone mas não têm foto de perfil
            $leads = LeadQuarkions::whereNotNull('telefone')
                ->where(function ($query) {
                    $query->whereNull('profile_photo')
                        ->orWhere('profile_photo', '');
                })
                ->limit(50) // Processar em lotes para evitar sobrecarga
                ->get();

            Log::info('Found leads to sync profile photos', ['count' => $leads->count()]);

            foreach ($leads as $lead) {
                $this->syncContactProfilePhoto($lead, $evolutionClient);
                
                // Pequeno delay para evitar rate limiting
                sleep(1);
            }

            Log::info('WhatsApp contacts sync job completed successfully');

        } catch (\Exception $e) {
            Log::error('WhatsApp contacts sync job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Sincronizar foto de perfil de um contato
     */
    private function syncContactProfilePhoto(LeadQuarkions $lead, EvolutionClient $evolutionClient): void
    {
        try {
            Log::info('Syncing profile photo for lead', [
                'lead_id' => $lead->id,
                'telefone' => $lead->telefone,
            ]);

            $result = $evolutionClient->profilePicture($lead->telefone);

            if ($result['success'] && !empty($result['profilePictureUrl'])) {
                // Atualizar o lead com a URL da foto de perfil
                $lead->update([
                    'profile_photo' => $result['profilePictureUrl'],
                    'whatsapp_wuid' => $result['wuid'] ?? null,
                ]);

                Log::info('Profile photo updated successfully', [
                    'lead_id' => $lead->id,
                    'profile_photo_url' => $result['profilePictureUrl'],
                ]);
            } else {
                Log::warning('Failed to fetch profile photo', [
                    'lead_id' => $lead->id,
                    'telefone' => $lead->telefone,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                // Marcar como tentativa realizada para evitar tentar novamente
                $lead->update([
                    'profile_photo_sync_attempted' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing profile photo for lead', [
                'lead_id' => $lead->id,
                'telefone' => $lead->telefone,
                'error' => $e->getMessage(),
            ]);

            // Marcar como erro para evitar tentar novamente imediatamente
            $lead->update([
                'profile_photo_sync_attempted' => now(),
                'profile_photo_sync_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncWhatsappContacts job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

