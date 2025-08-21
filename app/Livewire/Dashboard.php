<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Models\Rka;

use App\Models\Belanja;            // GU
use App\Models\BelanjaKkpd;        // KKPD (tabel: belanja_kkpds)
use App\Models\BelanjaLsDetails;   // LS (detail; join ke belanja_ls untuk tanggal grafik)

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

#[Title('Dashbaoard')] // biarkan sesuai punyamu
class Dashboard extends Component
{
    // stat box
    public $jumlahProgram;
    public $jumlahKegiatan;
    public $jumlahSubKegiatan;
    public $totalAnggaran;
    public $totalRealisasi;
    public $persentaseRealisasi;

    // chart
    public $chartData;

    // ringkasan sub kegiatan (cards)
    public $subKSummary = [];

    // ringkasan per bidang
    public $bidangSummary = [];

    // modal
    public $modalOpen = false;
    public $modalSubKegiatan = null;  // SubKegiatan terpilih
    public $modalRkaRows = [];        // rincian per RKA
    public $modalTotals = [
        'anggaran'  => 0,
        'gu'        => 0,
        'kkpd'      => 0,
        'ls'        => 0,
        'realisasi' => 0,
        'persen'    => 0,
    ];

    public function mount()
    {
        $tahun = (int) Session::get('tahun_anggaran', date('Y'));

        // ======= STAT BOXES =======
        $this->jumlahProgram = Program::where('tahun_anggaran', $tahun)->count();

        $this->jumlahKegiatan = Kegiatan::whereHas('program', function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        })->count();

        $this->jumlahSubKegiatan = SubKegiatan::whereHas('kegiatan.program', function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        })->count();

        $this->totalAnggaran = Rka::whereHas('subKegiatan.kegiatan.program', function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        })->sum('anggaran');

        $totalGu = Belanja::whereHas('rka.subKegiatan.kegiatan.program', function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        })->sum('nilai');

        $totalKkpd = BelanjaKkpd::whereHas('rka.subKegiatan.kegiatan.program', function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        })->sum('nilai');

        $totalLs = BelanjaLsDetails::whereHas('rka.subKegiatan.kegiatan.program', function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        })->sum('nilai');

        $this->totalRealisasi = $totalGu + $totalKkpd + $totalLs;

        $this->persentaseRealisasi = $this->totalAnggaran > 0
            ? round(($this->totalRealisasi / $this->totalAnggaran) * 100, 2)
            : 0;

        // ======= CHART (per bulan) =======
        $datagu = Belanja::selectRaw('MONTH(tanggal) as bulan, SUM(nilai) as total')
            ->whereHas('rka.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $tahun))
            ->groupBy('bulan')->orderBy('bulan')->get();

        $datakppd = BelanjaKkpd::selectRaw('MONTH(tanggal) as bulan, SUM(nilai) as total')
            ->whereHas('rka.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $tahun))
            ->groupBy('bulan')->orderBy('bulan')->get();

        $datals = BelanjaLsDetails::selectRaw('MONTH(belanja_ls.tanggal) as bulan, SUM(belanja_ls_details.nilai) as total')
            ->join('belanja_ls', 'belanja_ls_details.belanja_ls_id', '=', 'belanja_ls.id')
            ->whereHas('rka.subKegiatan.kegiatan.program', fn($q) => $q->where('tahun_anggaran', $tahun))
            ->groupBy('bulan')->orderBy('bulan')->get();

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
            $totalGU   = $datagu->firstWhere('bulan', $num)->total   ?? 0;
            $totalKKPD = $datakppd->firstWhere('bulan', $num)->total ?? 0;
            $totalLS   = $datals->firstWhere('bulan', $num)->total   ?? 0;

            $chartLabels[] = $name;
            $chartValues[] = $totalGU + $totalKKPD + $totalLS;
        }
        $this->chartData = ['labels' => $chartLabels, 'values' => $chartValues];

        // ======= CARD RINGKASAN SUB KEGIATAN =======
        $this->loadSubKegiatanSummary($tahun);
    }

    /** Pilih kolom yang ada di DB dari daftar kandidat; fallback ke $fallback (default: id) */
    private function pickColumn(string $table, array $candidates, string $fallback = 'id'): string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }
        return $fallback;
    }

    /** Ringkasan per sub-kegiatan untuk ditampilkan sebagai cards */
    private function loadSubKegiatanSummary(int $tahun): void
    {
        // deteksi kolom kode/nama sub_kegiatans yang tersedia
        $subKodeCol = $this->pickColumn('sub_kegiatans', ['kode', 'kode_sub_kegiatan', 'kode_subkegiatan', 'kode_rekening'], 'id');
        $subNamaCol = $this->pickColumn('sub_kegiatans', ['nama', 'uraian', 'nama_sub_kegiatan', 'nama_subkegiatan'], 'id');

        $rows = SubKegiatan::query()
            ->leftJoin('pengelola_keuangans', 'sub_kegiatans.pptk_id', '=', 'pengelola_keuangans.id')
            ->select(
                'sub_kegiatans.id',
                'pengelola_keuangans.bidang',
                DB::raw("sub_kegiatans.`{$subKodeCol}` as kode"),
                DB::raw("sub_kegiatans.`{$subNamaCol}` as nama")
            )
            ->whereHas('kegiatan.program', fn($q) => $q->where('tahun_anggaran', $tahun))

            // total anggaran
            ->selectSub(function ($q) {
                $q->from('rkas')
                    ->whereColumn('rkas.sub_kegiatan_id', 'sub_kegiatans.id')
                    ->selectRaw('COALESCE(SUM(anggaran),0)');
            }, 'total_anggaran')

            // total GU
            ->selectSub(function ($q) {
                $q->from('rkas')
                    ->join('belanjas', 'belanjas.rka_id', '=', 'rkas.id')
                    ->whereColumn('rkas.sub_kegiatan_id', 'sub_kegiatans.id')
                    ->selectRaw('COALESCE(SUM(belanjas.nilai),0)');
            }, 'total_gu')

            // total KKPD
            ->selectSub(function ($q) {
                $q->from('rkas')
                    ->join('belanja_kkpds', 'belanja_kkpds.rka_id', '=', 'rkas.id')
                    ->whereColumn('rkas.sub_kegiatan_id', 'sub_kegiatans.id')
                    ->selectRaw('COALESCE(SUM(belanja_kkpds.nilai),0)');
            }, 'total_kkpd')

            // total LS (detail)
            ->selectSub(function ($q) {
                $q->from('rkas')
                    ->join('belanja_ls_details', 'belanja_ls_details.rka_id', '=', 'rkas.id')
                    ->whereColumn('rkas.sub_kegiatan_id', 'sub_kegiatans.id')
                    ->selectRaw('COALESCE(SUM(belanja_ls_details.nilai),0)');
            }, 'total_ls')

            ->orderBy(DB::raw("sub_kegiatans.`{$subKodeCol}`"))
            ->get()
            ->map(function ($r) {
                $r->total_realisasi = (float)$r->total_gu + (float)$r->total_kkpd + (float)$r->total_ls;
                $r->persen = ($r->total_anggaran > 0)
                    ? round($r->total_realisasi / $r->total_anggaran * 100, 2)
                    : 0;
                return $r;
            });

        // ======= COLOR LOGIC PER BIDANG =======
        $bidangColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'indigo'];
        $bidangList = $rows->pluck('bidang')->unique()->values();
        $colorMap = $bidangList->mapWithKeys(function ($bidang, $index) use ($bidangColors) {
            return [$bidang => $bidangColors[$index % count($bidangColors)]];
        })->toArray();

        $rows = $rows->map(function ($row) use ($colorMap) {
            $row->color_name = $colorMap[$row->bidang] ?? 'light';
            return $row;
        });

        $this->subKSummary = $rows->toArray();

        // ======= RINGKASAN PER BIDANG =======
        $bidangSummary = $rows->groupBy('bidang')
            ->map(function ($group, $bidangName) {
                $total_anggaran = $group->sum('total_anggaran');
                $total_realisasi = $group->sum('total_realisasi');
                $persen = $total_anggaran > 0 ? round($total_realisasi / $total_anggaran * 100, 2) : 0;

                return [
                    'bidang' => $bidangName,
                    'total_anggaran' => $total_anggaran,
                    'total_realisasi' => $total_realisasi,
                    'persen' => $persen,
                ];
            })
            ->sortBy('bidang')
            ->values();

        $this->bidangSummary = $bidangSummary->toArray();
    }

    /** Klik card → buka modal rincian per RKA */
    public function openSubKegiatanModal(int $subKegiatanId)
    {
        $tahun = (int) Session::get('tahun_anggaran', date('Y'));

        $this->modalSubKegiatan = SubKegiatan::with(['kegiatan.program' => function ($q) use ($tahun) {
            $q->where('tahun_anggaran', $tahun);
        }])
            ->whereHas('kegiatan.program', fn($q) => $q->where('tahun_anggaran', $tahun))
            ->findOrFail($subKegiatanId);

        $rows = Rka::query()
            ->select(
                'rkas.id',
                'rkas.kode_belanja as kode',
                'rkas.nama_belanja as uraian',
                'rkas.anggaran'
            )
            ->where('rkas.sub_kegiatan_id', $subKegiatanId)

            // GU per RKA
            ->selectSub(function ($q) {
                $q->from('belanjas')
                    ->whereColumn('belanjas.rka_id', 'rkas.id')
                    ->selectRaw('COALESCE(SUM(nilai),0)');
            }, 'gu')

            // KKPD per RKA
            ->selectSub(function ($q) {
                $q->from('belanja_kkpds')
                    ->whereColumn('belanja_kkpds.rka_id', 'rkas.id')
                    ->selectRaw('COALESCE(SUM(nilai),0)');
            }, 'kkpd')

            // LS per RKA (details)
            ->selectSub(function ($q) {
                $q->from('belanja_ls_details')
                    ->whereColumn('belanja_ls_details.rka_id', 'rkas.id')
                    ->selectRaw('COALESCE(SUM(nilai),0)');
            }, 'ls')

            ->orderBy('rkas.kode_belanja')
            ->get()
            ->map(function ($r) {
                $r->realisasi = (float)$r->gu + (float)$r->kkpd + (float)$r->ls;
                $r->persen = ($r->anggaran > 0)
                    ? round($r->realisasi / $r->anggaran * 100, 2)
                    : 0;
                return $r;
            });

        $this->modalRkaRows = $rows->toArray();

        // hitung total footer
        $anggaran = array_sum(array_column($this->modalRkaRows, 'anggaran'));
        $gu       = array_sum(array_column($this->modalRkaRows, 'gu'));
        $kkpd     = array_sum(array_column($this->modalRkaRows, 'kkpd'));
        $ls       = array_sum(array_column($this->modalRkaRows, 'ls'));
        $realisasi = $gu + $kkpd + $ls;

        $this->modalTotals = [
            'anggaran'  => $anggaran,
            'gu'        => $gu,
            'kkpd'      => $kkpd,
            'ls'        => $ls,
            'realisasi' => $realisasi,
            'persen'    => $anggaran > 0 ? round($realisasi / $anggaran * 100, 2) : 0,
        ];

        $this->modalOpen = true;
        $this->dispatch('open-subk-modal'); // JS di Blade akan show modal
    }

    public function closeSubKegiatanModal()
    {
        $this->modalOpen = false;
        $this->dispatch('close-subk-modal');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
