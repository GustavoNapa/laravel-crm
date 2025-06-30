<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ConfiguracoesCliente extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'configuracoes_cliente';
    
    protected $fillable = [
        'cliente_id',
        'prompt_qualificacao',
        'prompt_followup_1',
        'prompt_followup_2',
        'prompt_followup_3',
        'prompt_agendamento',
        'mensagem_boas_vindas',
        'mensagem_encerramento'
    ];

    public $timestamps = false;

    protected $dates = ['criado_em'];

    const CREATED_AT = 'criado_em';
}
