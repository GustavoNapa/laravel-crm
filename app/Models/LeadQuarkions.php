<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadQuarkions extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'leads_quarkions';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'status',
        'origem',
        'cliente_id',
        'profile_photo',
        'whatsapp_wuid',
        'profile_photo_sync_attempted',
        'profile_photo_sync_error',
        'last_message',
        'last_message_timestamp',
        'last_message_from_me',
        'unread_count',
    ];

    public $timestamps = false;

    protected $dates = ['criado_em', 'profile_photo_sync_attempted', 'last_message_timestamp'];

    const CREATED_AT = 'criado_em';

    public function agendamentos()
    {
        return $this->hasMany(Agenda::class, 'lead_id');
    }

    public function conversas()
    {
        return $this->hasMany(HistoricoConversas::class, 'lead_id');
    }

    public function followups()
    {
        return $this->hasMany(MensagensFollowup::class, 'lead_id');
    }

    public function ligacoes()
    {
        return $this->hasMany(LigacoesIa::class, 'lead_id');
    }
}
