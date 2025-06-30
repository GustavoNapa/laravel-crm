<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Agentes extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'agentes';
    
    protected $fillable = [
        'nome',
        'tipo',
        'voz_padrao',
        'ativo',
        'cliente_id'
    ];

    public $timestamps = false;

    protected $casts = [
        'ativo' => 'boolean'
    ];
}
