<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;

#[Title('Laporan Realisasi Anggaran')]
class LaporanRealisasi extends Component
{
    public $selectedBulan;
    public $bulanList;
    public $tahun;
    public $subKegiatans;

    public function mount()
    {
        $this->tahun = Session::get('tahun_anggaran', date('Y')); // Ambil tahun anggaran dari session
        $this->bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $this->subKegiatans = collect();
        $this->selectedBulan = '';
    }

    public function updatedSelectedBulan()
    {
        if ($this->selectedBulan) {
            $this->subKegiatans = SubKegiatan::with([
                'kegiatan.program',
                'rkas' => function ($query) {
                    $query->withSum(['belanjas as gu_bulan_ini' => function ($subQuery) {
                        $subQuery->whereMonth('tanggal', '=', $this->selectedBulan)
                            ->whereYear('tanggal', '=', $this->tahun);
                    }], 'nilai')
                        ->withSum(['belanjas as gu_bulan_lalu' => function ($subQuery) {
                            $subQuery->whereMonth('tanggal', '<', $this->selectedBulan)
                                ->whereYear('tanggal', '=', $this->tahun);
                        }], 'nilai')
                        ->withSum(['belanjaLsDetails as ls_bulan_ini' => function ($subQuery) {
                            $subQuery->whereHas('belanjaLs', function ($q) {
                                $q->whereMonth('tanggal', '=', $this->selectedBulan)
                                    ->whereYear('tanggal', '=', $this->tahun);
                            });
                        }], 'nilai')
                        ->withSum(['belanjaLsDetails as ls_bulan_lalu' => function ($subQuery) {
                            $subQuery->whereHas('belanjaLs', function ($q) {
                                $q->whereMonth('tanggal', '<', $this->selectedBulan)
                                    ->whereYear('tanggal', '=', $this->tahun);
                            });
                        }], 'nilai');
                }
            ])->whereHas('kegiatan.program', function ($query) {
                $query->where('tahun_anggaran', $this->tahun);
            })->get();
        } else {
            $this->subKegiatans = collect();
        }
    }

    public function render()
    {
        return view('livewire.laporan.laporan-realisasi', [
            'subKegiatans' => $this->subKegiatans,
            'bulanList' => $this->bulanList,
            'tahun' => $this->tahun,
        ]);
    }
}
