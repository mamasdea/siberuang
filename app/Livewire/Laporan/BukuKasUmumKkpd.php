<?php

namespace App\Livewire\Laporan;

use App\Models\Pajak;
use App\Models\Belanja;
use Livewire\Component;
use App\Models\UangGiro;
use App\Models\UangKkpd;
use App\Models\BelanjaKkpd;
use App\Models\VwTransaksi;
use Livewire\Attributes\Title;
use App\Models\VwTransaksiKkpd;
use Illuminate\Support\Facades\DB;

#[Title('Buku Kas Pengeluaran')]
class BukuKasUmumKkpd extends Component
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
        $uangasuk = UangKkpd::where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $this->tahun . "-01-01")->sum('nominal');
        $uangKeluar = BelanjaKkpd::where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $this->tahun . "-01-01")->where('is_transfer', '1')->sum('nilai');

        $saldo = $uangasuk - $uangKeluar;
        $data = VwTransaksiKkpd::with('pajakkkpd')->where('tanggal', '>=', $this->mulai)->where('tanggal', '<=', $this->end)->orderBy('tanggal', 'asc')->get();

        return view('livewire.laporan.buku-kas-umum-kkpd', [
            'data' => $data,
            'saldo' => $saldo
        ]);
    }
}
