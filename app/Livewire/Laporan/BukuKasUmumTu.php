<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use App\Models\BelanjaTu;
use App\Models\SppSpmTuNihil;
use App\Models\UangGiro;
use Livewire\Attributes\Title;
use App\Models\VwTransaksiTu;

#[Title('BKU TU')]
class BukuKasUmumTu extends Component
{
    public $mulai;
    public $end;
    public $tahun;

    public function mount()
    {
        $this->tahun = session('tahun_anggaran', date('Y'));
        $this->mulai = $this->tahun . "-01-01";
        $this->end = $this->tahun . "-01-01";
    }

    public function render()
    {
        $startYear = $this->tahun . "-01-01";

        // Saldo awal: SP2D TU masuk - Belanja TU keluar - TU Nihil keluar (sebelum periode mulai)
        $uangMasuk = UangGiro::where('tipe', 'TU')
            ->where('tanggal', '<', $this->mulai)
            ->where('tanggal', '>=', $startYear)
            ->sum('nominal');

        $uangKeluarBelanja = BelanjaTu::where('tanggal', '<', $this->mulai)
            ->where('tanggal', '>=', $startYear)
            ->sum('nilai');

        $uangKeluarNihil = SppSpmTuNihil::where('tanggal', '<', $this->mulai)
            ->where('tanggal', '>=', $startYear)
            ->sum('nilai_setor');

        // Pajak: dipungut (debet) - disetor (kredit) = net 0, tapi perlu hitung terpisah
        $pajakDipungut = \App\Models\PajakTu::whereHas('belanjaTu', function ($q) use ($startYear) {
            $q->where('tanggal', '<', $this->mulai)->where('tanggal', '>=', $startYear);
        })->sum('nominal');

        $pajakDisetor = $pajakDipungut; // disetor = dipungut (selalu sama)

        $saldo = $uangMasuk + $pajakDipungut - $uangKeluarBelanja - $uangKeluarNihil - $pajakDisetor;

        $data = VwTransaksiTu::with('pajakTu')
            ->where('tanggal', '>=', $this->mulai)
            ->where('tanggal', '<=', $this->end)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('livewire.laporan.buku-kas-umum-tu', [
            'data' => $data,
            'saldo' => $saldo,
        ]);
    }
}
