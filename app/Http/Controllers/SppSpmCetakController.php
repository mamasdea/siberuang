<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\SppSpmGu;
use App\Models\SppSpmTu;
use App\Models\SppSpmUp;
use App\Models\SppSpmGuNihil;
use App\Models\SppSpmTuNihil;
use App\Traits\AmbiPejabat;

class SppSpmCetakController extends Controller
{
    use AmbiPejabat;

    public function cetakGu($id)
    {
        Carbon::setLocale('id');

        $dokumen = SppSpmGu::with([
            'spjGus.belanjas.rka.subKegiatan.kegiatan',
            'spjGus.belanjas.rka.subKegiatan.pptk',
            'spjGus.belanjas.pajak',
        ])->findOrFail($id);

        $tgl   = Carbon::parse($dokumen->tanggal);
        $tahun = $dokumen->tahun_bukti ?? $tgl->format('Y');
        $jenis = 'GU';

        // ---- SUB KEGIATAN / RKA pertama ----
        $allBelanjas = $dokumen->spjGus->flatMap->belanjas;
        $firstBelanja = $allBelanjas->first();
        $firstRka  = optional($firstBelanja)->rka;
        $subKeg    = optional($firstRka)->subKegiatan;
        $pptk      = optional($subKeg)->pptk;

        $subKegKode = optional($subKeg)->kode ?? '';

        // ---- PAJAK ----
        $allPajaks = $dokumen->spjGus->flatMap->belanjas->flatMap->pajak;
        $ppn   = $allPajaks->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $allPajaks->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $allPajaks->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $allPajaks->where('jenis_pajak', 'PPh 23')->sum('nominal');
        $totalPajak  = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih = $dokumen->total_nilai - $totalPajak;

        // ---- NOMOR ----
        $noSpp        = $dokumen->no_bukti . '/GU/' . $subKegKode . '/' . $tahun;
        $noSpmSipd    = $dokumen->no_spm_sipd ?? '-';
        $noPernyataan = str_pad((int)$dokumen->no_bukti + 1, 4, '0', STR_PAD_LEFT) . '/' . $subKegKode . '/' . $tahun;
        $noTjawab     = str_pad((int)$dokumen->no_bukti + 2, 4, '0', STR_PAD_LEFT) . '/' . $noSpmSipd . '/GU';

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($dokumen->tanggal);
        $pa   = $pejabat['pa'];
        $bp   = $pejabat['bp'];
        $ppk  = $pejabat['ppk'];

        return view('cetak.kwitansi-spp-spm', compact(
            'dokumen', 'jenis', 'tgl', 'tahun',
            'noSpp', 'noSpmSipd', 'noPernyataan', 'noTjawab',
            'subKeg', 'firstRka',
            'ppn', 'pph21', 'pph22', 'pph23', 'totalPajak', 'totalBersih',
            'pa', 'bp', 'ppk', 'pptk'
        ));
    }

    public function cetakTu($id)
    {
        Carbon::setLocale('id');

        $dokumen = SppSpmTu::with([
            'details.rka.subKegiatan.kegiatan',
            'details.rka.subKegiatan.pptk',
            'belanjaTus.pajakTus',
        ])->findOrFail($id);

        $tgl   = Carbon::parse($dokumen->tanggal);
        $tahun = $dokumen->tahun_bukti ?? $tgl->format('Y');
        $jenis = 'TU';

        // ---- SUB KEGIATAN / RKA pertama ----
        $firstDetail = $dokumen->details->first();
        $firstRka    = optional($firstDetail)->rka;
        $subKeg      = optional($firstRka)->subKegiatan;
        $pptk        = optional($subKeg)->pptk;

        $subKegKode = optional($subKeg)->kode ?? '';

        // ---- PAJAK ----
        $allPajaks = $dokumen->belanjaTus->flatMap->pajakTus;
        $ppn   = $allPajaks->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $allPajaks->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $allPajaks->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $allPajaks->where('jenis_pajak', 'PPh 23')->sum('nominal');
        $totalPajak  = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih = $dokumen->total_nilai - $totalPajak;

        // ---- NOMOR ----
        $noSpp        = $dokumen->no_bukti . '/TU/' . $subKegKode . '/' . $tahun;
        $noSpmSipd    = $dokumen->no_spm_sipd ?? '-';
        $noPernyataan = str_pad((int)$dokumen->no_bukti + 1, 4, '0', STR_PAD_LEFT) . '/' . $subKegKode . '/' . $tahun;
        $noTjawab     = str_pad((int)$dokumen->no_bukti + 2, 4, '0', STR_PAD_LEFT) . '/' . $noSpmSipd . '/TU';

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($dokumen->tanggal);
        $pa   = $pejabat['pa'];
        $bp   = $pejabat['bp'];
        $ppk  = $pejabat['ppk'];

        return view('cetak.kwitansi-spp-spm', compact(
            'dokumen', 'jenis', 'tgl', 'tahun',
            'noSpp', 'noSpmSipd', 'noPernyataan', 'noTjawab',
            'subKeg', 'firstRka',
            'ppn', 'pph21', 'pph22', 'pph23', 'totalPajak', 'totalBersih',
            'pa', 'bp', 'ppk', 'pptk'
        ));
    }

    public function cetakUp($id)
    {
        Carbon::setLocale('id');

        $dokumen = SppSpmUp::findOrFail($id);

        $tgl   = Carbon::parse($dokumen->tanggal);
        $tahun = $dokumen->tahun_bukti ?? $tgl->format('Y');
        $jenis = 'UP';

        // UP tidak punya sub kegiatan / RKA
        $firstRka  = null;
        $subKeg    = null;
        $pptk      = null;

        // UP tidak ada pajak
        $ppn   = 0;
        $pph21 = 0;
        $pph22 = 0;
        $pph23 = 0;
        $totalPajak  = 0;
        $totalBersih = $dokumen->total_nilai;

        // ---- NOMOR ----
        $noSpp        = $dokumen->no_bukti . '/UP/' . $tahun;
        $noSpmSipd    = $dokumen->no_spm_sipd ?? '-';
        $noPernyataan = str_pad((int)$dokumen->no_bukti + 1, 4, '0', STR_PAD_LEFT) . '/UP/' . $tahun;
        $noTjawab     = str_pad((int)$dokumen->no_bukti + 2, 4, '0', STR_PAD_LEFT) . '/' . $noSpmSipd . '/UP';

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($dokumen->tanggal);
        $pa   = $pejabat['pa'];
        $bp   = $pejabat['bp'];
        $ppk  = $pejabat['ppk'];

        return view('cetak.kwitansi-spp-spm', compact(
            'dokumen', 'jenis', 'tgl', 'tahun',
            'noSpp', 'noSpmSipd', 'noPernyataan', 'noTjawab',
            'subKeg', 'firstRka',
            'ppn', 'pph21', 'pph22', 'pph23', 'totalPajak', 'totalBersih',
            'pa', 'bp', 'ppk', 'pptk'
        ));
    }

    public function cetakGuNihil($id)
    {
        Carbon::setLocale('id');

        $dokumen = SppSpmGuNihil::with([
            'spjGu.belanjas.rka.subKegiatan.kegiatan',
            'spjGu.belanjas.rka.subKegiatan.pptk',
        ])->findOrFail($id);

        $tgl   = Carbon::parse($dokumen->tanggal);
        $tahun = $dokumen->tahun_bukti ?? $tgl->format('Y');
        $jenis = 'GU Nihil';

        $firstBelanja = optional($dokumen->spjGu)->belanjas?->first();
        $firstRka     = optional($firstBelanja)->rka;
        $subKeg       = optional($firstRka)->subKegiatan;
        $pptk         = optional($subKeg)->pptk;

        // Nihil: tidak ada pajak
        $ppn   = 0;
        $pph21 = 0;
        $pph22 = 0;
        $pph23 = 0;
        $totalPajak  = 0;
        $nilaiSetor  = $dokumen->nilai_setor;
        $totalBersih = $nilaiSetor;

        $noSpp     = $dokumen->no_spp ?? '-';
        $noSpmSipd = $dokumen->no_spm_sipd ?? '-';
        $noPernyataan = '-';
        $noTjawab     = '-';

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($dokumen->tanggal);
        $pa   = $pejabat['pa'];
        $bp   = $pejabat['bp'];
        $ppk  = $pejabat['ppk'];

        return view('cetak.kwitansi-nihil', compact(
            'dokumen', 'jenis', 'tgl', 'tahun',
            'noSpp', 'noSpmSipd', 'noPernyataan', 'noTjawab',
            'subKeg', 'firstRka',
            'ppn', 'pph21', 'pph22', 'pph23', 'totalPajak', 'totalBersih',
            'nilaiSetor',
            'pa', 'bp', 'ppk', 'pptk'
        ));
    }

    public function cetakTuNihil($id)
    {
        Carbon::setLocale('id');

        $dokumen = SppSpmTuNihil::with([
            'sppSpmTu.details.rka.subKegiatan.kegiatan',
            'sppSpmTu.details.rka.subKegiatan.pptk',
        ])->findOrFail($id);

        $tgl   = Carbon::parse($dokumen->tanggal);
        $tahun = $dokumen->tahun_bukti ?? $tgl->format('Y');
        $jenis = 'TU Nihil';

        $firstDetail = optional($dokumen->sppSpmTu)->details?->first();
        $firstRka    = optional($firstDetail)->rka;
        $subKeg      = optional($firstRka)->subKegiatan;
        $pptk        = optional($subKeg)->pptk;

        // Nihil: tidak ada pajak
        $ppn   = 0;
        $pph21 = 0;
        $pph22 = 0;
        $pph23 = 0;
        $totalPajak  = 0;
        $nilaiSetor  = $dokumen->nilai_setor;
        $totalBersih = $nilaiSetor;

        $noSpp     = $dokumen->no_spp ?? '-';
        $noSpmSipd = $dokumen->no_spm_sipd ?? '-';
        $noPernyataan = '-';
        $noTjawab     = '-';

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($dokumen->tanggal);
        $pa   = $pejabat['pa'];
        $bp   = $pejabat['bp'];
        $ppk  = $pejabat['ppk'];

        return view('cetak.kwitansi-nihil', compact(
            'dokumen', 'jenis', 'tgl', 'tahun',
            'noSpp', 'noSpmSipd', 'noPernyataan', 'noTjawab',
            'subKeg', 'firstRka',
            'ppn', 'pph21', 'pph22', 'pph23', 'totalPajak', 'totalBersih',
            'nilaiSetor',
            'pa', 'bp', 'ppk', 'pptk'
        ));
    }
}
