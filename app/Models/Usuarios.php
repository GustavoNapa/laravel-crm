<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Usuarios extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'tipo',
        'cliente_id',
    ];

    public $timestamps = false;

    protected $dates = ['criado_em'];

    protected $hidden = ['senha'];

    const CREATED_AT = 'criado_em';

    public function setSenhaAttribute($value)
    {
        $this->attributes['senha'] = Hash::make($value);
    }
}
