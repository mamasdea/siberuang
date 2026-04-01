<?php

namespace App\Livewire\Laporan;

use App\Models\Belanja;
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
            ->whereHas('rkas.belanjas', function ($q) {
                $q->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
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
            ->whereHas('belanjas', function ($q) {
                $q->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir]);
            })
            ->orderBy('kode_belanja', 'asc')
            ->get();

        $pages = [];

        foreach ($rkas as $rka) {
            $spjSebelumnya = Belanja::where('rka_id', $rka->id)
                ->where('tanggal', '<', $this->periodeAwal)
                ->sum('nilai');

            $belanjas = Belanja::where('rka_id', $rka->id)
                ->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
                ->orderBy('tanggal', 'asc')
                ->get();

            $totalPeriodeIni = $belanjas->sum('nilai');
            $jumlahRealisasi = $spjSebelumnya + $totalPeriodeIni;
            // Sisa dihitung dari DPPA (perubahan), jika 0 maka dari DPA (penetapan)
            $anggaranAcuan = ($rka->perubahan ?? 0) > 0 ? $rka->perubahan : ($rka->penetapan ?? $rka->anggaran);
            $sisaAnggaran = $anggaranAcuan - $jumlahRealisasi;

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
                'belanjas' => $belanjas->map(fn($b) => [
                    'tanggal' => $b->tanggal,
                    'no_bukti' => $b->no_bukti,
                    'uraian' => $b->uraian,
                    'nilai' => $b->nilai,
                ])->toArray(),
                'totalPeriodeIni' => $totalPeriodeIni,
                'jumlahRealisasi' => $jumlahRealisasi,
                'sisaAnggaran' => $sisaAnggaran,
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
