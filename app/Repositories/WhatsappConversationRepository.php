<?php

namespace App\Repositories;

use App\Models\HistoricoConversas;
use App\Models\LeadQuarkions;
use Illuminate\Support\Facades\DB;

class WhatsappConversationRepository
{
    /**
     * Buscar conversas com paginação e filtros
     */
    public function getConversations($filters = [], $perPage = 15)
    {
        $query = HistoricoConversas::with(['lead'])
            ->select([
                'historico_conversas.*',
                DB::raw('MAX(historico_conversas.criado_em) as last_message_at'),
                DB::raw('COUNT(historico_conversas.id) as message_count'),
            ])
            ->join('leads_quarkions', 'historico_conversas.lead_id', '=', 'leads_quarkions.id')
            ->groupBy('historico_conversas.lead_id')
            ->orderBy('last_message_at', 'desc');

        // Filtro por busca
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('leads_quarkions.nome', 'LIKE', "%{$search}%")
                    ->orWhere('leads_quarkions.telefone', 'LIKE', "%{$search}%")
                    ->orWhere('historico_conversas.mensagem', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por status
        if (! empty($filters['status'])) {
            $query->where('leads_quarkions.status', $filters['status']);
        }

        // Filtro por não lidas - removido pois coluna 'lida' não existe
        // if (!empty($filters['unread_only'])) {
        //     $query->having('unread_count', '>', 0);
        // }

        return $query->paginate($perPage);
    }

    /**
     * Buscar histórico de mensagens de uma conversa específica
     */
    public function getConversationHistory($leadId, $perPage = 50)
    {
        return HistoricoConversas::with(['lead'])
            ->where('lead_id', $leadId)
            ->orderBy('criado_em', 'asc')
            ->paginate($perPage);
    }

    /**
     * Marcar mensagens como lidas - simplificado pois coluna 'lida' não existe
     */
    public function markAsRead($leadId, $userId = null)
    {
        // Método simplificado - coluna 'lida' não existe na tabela atual
        return true;
    }

    /**
     * Criar nova mensagem
     */
    public function createMessage($data)
    {
        return HistoricoConversas::create([
            'lead_id'   => $data['lead_id'],
            'mensagem'  => $data['message'],
            'tipo'      => $data['type'] ?? 'enviada',
            'criado_em' => now(),
        ]);
    }

    /**
     * Buscar conversa por ID do lead
     */
    public function findByLeadId($leadId)
    {
        return LeadQuarkions::with(['historicoConversas' => function ($query) {
            $query->orderBy('criado_em', 'desc')->limit(1);
        }])->find($leadId);
    }

    /**
     * Atualizar status da conversa
     */
    public function updateConversationStatus($leadId, $status)
    {
        return LeadQuarkions::where('id', $leadId)->update(['status' => $status]);
    }

    /**
     * Buscar conversas recentes (últimas 24h)
     */
    public function getRecentConversations($limit = 10)
    {
        return HistoricoConversas::with(['lead'])
            ->where('criado_em', '>=', now()->subDay())
            ->orderBy('criado_em', 'desc')
            ->limit($limit)
            ->get()
            ->groupBy('lead_id');
    }

    /**
     * Contar mensagens não lidas por usuário - simplificado
     */
    public function getUnreadCount($userId = null)
    {
        // Simplificado pois coluna 'lida' não existe
        return 0;
    }

    /**
     * Buscar estatísticas das conversas
     */
    public function getConversationStats()
    {
        return [
            'total_conversations'  => LeadQuarkions::count(),
            'active_conversations' => LeadQuarkions::where('status', 'ativo')->count(),
            'unread_messages'      => $this->getUnreadCount(),
            'today_messages'       => HistoricoConversas::whereDate('criado_em', today())->count(),
        ];
    }
}
