<?php

namespace App\Livewire\Belanja\Kkpd;

use App\Models\BelanjaKkpd;
use App\Models\PajakKkpd;
use Livewire\Component;
use App\Models\PenerimaanKkpd;

class BelanjaPreviewKkpd extends Component
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
        $this->belanja = BelanjaKkpd::with('rka')->find($belanjaId);
        $this->pajaks = PajakKkpd::where('belanja_id', $belanjaId)->get();
        $this->penerimaans = PenerimaanKkpd::where('belanja_id', $belanjaId)->with('penerima')->get();

        // Ambil data RKA, Sub Kegiatan, dan Kegiatan
        $this->rka = $this->belanja->rka;
        $this->subKegiatan = $this->rka->subKegiatan;
        $this->kegiatan = $this->subKegiatan->kegiatan;
        $this->program = $this->kegiatan->program;
    }

    public function render()
    {
        return view('livewire.belanja.kkpd.belanja-preview-kkpd', [
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
