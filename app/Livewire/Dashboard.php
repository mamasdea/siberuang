<?php

namespace App\Livewire;

use App\Models\Rka;
use App\Models\Belanja;
use App\Models\Program;
use Livewire\Component;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
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
        $this->totalRealisasi = Belanja::whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->sum('nilai');

        // Hitung persentase realisasi anggaran
        $this->persentaseRealisasi = $this->totalAnggaran > 0
            ? round(($this->totalRealisasi / $this->totalAnggaran) * 100, 2)
            : 0;

        // Ambil data realisasi anggaran per bulan berdasarkan tahun anggaran
        $data = Belanja::select(
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nilai) as total')
        )->whereHas('rka.subKegiatan.kegiatan.program', function ($query) use ($tahun) {
            $query->where('tahun_anggaran', $tahun);
        })->groupBy('bulan')->orderBy('bulan')->get();

        // Konversi bulan ke nama bulan (Bahasa Indonesia)
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

        // Pastikan semua bulan muncul di chart meskipun realisasi 0
        $chartLabels = [];
        $chartValues = [];
        foreach ($bulanIndo as $num => $name) {
            $chartLabels[] = $name;
            $chartValues[] = $data->where('bulan', $num)->first()->total ?? 0;
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
