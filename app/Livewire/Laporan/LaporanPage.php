<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;

#[Title('Laporan NPD')]
class LaporanPage extends Component
{
    public $selectedSubKegiatan;
    public $selectedBulan;
    public $subKegiatans;
    public $bulanList;
    public $tahun;

    public function mount()
    {
        $this->tahun = Session::get('tahun_anggaran', date('Y')); // Ambil tahun anggaran dari session
        $this->bulanList = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $this->subKegiatans = collect();
        $this->selectedBulan = '';
    }

    public function updatedSelectedBulan()
    {
        if ($this->selectedBulan) {
            $this->subKegiatans = SubKegiatan::whereHas('rkas', function ($query) {
                $query->whereHas('belanjas', function ($q) {
                    $q->whereMonth('tanggal', $this->selectedBulan)
                        ->whereYear('tanggal', $this->tahun);
                })
                    ->orWhereHas('belanjaKkpds', function ($q) {
                        $q->whereMonth('tanggal', $this->selectedBulan)
                            ->whereYear('tanggal', $this->tahun);
                    })
                    ->orWhereHas('belanjaLsDetails.belanjaLs', function ($q) {
                        $q->whereMonth('tanggal', $this->selectedBulan)
                            ->whereYear('tanggal', $this->tahun);
                    });
            })
                ->whereHas('kegiatan.program', function ($query) {
                    $query->where('tahun_anggaran', $this->tahun);
                })->get();
        } else {
            $this->subKegiatans = collect();
        }

        $this->selectedSubKegiatan = null;
    }

    public function render()
    {
        $subKegiatan = null;


        if ($this->selectedSubKegiatan) {
            $subKegiatan = SubKegiatan::with([
                'kegiatan.program',
                'rkas' => function ($query) {
                    $query->withSum(['belanjas as gu_baru' => function ($subQuery) {
                        $subQuery->whereMonth('tanggal', $this->selectedBulan)
                            ->whereYear('tanggal', $this->tahun);
                    }], 'nilai')
                        ->withSum(['belanjas as gu_lama' => function ($subQuery) {
                            $subQuery->whereMonth('tanggal', '<', $this->selectedBulan)
                                ->whereYear('tanggal', $this->tahun);
                        }], 'nilai')
                        ->withSum(['belanjaKkpds as kkpd_baru' => function ($subQuery) {
                            $subQuery->whereMonth('tanggal', $this->selectedBulan)
                                ->whereYear('tanggal', $this->tahun);
                        }], 'nilai')
                        ->withSum(['belanjaKkpds as kkpd_lama' => function ($subQuery) {
                            $subQuery->whereMonth('tanggal', '<', $this->selectedBulan)
                                ->whereYear('tanggal', $this->tahun);
                        }], 'nilai')
                        ->withSum(['belanjaLsDetails as ls_baru' => function ($subQuery) {
                            $subQuery->whereHas('belanjaLs', function ($q) {
                                $q->whereMonth('tanggal', $this->selectedBulan)
                                    ->whereYear('tanggal', $this->tahun);
                            });
                        }], 'nilai')
                        ->withSum(['belanjaLsDetails as ls_lama' => function ($subQuery) {
                            $subQuery->whereHas('belanjaLs', function ($q) {
                                $q->whereMonth('tanggal', '<', $this->selectedBulan)
                                    ->whereYear('tanggal', $this->tahun);
                            });
                        }], 'nilai');
                }
            ])->whereHas('kegiatan.program', function ($query) {
                $query->where('tahun_anggaran', $this->tahun);
            })->find($this->selectedSubKegiatan);
        }

        return view('livewire.laporan.laporan-page', [
            'subKegiatans' => $this->subKegiatans,
            'bulanList' => $this->bulanList,
            'tahun' => $this->tahun,
            'dataArray' => $subKegiatan
        ]);
    }
}
