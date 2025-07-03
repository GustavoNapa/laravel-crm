<?php

namespace App\Services;

use App\Models\Agenda;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\GoogleCalendar\Event;

class GoogleCalendarSyncService
{
    /**
     * Sincroniza um evento da agenda com o Google Calendar
     */
    public function syncToGoogle(Agenda $agenda)
    {
        try {
            // Verificar se as credenciais do Google estão configuradas
            if (! $this->hasGoogleCredentials()) {
                Log::warning('Google Calendar credentials not configured');

                return false;
            }

            if ($agenda->google_event_id) {
                // Atualizar evento existente
                return $this->updateGoogleEvent($agenda);
            } else {
                // Criar novo evento
                return $this->createGoogleEvent($agenda);
            }
        } catch (Exception $e) {
            Log::error('Error syncing to Google Calendar: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Cria um novo evento no Google Calendar
     */
    private function createGoogleEvent(Agenda $agenda)
    {
        try {
            $event = new Event;

            $event->name = $agenda->titulo ?? 'Agendamento';
            $event->description = $agenda->observacoes ?? '';

            // Configurar data e hora
            $startDateTime = Carbon::parse($agenda->data.' '.$agenda->horario);
            $endDateTime = $startDateTime->copy()->addHour(); // Duração padrão de 1 hora

            $event->startDateTime = $startDateTime;
            $event->endDateTime = $endDateTime;

            // Adicionar participantes se houver
            if ($agenda->lead && $agenda->lead->email) {
                $event->addAttendee([
                    'email' => $agenda->lead->email,
                    'name'  => $agenda->lead->nome ?? 'Cliente',
                ]);
            }

            $googleEvent = $event->save();

            // Atualizar a agenda com o ID do evento do Google
            $agenda->update([
                'google_event_id'  => $googleEvent->id,
                'synced_at'        => now(),
                'sync_with_google' => true,
                'google_metadata'  => [
                    'calendar_id' => config('google-calendar.calendar_id'),
                    'created_at'  => now()->toISOString(),
                ],
            ]);

            Log::info('Google Calendar event created', ['agenda_id' => $agenda->id, 'google_event_id' => $googleEvent->id]);

            return true;

        } catch (Exception $e) {
            Log::error('Error creating Google Calendar event: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Atualiza um evento existente no Google Calendar
     */
    private function updateGoogleEvent(Agenda $agenda)
    {
        try {
            $event = Event::find($agenda->google_event_id);

            if (! $event) {
                Log::warning('Google Calendar event not found', ['google_event_id' => $agenda->google_event_id]);
                // Tentar criar um novo evento
                $agenda->google_event_id = null;

                return $this->createGoogleEvent($agenda);
            }

            $event->name = $agenda->titulo ?? 'Agendamento';
            $event->description = $agenda->observacoes ?? '';

            // Configurar data e hora
            $startDateTime = Carbon::parse($agenda->data.' '.$agenda->horario);
            $endDateTime = $startDateTime->copy()->addHour();

            $event->startDateTime = $startDateTime;
            $event->endDateTime = $endDateTime;

            $event->save();

            // Atualizar timestamp de sincronização
            $agenda->update([
                'synced_at'       => now(),
                'google_metadata' => array_merge(
                    $agenda->google_metadata ?? [],
                    ['updated_at' => now()->toISOString()]
                ),
            ]);

            Log::info('Google Calendar event updated', ['agenda_id' => $agenda->id, 'google_event_id' => $agenda->google_event_id]);

            return true;

        } catch (Exception $e) {
            Log::error('Error updating Google Calendar event: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Remove um evento do Google Calendar
     */
    public function deleteFromGoogle(Agenda $agenda)
    {
        try {
            if (! $agenda->google_event_id) {
                return true; // Não há evento para deletar
            }

            if (! $this->hasGoogleCredentials()) {
                Log::warning('Google Calendar credentials not configured');

                return false;
            }

            $event = Event::find($agenda->google_event_id);

            if ($event) {
                $event->delete();
                Log::info('Google Calendar event deleted', ['agenda_id' => $agenda->id, 'google_event_id' => $agenda->google_event_id]);
            }

            // Limpar dados do Google Calendar da agenda
            $agenda->update([
                'google_event_id'  => null,
                'synced_at'        => null,
                'sync_with_google' => false,
                'google_metadata'  => null,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error deleting Google Calendar event: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Sincroniza todos os eventos pendentes
     */
    public function syncPendingEvents()
    {
        $pendingEvents = Agenda::where('sync_with_google', true)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhere('updated_at', '>', 'synced_at');
            })
            ->get();

        $synced = 0;
        foreach ($pendingEvents as $agenda) {
            if ($this->syncToGoogle($agenda)) {
                $synced++;
            }
        }

        Log::info('Synced pending events to Google Calendar', ['count' => $synced]);

        return $synced;
    }

    /**
     * Verifica se as credenciais do Google estão configuradas
     */
    private function hasGoogleCredentials()
    {
        $credentialsPath = storage_path('app/google/credentials.json');

        return file_exists($credentialsPath) && config('google-calendar.calendar_id');
    }

    /**
     * Importa eventos do Google Calendar para a agenda local
     */
    public function importFromGoogle($startDate = null, $endDate = null)
    {
        try {
            if (! $this->hasGoogleCredentials()) {
                Log::warning('Google Calendar credentials not configured');

                return false;
            }

            $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
            $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

            $events = Event::get($startDate, $endDate);
            $imported = 0;

            foreach ($events as $event) {
                // Verificar se o evento já existe na agenda local
                $existingAgenda = Agenda::where('google_event_id', $event->id)->first();

                if (! $existingAgenda) {
                    // Criar nova agenda a partir do evento do Google
                    $agenda = Agenda::create([
                        'cliente_id'       => 'default_client', // Ajustar conforme necessário
                        'lead_id'          => null, // Pode ser associado posteriormente
                        'data'             => $event->startDateTime->format('Y-m-d'),
                        'horario'          => $event->startDateTime->format('H:i:s'),
                        'status'           => 'agendado',
                        'observacoes'      => $event->description ?? '',
                        'google_event_id'  => $event->id,
                        'synced_at'        => now(),
                        'sync_with_google' => true,
                        'google_metadata'  => [
                            'imported_at' => now()->toISOString(),
                            'calendar_id' => config('google-calendar.calendar_id'),
                        ],
                    ]);

                    $imported++;
                    Log::info('Imported Google Calendar event', ['agenda_id' => $agenda->id, 'google_event_id' => $event->id]);
                }
            }

            Log::info('Imported events from Google Calendar', ['count' => $imported]);

            return $imported;

        } catch (Exception $e) {
            Log::error('Error importing from Google Calendar: '.$e->getMessage());

            return false;
        }
    }
}
