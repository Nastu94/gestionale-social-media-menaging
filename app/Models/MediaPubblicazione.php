<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaPubblicazione extends Model
{
    use HasFactory;

    protected $table = 'media_pubblicazioni';

    protected $fillable = [
        'nome',
        'id_cliente',
    ];

    /**
     * Get the client associated with the media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Get the publications associated with the media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function pubblicazioni()
    {
        return $this->hasManyThrough(
            Pubblicazione::class,
            MediaInPubblicazione::class,
            'id_media',
            'id',
            'id',
            'id_pubblicazione'
        );
    }
}