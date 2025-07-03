<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigacoesIa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ligacoes_ia';

    protected $fillable = [
        'cliente_id',
        'lead_id',
        'status',
        'duracao',
        'transcricao',
    ];

    public $timestamps = false;

    protected $dates = ['criado_em'];

    const CREATED_AT = 'criado_em';

    public function lead()
    {
        return $this->belongsTo(LeadQuarkions::class, 'lead_id');
    }
}
