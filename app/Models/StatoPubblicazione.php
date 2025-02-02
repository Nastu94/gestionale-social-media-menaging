<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatoPubblicazione extends Model
{
    use HasFactory;

    protected $table = 'stato_pubblicazione';

    protected $fillable = ['nome_stato'];

    /**
     * Get the publications for the status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pubblicazioni()
    {
        return $this->hasMany(Pubblicazione::class, 'stato_id');
    }
}