<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteFacebook extends Model
{
    use HasFactory;

    protected $table = 'cliente_facebook';

    protected $fillable = [
        'id_cliente',
        'facebook_id',
        'token',
        'refresh_token',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
}
