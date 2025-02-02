<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacchettoPubblicazione extends Model
{
    use HasFactory;

    protected $table = 'pacchetto_pubblicazioni';

    protected $fillable = ['nome', 'numero_pubblicazioni', 'costo'];

    /**
     * Get the clients associated with the package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clienti()
    {
        return $this->hasMany(Cliente::class, 'pacchetto_id');
    }
}