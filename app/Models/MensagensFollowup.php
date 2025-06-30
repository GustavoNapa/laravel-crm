<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MensagensFollowup extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mensagens_followup';
    
    protected $fillable = [
        'cliente_id',
        'lead_id',
        'mensagem',
        'tipo',
        'agendado_para',
        'enviado'
    ];

    public $timestamps = false;

    protected $dates = ['criado_em', 'agendado_para'];

    protected $casts = [
        'enviado' => 'boolean'
    ];

    const CREATED_AT = 'criado_em';

    public function lead()
    {
        return $this->belongsTo(LeadQuarkions::class, 'lead_id');
    }
}
