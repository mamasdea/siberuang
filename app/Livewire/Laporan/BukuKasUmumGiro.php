<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use App\Models\Belanja;
use App\Models\Pajak;
use App\Models\UangGiro;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use App\Models\VwTransaksi;

#[Title('Buku Kas Pengeluaran')]
class BukuKasUmumGiro extends Component
{


    public $mulai;
    public $end;
    public $tahun;
    public function mount()
    {
        $this->mulai = date('Y') . "-01-01";
        $this->end = date('Y') . "-01-01";
        $this->tahun = session('tahun_anggaran', date('Y'));
    }

    public function render()
    {
        $uangasuk = UangGiro::where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $this->tahun . "-01-01")->sum('nominal');
        $uangKeluar = Belanja::where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $this->tahun . "-01-01")->sum('nilai');

        $saldo = $uangasuk - $uangKeluar;
        $data = VwTransaksi::with('pajak')->where('tanggal', '>=', $this->mulai)->where('tanggal', '<=', $this->end)->orderBy('tanggal', 'asc')->get();

        return view('livewire.laporan.buku-kas-umum-giro', [
            'data' => $data,
            'saldo' => $saldo
        ]);
    }
}
