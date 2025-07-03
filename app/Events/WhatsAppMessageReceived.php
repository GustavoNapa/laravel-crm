<?php

namespace App\Events;

use App\Models\HistoricoConversas;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $leadId;

    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(HistoricoConversas $message, $userId = null)
    {
        $this->message = $message->load('lead');
        $this->leadId = $message->lead_id;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('whatsapp.'.($this->userId ?? 'admin')),
            new PrivateChannel('whatsapp.conversation.'.$this->leadId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'         => $this->message->id,
                'lead_id'    => $this->message->lead_id,
                'mensagem'   => $this->message->mensagem,
                'tipo'       => $this->message->tipo,
                'status'     => $this->message->status,
                'created_at' => $this->message->created_at,
                'lead'       => [
                    'id'       => $this->message->lead->id,
                    'nome'     => $this->message->lead->nome,
                    'telefone' => $this->message->lead->telefone,
                    'status'   => $this->message->lead->status,
                ],
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'WhatsAppMessageReceived';
    }
}
