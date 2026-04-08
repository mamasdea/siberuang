<?php

namespace App\Livewire\Laporan;

use App\Models\Belanja;
use App\Models\BelanjaTu;
use App\Models\BelanjaLsDetails;
use App\Models\Rka;
use App\Models\SubKegiatan;
use App\Models\PengelolaKeuangan;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;

#[Title('Laporan Rincian Obyek')]
class LaporanRincianObyek extends Component
{
    public $periodeAwal;
    public $periodeAkhir;
    public $tanggalLaporan;
    public $tahun;
    public $selectedSubKegiatan = '';
    public $subKegiatanList = [];
    public $rkaPages = [];

    public function mount()
    {
        $this->tahun = Session::get('tahun_anggaran', date('Y'));
        $this->tanggalLaporan = date('Y-m-d');
        $this->loadSubKegiatanList();
    }

    public function updatedPeriodeAkhir()
    {
        $this->loadSubKegiatanList();
        $this->selectedSubKegiatan = '';
        $this->rkaPages = [];
    }

    public function updatedPeriodeAwal()
    {
        if ($this->periodeAkhir) {
            $this->loadSubKegiatanList();
            $this->selectedSubKegiatan = '';
            $this->rkaPages = [];
        }
    }

    public function updatedSelectedSubKegiatan()
    {
        $this->loadReport();
    }

    public function loadSubKegiatanList()
    {
        if (!$this->periodeAwal || !$this->periodeAkhir) {
            $this->subKegiatanList = [];
            return;
        }

        $this->subKegiatanList = SubKegiatan::with('kegiatan.program')
            ->whereHas('kegiatan.program', function ($q) {
                $q->where('tahun_anggaran', $this->tahun);
            })
            ->where(function ($q) {
                $q->whereHas('rkas.belanjas', function ($sub) {
                    $sub->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                })->orWhereHas('rkas.belanjaTus', function ($sub) {
                    $sub->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                })->orWhereHas('rkas.belanjaLsDetails.belanjaLs', function ($sub) {
                    $sub->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                });
            })
            ->orderBy('kode', 'asc')
            ->get();
    }

    public function loadReport()
    {
        if (!$this->selectedSubKegiatan || !$this->periodeAwal || !$this->periodeAkhir) {
            $this->rkaPages = [];
            return;
        }

        $subKegiatan = SubKegiatan::with(['kegiatan.program', 'pptk'])
            ->findOrFail($this->selectedSubKegiatan);

        $rkas = Rka::where('sub_kegiatan_id', $this->selectedSubKegiatan)
            ->where(function ($q) {
                $q->whereHas('belanjas', function ($sub) {
                    $sub->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                })->orWhereHas('belanjaTus', function ($sub) {
                    $sub->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                })->orWhereHas('belanjaLsDetails.belanjaLs', function ($sub) {
                    $sub->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                });
            })
            ->orderBy('kode_belanja', 'asc')
            ->get();

        $pages = [];

        foreach ($rkas as $rka) {
            // ===== SPJ Sebelumnya per jenis =====
            $spjSebelumnyaGu = Belanja::where('rka_id', $rka->id)
                ->where('tanggal', '<', $this->periodeAwal)->sum('nilai');
            $spjSebelumnyaTu = BelanjaTu::where('rka_id', $rka->id)
                ->where('tanggal', '<', $this->periodeAwal)->sum('nilai');
            $spjSebelumnyaLs = BelanjaLsDetails::where('rka_id', $rka->id)
                ->whereHas('belanjaLs', function ($q) {
                    $q->where('tanggal', '<', $this->periodeAwal);
                })->sum('nilai');

            $spjSebelumnya = [
                'ls' => $spjSebelumnyaLs,
                'tu' => $spjSebelumnyaTu,
                'gu' => $spjSebelumnyaGu,
                'total' => $spjSebelumnyaLs + $spjSebelumnyaTu + $spjSebelumnyaGu,
            ];

            // ===== Belanja periode ini =====
            $belanjasGu = Belanja::where('rka_id', $rka->id)
                ->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
                ->orderBy('tanggal', 'asc')->get()
                ->map(fn($b) => [
                    'tanggal' => $b->tanggal,
                    'no_bukti' => $b->no_bukti,
                    'uraian' => $b->uraian,
                    'nilai' => $b->nilai,
                    'jenis' => 'gu',
                ]);

            $belanjasTu = BelanjaTu::where('rka_id', $rka->id)
                ->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
                ->orderBy('tanggal', 'asc')->get()
                ->map(fn($b) => [
                    'tanggal' => $b->tanggal,
                    'no_bukti' => $b->no_bukti,
                    'uraian' => $b->uraian,
                    'nilai' => $b->nilai,
                    'jenis' => 'tu',
                ]);

            $belanjasLs = BelanjaLsDetails::with('belanjaLs')
                ->where('rka_id', $rka->id)
                ->whereHas('belanjaLs', function ($q) {
                    $q->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
                })->get()
                ->map(fn($d) => [
                    'tanggal' => $d->belanjaLs->tanggal,
                    'no_bukti' => $d->belanjaLs->no_bukti,
                    'uraian' => $d->belanjaLs->uraian,
                    'nilai' => $d->nilai,
                    'jenis' => 'ls',
                ]);

            $belanjas = $belanjasGu->concat($belanjasTu)->concat($belanjasLs)
                ->sortBy('tanggal')->values()->toArray();

            $totalPeriode = [
                'ls' => collect($belanjas)->where('jenis', 'ls')->sum('nilai'),
                'tu' => collect($belanjas)->where('jenis', 'tu')->sum('nilai'),
                'gu' => collect($belanjas)->where('jenis', 'gu')->sum('nilai'),
            ];
            $totalPeriode['total'] = $totalPeriode['ls'] + $totalPeriode['tu'] + $totalPeriode['gu'];

            $jumlahRealisasi = [
                'ls' => $spjSebelumnya['ls'] + $totalPeriode['ls'],
                'tu' => $spjSebelumnya['tu'] + $totalPeriode['tu'],
                'gu' => $spjSebelumnya['gu'] + $totalPeriode['gu'],
            ];
            $jumlahRealisasi['total'] = $jumlahRealisasi['ls'] + $jumlahRealisasi['tu'] + $jumlahRealisasi['gu'];

            $anggaranAcuan = ($rka->perubahan ?? 0) > 0 ? $rka->perubahan : ($rka->penetapan ?? $rka->anggaran);
            $sisaAnggaran = $anggaranAcuan - $jumlahRealisasi['total'];

            $pages[] = [
                'subKegiatan' => [
                    'kode' => $subKegiatan->kode,
                    'nama' => $subKegiatan->nama,
                    'pptk_nama' => $subKegiatan->pptk->nama ?? '-',
                    'kegiatan_kode' => $subKegiatan->kegiatan->kode,
                    'kegiatan_nama' => $subKegiatan->kegiatan->nama,
                    'program_kode' => $subKegiatan->kegiatan->program->kode,
                    'program_nama' => $subKegiatan->kegiatan->program->nama,
                ],
                'rka' => [
                    'kode_belanja' => $rka->kode_belanja,
                    'nama_belanja' => $rka->nama_belanja,
                    'anggaran' => $rka->anggaran,
                    'penetapan' => $rka->penetapan,
                    'perubahan' => $rka->perubahan,
                ],
                'spjSebelumnya' => $spjSebelumnya,
                'belanjas' => $belanjas,
                'totalPeriodeIni' => $totalPeriode,
                'jumlahRealisasi' => $jumlahRealisasi,
                'sisaAnggaran' => $sisaAnggaran,
                'anggaranAcuan' => $anggaranAcuan,
            ];
        }

        $this->rkaPages = $pages;
    }

    public function render()
    {
        $penggunaAnggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();
        $bendaharaPengeluaran = PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();

        return view('livewire.laporan.laporan-rincian-obyek', [
            'penggunaAnggaran' => $penggunaAnggaran,
            'bendaharaPengeluaran' => $bendaharaPengeluaran,
        ]);
    }
}
