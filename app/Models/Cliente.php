<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    /**
     * Il nome della tabella associata al modello.
     *
     * @var string
     */
    protected $table = 'clienti';

    /**
     * Gli attributi assegnabili di massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'id_utente_cliente',
        'pacchetto_id',
        'sito_web',
        'logo_cliente',
        'firma',
        'token',
        'cellulare',
        'promptGPT',
    ];
    
    /**
     * Ottieni l'utente associato al cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function utenteCliente()
    {
        return $this->belongsTo(User::class, 'id_utente_cliente');
    }

    /**
     * Ottieni il pacchetto associato al cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pacchetto()
    {
        return $this->belongsTo(PacchettoPubblicazione::class, 'pacchetto_id');
    }

    /**
     * Ottieni gli asset del cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(AssetCliente::class, 'id_cliente');
    }

    /**
     * Ottieni le pubblicazioni per il cliente attraverso media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function pubblicazioni()
    {
        return $this->hasMany(Pubblicazione::class, 'id_cliente');
    }

    /**
     * Ottieni i media per il cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media()
    {
        return $this->hasMany(MediaPubblicazione::class, 'id_cliente');
    }

    /**
     * Ottieni i permessi per il cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permessi()
    {
        return $this->hasMany(PermessoCliente::class, 'id_cliente');
    }
}