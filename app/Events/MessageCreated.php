<?php

namespace App\Events;

use App\Models\HistoricoConversas;
use App\Models\LeadQuarkions;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $lead;

    /**
     * Create a new event instance.
     */
    public function __construct(HistoricoConversas $message, LeadQuarkions $lead)
    {
        $this->message = $message;
        $this->lead = $lead;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.'.$this->lead->id),
            new Channel('whatsapp-messages'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MessageCreated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id'        => $this->message->id,
                'lead_id'   => $this->message->lead_id,
                'mensagem'  => $this->message->mensagem,
                'tipo'      => $this->message->tipo,
                'criado_em' => $this->message->criado_em,
            ],
            'lead' => [
                'id'            => $this->lead->id,
                'nome'          => $this->lead->nome,
                'telefone'      => $this->lead->telefone,
                'profile_photo' => $this->lead->profile_photo,
                'last_message'  => $this->lead->last_message,
                'unread_count'  => $this->lead->unread_count,
            ],
        ];
    }
}
