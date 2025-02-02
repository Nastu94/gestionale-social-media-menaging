<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaInPubblicazione extends Model
{
    use HasFactory;

    protected $table = 'media_in_pubblicazioni';

    protected $fillable = ['id_media', 'id_pubblicazione'];

    /**
     * Ottieni il media associato a questa relazione.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function media()
    {
        return $this->belongsTo(MediaPubblicazione::class, 'id_media');
    }

    /**
     * Ottieni la pubblicazione associata a questa relazione.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pubblicazione()
    {
        return $this->belongsTo(Pubblicazione::class, 'id_pubblicazione');
    }
}