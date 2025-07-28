<?php

namespace App\Livewire;

use App\Models\Rka;
use App\Models\Belanja;
use App\Models\Program;
use Livewire\Component;
use App\Models\Kegiatan;
use App\Models\BelanjaLs;
use App\Models\BelanjaKkpd;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
use App\Models\BelanjaLsDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

#[Title('Dashbaoard')]
class Dashboard extends Component
{
    public $jumlahProgram;
    public $jumlahKegiatan;
    public $jumlahSubKegiatan;
    public $totalAnggaran;
    public $totalRealisasi;
    public $persentaseRealisasi;
    public $chartData;

    public function mount()
    {
        $activeMenu = 'Dashboard';
        $tahun = Session::get('tahun_anggaran', date('Y')); // Ambil tahun anggaran yang dipilih

        // Hitung jumlah program berdasarkan tahun anggaran
        $this->jumlahProgram = Program::where('tahun_anggaran', $tahun)->count();

        // Hitung jumlah kegiatan berdasarkan tahun anggaran
        $this->jumlahKegiatan = Kegiatan::whereHas('program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->count();

        // Hitung jumlah sub-kegiatan berdasarkan tahun anggaran
        $this->jumlahSubKegiatan = SubKegiatan::whereHas('kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->count();

        // Hitung total anggaran berdasarkan tahun anggaran
        $this->totalAnggaran = Rka::whereHas('subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->sum('anggaran');

        // Hitung total realisasi anggaran berdasarkan tahun anggaran
        $totalGu = Belanja::whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->sum('nilai');

        $totalKkpd = BelanjaKkpd::whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->sum('nilai');

        $totalLs = BelanjaLsDetails::whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->sum('nilai'); // ambil dari detail, bukan belanja_ls

        $this->totalRealisasi = $totalGu + $totalKkpd + $totalLs;


        // Hitung persentase realisasi anggaran
        $this->persentaseRealisasi = $this->totalAnggaran > 0
            ? round(($this->totalRealisasi / $this->totalAnggaran) * 100, 2)
            : 0;

        // Ambil data realisasi anggaran per bulan berdasarkan tahun anggaran
        $datagu = Belanja::select(
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nilai) as total')
        )->whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->groupBy('bulan')->orderBy('bulan')->get();

        $datakppd = BelanjaKkpd::select(
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nilai) as total')
        )->whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->groupBy('bulan')->orderBy('bulan')->get();

        $datals = BelanjaLsDetails::select(
            DB::raw('MONTH(belanja_ls.tanggal) as bulan'),
            DB::raw('SUM(belanja_ls_details.nilai) as total')
        )
            ->join('belanja_ls', 'belanja_ls_details.belanja_ls_id', '=', 'belanja_ls.id')
            ->whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
                $query->where('tahun_anggaran', $tahun);
            })
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();


        $bulanIndo = [
            1 => "Januari",
            2 => "Februari",
            3 => "Maret",
            4 => "April",
            5 => "Mei",
            6 => "Juni",
            7 => "Juli",
            8 => "Agustus",
            9 => "September",
            10 => "Oktober",
            11 => "November",
            12 => "Desember"
        ];

        $chartLabels = [];
        $chartValues = [];

        foreach ($bulanIndo as $num => $name) {
            $totalGU = $datagu->firstWhere('bulan', $num)?->total ?? 0;
            $totalKKPD = $datakppd->firstWhere('bulan', $num)?->total ?? 0;
            $totalLS = $datals->firstWhere('bulan', $num)?->total ?? 0;

            $chartLabels[] = $name;
            $chartValues[] = $totalGU + $totalKKPD + $totalLS;
        }

        $this->chartData = [
            'labels' => $chartLabels,
            'values' => $chartValues,
        ];

        // dd($this->chartData);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
