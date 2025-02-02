<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCliente extends Model
{
    use HasFactory;

    protected $table = 'assets_clienti';

    protected $fillable = ['id_cliente', 'nome_assets', 'username', 'password'];

    /**
     * Ottieni il cliente associato all'asset.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
}