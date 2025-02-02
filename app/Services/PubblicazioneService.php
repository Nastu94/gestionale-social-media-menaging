<?php 

namespace App\Services;

use App\Models\Pubblicazione;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PubblicazioneService
{
    public function pubblicaPubblicazioniScadute()
    {
        Log::info("Inizio dell'aggiornamento delle pubblicazioni scadute: " . Carbon::now()->toDateTimeString());
        $pubblicazioni = Pubblicazione::where('stato_id', 5)
                           ->where('data_pubblicazione', '<=', now())
                           ->get();

        foreach ($pubblicazioni as $pubblicazione) {
            $pubblicazione->update([
                'stato_id' => 6
            ]);
            Log::info("Pubblicazione ID {$pubblicazione->id} aggiornata allo stato 6: " . $pubblicazione->data_pubblicazione);
        }
        Log::info("Pubblicazioni scadute aggiornate: " . $pubblicazioni->count());
    }
}
