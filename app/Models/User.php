<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Gli attributi assegnabili di massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ruolo_id',
    ];

    /**
     * Gli attributi che devono essere nascosti per la serializzazione.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Gli attributi che devono essere convertiti automaticamente.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Ottieni il ruolo associato all'utente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ruolo()
    {
        return $this->belongsTo(Ruolo::class, 'ruolo_id');
    }

    /**
     * Ottieni i clienti associati a questo utente se è un cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientiAssociati()
    {
        return $this->hasMany(Cliente::class, 'id_utente_cliente');
    }

    /**
     * Ottieni i permessi associati all'utente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permessi()
    {
        return $this->hasMany(PermessoCliente::class, 'id_utente');
    }

    /**
     * Ottieni i clienti associati all'utente tramite i permessi.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getClientiAttribute()
    {
        return $this->permessi()->with('cliente')->get()->pluck('cliente');
    }
}