<?php

namespace App\Console\Commands;

use App\Services\EvolutionSessionService;
use Illuminate\Console\Command;

class TestEvolutionConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evolution:test-chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conexão com a Evolution API do WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando conexão com Evolution API...');

        $evolutionService = new EvolutionSessionService;
        $result = $evolutionService->testConnection();

        if ($result['status'] === 'connected') {
            $this->info('✅ '.$result['message']);
            $this->line('Status: connected');

            return Command::SUCCESS;
        } elseif ($result['status'] === 'disconnected') {
            $this->warn('⚠️  '.$result['message']);
            $this->line('Estado atual: '.($result['state'] ?? 'unknown'));

            // Tentar reconectar
            $this->info('Tentando reconectar...');
            try {
                $reconnectResult = $evolutionService->reconnectIfNeeded();
                if (isset($reconnectResult['state']) && $reconnectResult['state'] === 'open') {
                    $this->info('✅ Reconectado com sucesso!');

                    return Command::SUCCESS;
                } else {
                    $this->warn('⚠️  Ainda não conectado. Estado: '.($reconnectResult['state'] ?? 'unknown'));

                    return Command::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error('❌ Erro ao tentar reconectar: '.$e->getMessage());

                return Command::FAILURE;
            }
        } else {
            $this->error('❌ '.$result['message']);

            return Command::FAILURE;
        }
    }
}
