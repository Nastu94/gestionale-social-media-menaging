<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruolo extends Model
{
    use HasFactory;

    protected $table = 'ruoli';

    protected $fillable = ['nome'];

    /**
     * Get the users for the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function utenti()
    {
        return $this->hasMany(User::class, 'ruolo_id');
    }
} 