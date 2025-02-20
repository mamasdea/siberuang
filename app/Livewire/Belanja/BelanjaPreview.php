<?php

namespace App\Livewire\Belanja;

use App\Models\Pajak;
use App\Models\Belanja;
use Livewire\Component;
use App\Models\Penerimaan;

class BelanjaPreview extends Component
{
    public $belanja;
    public $pajaks = [];
    public $penerimaans = [];
    public $rka;
    public $subKegiatan;
    public $kegiatan;
    public $program;

    protected $listeners = ['openPreview' => 'loadData'];

    public function loadData($belanjaId)
    {
        $this->belanja = Belanja::with('rka')->find($belanjaId);
        $this->pajaks = Pajak::where('belanja_id', $belanjaId)->get();
        $this->penerimaans = Penerimaan::where('belanja_id', $belanjaId)->with('penerima')->get();

        // Ambil data RKA, Sub Kegiatan, dan Kegiatan
        $this->rka = $this->belanja->rka;
        $this->subKegiatan = $this->rka->subKegiatan;
        $this->kegiatan = $this->subKegiatan->kegiatan;
        $this->program = $this->kegiatan->program;
    }

    public function render()
    {
        return view('livewire.belanja.belanja-preview', [
            'belanja' => $this->belanja,
            'pajak' => $this->pajaks,
            'penerimaan' => $this->penerimaans,
            'rka' => $this->rka,
            'subKegiatan' => $this->subKegiatan,
            'kegiatan' => $this->kegiatan,
            'program' => $this->program,
        ]);
    }
}
