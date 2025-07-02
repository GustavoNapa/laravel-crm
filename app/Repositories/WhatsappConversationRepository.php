<?php

namespace App\Repositories;

use App\Models\HistoricoConversas;
use App\Models\LeadQuarkions;
use Illuminate\Pagination\LengthAwarePaginator;
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
                DB::raw('MAX(historico_conversas.created_at) as last_message_at'),
                DB::raw('COUNT(CASE WHEN historico_conversas.lida = 0 AND historico_conversas.tipo = "recebida" THEN 1 END) as unread_count')
            ])
            ->join('leads_quarkions', 'historico_conversas.lead_id', '=', 'leads_quarkions.id')
            ->groupBy('historico_conversas.lead_id')
            ->orderBy('last_message_at', 'desc');

        // Filtro por busca
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('leads_quarkions.nome', 'LIKE', "%{$search}%")
                  ->orWhere('leads_quarkions.telefone', 'LIKE', "%{$search}%")
                  ->orWhere('historico_conversas.mensagem', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $query->where('leads_quarkions.status', $filters['status']);
        }

        // Filtro por não lidas
        if (!empty($filters['unread_only'])) {
            $query->having('unread_count', '>', 0);
        }

        return $query->paginate($perPage);
    }

    /**
     * Buscar histórico de mensagens de uma conversa específica
     */
    public function getConversationHistory($leadId, $perPage = 50)
    {
        return HistoricoConversas::with(['lead'])
            ->where('lead_id', $leadId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Marcar mensagens como lidas
     */
    public function markAsRead($leadId, $userId = null)
    {
        return HistoricoConversas::where('lead_id', $leadId)
            ->where('tipo', 'recebida')
            ->where('lida', false)
            ->update([
                'lida' => true,
                'lida_em' => now(),
                'lida_por' => $userId
            ]);
    }

    /**
     * Criar nova mensagem
     */
    public function createMessage($data)
    {
        return HistoricoConversas::create([
            'lead_id' => $data['lead_id'],
            'mensagem' => $data['message'],
            'tipo' => $data['type'] ?? 'enviada',
            'status' => $data['status'] ?? 'sent',
            'message_id' => $data['message_id'] ?? null,
            'media_url' => $data['media_url'] ?? null,
            'media_type' => $data['media_type'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Buscar conversa por ID do lead
     */
    public function findByLeadId($leadId)
    {
        return LeadQuarkions::with(['historicoConversas' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(1);
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
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->groupBy('lead_id');
    }

    /**
     * Contar mensagens não lidas por usuário
     */
    public function getUnreadCount($userId = null)
    {
        return HistoricoConversas::where('tipo', 'recebida')
            ->where('lida', false)
            ->count();
    }

    /**
     * Buscar estatísticas das conversas
     */
    public function getConversationStats()
    {
        return [
            'total_conversations' => LeadQuarkions::count(),
            'active_conversations' => LeadQuarkions::where('status', 'ativo')->count(),
            'unread_messages' => $this->getUnreadCount(),
            'today_messages' => HistoricoConversas::whereDate('created_at', today())->count(),
        ];
    }
}

