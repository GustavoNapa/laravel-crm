<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HistoricoConversas extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'historico_conversas';
    
    protected $fillable = [
        'cliente_id',
        'lead_id',
        'mensagem',
        'tipo'
    ];

    public $timestamps = false;

    protected $dates = ['criado_em'];

    const CREATED_AT = 'criado_em';

    public function lead()
    {
        return $this->belongsTo(LeadQuarkions::class, 'lead_id');
    }
}
