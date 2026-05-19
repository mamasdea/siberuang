<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Belanja;
use App\Models\BelanjaKkpd;
use App\Models\BelanjaLsDetails;
use App\Traits\AmbiPejabat;
use Illuminate\Support\Str;

class BelanjaCetakController extends Controller
{
    use AmbiPejabat;

    public function cetak($id)
    {
        Carbon::setLocale('id');

        $belanja = Belanja::with([
            'rka.subKegiatan.kegiatan.program',
            'rka.subKegiatan.pptk',
            'pajak',
            'penerimaan.penerima',
        ])->findOrFail($id);

        $tgl   = Carbon::parse($belanja->tanggal);
        $tahun = $tgl->format('Y');

        // ---- PAJAK ----
        $ppn   = $belanja->pajak->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $belanja->pajak->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $belanja->pajak->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $belanja->pajak->where('jenis_pajak', 'PPh 23')->sum('nominal');
        $totalPajak      = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih     = $belanja->nilai - $totalPajak;
        $totalPenerimaan = $belanja->penerimaan->sum('nominal');
        $totalNominal    = $totalPenerimaan + $totalPajak;

        $sortedPajaks = $belanja->pajak->sortBy(
            fn($p) => ['PPN' => 1, 'PPh 21' => 2, 'PPh 22' => 3, 'PPh 23' => 4][$p->jenis_pajak] ?? 9
        )->values();

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($belanja->tanggal);
        $pa  = $pejabat['pa'];
        $bp  = $pejabat['bp'];
        $ppk = $pejabat['ppk'];
        $pptk = $belanja->rka->subKegiatan->pptk ?? null;

        $hasSpecificCode = Str::startsWith($belanja->rka->kode_belanja, '5.1.02.01.');
        $pb = $hasSpecificCode
            ? $pejabat['pb']
            : (object)['nama' => null, 'nip' => null];

        // ---- REALISASI SEBELUMNYA (sebelum transaksi ini) ----
        $guSebelumnya = Belanja::where('rka_id', $belanja->rka_id)
            ->where(function ($q) use ($belanja) {
                $q->where('tanggal', '<', $belanja->tanggal)
                  ->orWhere(fn($q2) => $q2
                      ->where('tanggal', $belanja->tanggal)
                      ->where('no_bukti', '<', $belanja->no_bukti));
            })->sum('nilai');

        $kkpdSebelumnya = BelanjaKkpd::where('rka_id', $belanja->rka_id)
            ->where(function ($q) use ($belanja) {
                $q->where('tanggal', '<', $belanja->tanggal)
                  ->orWhere(fn($q2) => $q2
                      ->where('tanggal', $belanja->tanggal)
                      ->where('no_bukti', '<', $belanja->no_bukti));
            })->sum('nilai');

        $lsSebelumnya = BelanjaLsDetails::where('rka_id', $belanja->rka_id)
            ->whereHas('belanjaLs', fn($q) => $q->where('tanggal', '<', $belanja->tanggal))
            ->sum('nilai');

        $realisasiTotalSebelumnya = $guSebelumnya + $kkpdSebelumnya + $lsSebelumnya;

        // ---- REALISASI S.D. TRANSAKSI INI ----
        $guSaatIni = Belanja::where('rka_id', $belanja->rka_id)
            ->where(function ($q) use ($belanja) {
                $q->where('tanggal', '<', $belanja->tanggal)
                  ->orWhere(fn($q2) => $q2
                      ->where('tanggal', $belanja->tanggal)
                      ->where('no_bukti', '<=', $belanja->no_bukti));
            })->sum('nilai');

        $kkpdSaatIni = BelanjaKkpd::where('rka_id', $belanja->rka_id)
            ->where('tanggal', '<=', $belanja->tanggal)
            ->sum('nilai');

        $lsSaatIni = BelanjaLsDetails::where('rka_id', $belanja->rka_id)
            ->whereHas('belanjaLs', fn($q) => $q->where('tanggal', '<=', $belanja->tanggal))
            ->sum('nilai');

        $realisasiTotalSaatIni = $guSaatIni + $kkpdSaatIni + $lsSaatIni;

        $sisaSebelum = $belanja->rka->anggaran - $realisasiTotalSebelumnya;
        $sisaSesudah = $belanja->rka->anggaran - $realisasiTotalSaatIni;

        // ---- PENERIMA (untuk pemindahbukuan) ----
        $firstPenerima = $belanja->penerimaan->first();
        $bankTujuan    = $firstPenerima?->penerima->bank ?? 'Bank Tujuan';

        return view('cetak.kwitansi-belanja', compact(
            'belanja', 'tgl', 'tahun',
            'ppn', 'pph21', 'pph22', 'pph23',
            'totalPajak', 'totalBersih', 'totalPenerimaan', 'totalNominal',
            'sortedPajaks',
            'pa', 'bp', 'ppk', 'pb', 'pptk',
            'guSebelumnya', 'realisasiTotalSebelumnya',
            'guSaatIni', 'realisasiTotalSaatIni',
            'sisaSebelum', 'sisaSesudah',
            'bankTujuan',
        ));
    }
}
