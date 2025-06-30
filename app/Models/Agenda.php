<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Agenda extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'agenda';
    
    protected $fillable = [
        'cliente_id',
        'lead_id',
        'data',
        'horario',
        'status',
        'observacoes'
    ];

    public $timestamps = false;

    protected $dates = ['criado_em', 'data'];

    const CREATED_AT = 'criado_em';

    public function lead()
    {
        return $this->belongsTo(LeadQuarkions::class, 'lead_id');
    }
}
