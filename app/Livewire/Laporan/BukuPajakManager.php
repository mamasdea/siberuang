<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Laporan Buku Pajak')]
class BukuPajakManager extends Component
{
    public $jenisLaporan = 'all';

    public function render()
    {
        return view('livewire.laporan.buku-pajak-manager');
    }
}
