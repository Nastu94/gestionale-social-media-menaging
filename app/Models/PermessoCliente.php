<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermessoCliente extends Model
{
    use HasFactory;

    /**
     * Il nome della tabella associata al modello.
     *
     * @var string
     */
    protected $table = 'permessi_cliente';

    /**
     * Gli attributi assegnabili di massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cliente',
        'id_utente',
    ];

    /**
     * Ottieni il cliente associato al permesso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Ottieni l'utente associato al permesso.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function utente()
    {
        return $this->belongsTo(User::class, 'id_utente');
    }
}