<?php

namespace App\Livewire\Laporan;

use App\Models\Program;
use Livewire\Component;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Laporan\LaporanNPD;
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
            $this->subKegiatans = SubKegiatan::where(function ($query) {
                // Cek transaksi GU
                $query->whereHas('rkas.belanjas', function ($q) {
                    $q->whereMonth('tanggal', $this->selectedBulan)
                        ->whereYear('tanggal', $this->tahun);
                })
                    // Atau cek transaksi LS melalui relasi belanjaLsDetails
                    ->orWhereHas('rkas.belanjaLsDetails', function ($q) {
                        $q->whereHas('belanjaLs', function ($q2) {
                            $q2->whereMonth('tanggal', $this->selectedBulan)
                                ->whereYear('tanggal', $this->tahun);
                        });
                    });
            })->whereHas('kegiatan.program', function ($query) {
                $query->where('tahun_anggaran', $this->tahun);
            })->get();
        } else {
            $this->subKegiatans = collect();
        }

        $this->selectedSubKegiatan = null; // Reset pilihan sub kegiatan ketika bulan berubah
    }


    public function exportLaporanNPD()
    {
        $laporanNPD = new LaporanNPD($this->selectedBulan);
        $laporanNPD->selectedSubKegiatan = $this->selectedSubKegiatan;
        $laporanNPD->selectedBulan = $this->selectedBulan;
        return $laporanNPD->anyar();
    }

    public function printLaporanNPD()
    {
        $subKegiatan = SubKegiatan::with(['kegiatan.program', 'rkas.belanjas'])
            ->whereHas('kegiatan.program', function ($query) {
                $query->where('tahun_anggaran', $this->tahun); // Filter berdasarkan tahun anggaran
            })
            ->find($this->selectedSubKegiatan);

        $pdf = PDF::loadView('livewire.laporan.pdf-view', [
            'subKegiatan' => $subKegiatan,
            'bulanList' => $this->bulanList,
            'selectedBulan' => $this->selectedBulan,
            'tahun' => $this->tahun,
        ]);

        return $pdf->download("Laporan-NPD-{$this->tahun}.pdf");
    }

    public function render()
    {
        $subKegiatan = SubKegiatan::with([
            'kegiatan.program',
            'rkas' => function ($query) {
                $query->withSum(['belanjas as gu_baru' => function ($subQuery) {
                    $subQuery->whereMonth('tanggal', '=', $this->selectedBulan)
                        ->whereYear('tanggal', '=', $this->tahun);
                }], 'nilai')
                    ->withSum(['belanjas as gu_lama' => function ($subQuery) {
                        $subQuery->whereMonth('tanggal', '<', $this->selectedBulan)
                            ->whereYear('tanggal', '=', $this->tahun);
                    }], 'nilai')
                    ->withSum(['belanjaLsDetails as ls_baru' => function ($subQuery) {
                        // Filter berdasarkan tanggal pada header BelanjaLs
                        $subQuery->whereHas('belanjaLs', function ($q) {
                            $q->whereMonth('tanggal', '=', $this->selectedBulan)
                                ->whereYear('tanggal', '=', $this->tahun);
                        });
                    }], 'nilai')
                    ->withSum(['belanjaLsDetails as ls_lama' => function ($subQuery) {
                        $subQuery->whereHas('belanjaLs', function ($q) {
                            $q->whereMonth('tanggal', '<', $this->selectedBulan)
                                ->whereYear('tanggal', '=', $this->tahun);
                        });
                    }], 'nilai')
                    ->with(['belanjas' => function ($a) {
                        $a->whereMonth('tanggal', '=', $this->selectedBulan)
                            ->whereYear('tanggal', '=', $this->tahun);
                    }]);
            }
        ])->whereHas('kegiatan.program', function ($query) {
            $query->where('tahun_anggaran', $this->tahun);
        })->find($this->selectedSubKegiatan);



        return view('livewire.laporan.laporan-page', [
            'subKegiatans' => $this->subKegiatans,
            'bulanList' => $this->bulanList,
            'tahun' => $this->tahun,
            'dataArray' => $subKegiatan
        ]);
    }
}
