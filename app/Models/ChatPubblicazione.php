<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatPubblicazione extends Model
{
    protected $table = 'chat_pubblicazioni';

    protected $fillable = [
        'id_pubblicazione',
        'utente',
        'commento',
        'data_testo'
    ];

    // Disabilita la gestione automatica dei timestamp
    public $timestamps = false;

    /**
     * Ottieni la pubblicazione associata a questo commento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pubblicazione()
    {
        return $this->belongsTo(Pubblicazione::class, 'id_pubblicazione');
    }
}