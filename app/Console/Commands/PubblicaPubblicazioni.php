<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PubblicazioneService;

class PubblicaPubblicazioni extends Command
{
    protected $signature = 'pubblicazioni:pubblica';
    protected $description = 'Pubblica tutte le pubblicazioni scadute.';

    public function __construct(PubblicazioneService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->service->pubblicaPubblicazioniScadute();
        $this->info('Le pubblicazioni scadute sono state aggiornate.');
    }
}
