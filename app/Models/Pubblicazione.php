<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pubblicazione extends Model
{
    use HasFactory;

    protected $table = 'pubblicazioni';

    protected $fillable = [
        'id_cliente', 
        'stato_id', 
        'testo', 
        'data_pubblicazione', 
        'ultima_modifica', 
        'note'
    ];

    protected $dates = ['data_pubblicazione'];

    /**
     * Ottieni lo stato associato alla pubblicazione.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stato()
    {
        return $this->belongsTo(StatoPubblicazione::class, 'stato_id');
    }

    /**
     * Ottieni i media associati alla pubblicazione tramite la tabella pivot.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function media()
    {
        return $this->hasManyThrough(
            MediaPubblicazione::class, 
            MediaInPubblicazione::class, 
            'id_pubblicazione', 
            'id', 
            'id', 
            'id_media'
        );
    }

    /**
     * Ottieni il cliente associato al media della pubblicazione.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
    

    /**
     * Ottieni i commenti della chat associati alla pubblicazione.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commenti()
    {
        return $this->hasMany(ChatPubblicazione::class, 'id_pubblicazione');
    }
}