<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\BelanjaLs;
use App\Models\BelanjaLsDetails;
use App\Traits\AmbiPejabat;
use Illuminate\Support\Str;

class BelanjaLsCetakController extends Controller
{
    use AmbiPejabat;

    public function cetak($id)
    {
        Carbon::setLocale('id');

        $belanja = BelanjaLs::with([
            'details.rka.subKegiatan.kegiatan.program',
            'details.rka.subKegiatan.pptk',
            'pajakLs',
        ])->findOrFail($id);

        $tgl   = Carbon::parse($belanja->tanggal);
        $tahun = $tgl->format('Y');

        // ---- PAJAK ----
        $ppn   = $belanja->pajakLs->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $belanja->pajakLs->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $belanja->pajakLs->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $belanja->pajakLs->where('jenis_pajak', 'PPh 23')->sum('nominal');
        $totalPajak  = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih = $belanja->total_nilai - $totalPajak;

        // ---- DATA RKA PERTAMA (untuk sub kegiatan / pptk) ----
        $firstDetail  = $belanja->details->first();
        $firstRka     = optional($firstDetail)->rka;
        $subKeg       = optional($firstRka)->subKegiatan;
        $kegiatan     = optional($subKeg)->kegiatan;
        $pptk         = optional($subKeg)->pptk;

        $hasSpecificCode = $firstRka && Str::startsWith($firstRka->kode_belanja, '5.1.02.01.');

        // ---- PEJABAT ----
        $pejabat = $this->ambilPejabat($belanja->tanggal);
        $pa  = $pejabat['pa'];
        $bp  = $pejabat['bp'];
        $ppk = $pejabat['ppk'];
        $pb  = $hasSpecificCode
            ? $pejabat['pb']
            : (object)['nama' => null, 'nip' => null];

        // ---- REALISASI PER RKA ----
        $detailsWithRealisasi = $belanja->details->map(function ($detail) use ($belanja) {
            $rka = $detail->rka;

            $lsSebelumnya = BelanjaLsDetails::where('rka_id', $rka->id)
                ->whereHas('belanjaLs', fn($q) => $q->where('tanggal', '<', $belanja->tanggal)
                    ->orWhere(fn($q2) => $q2->where('tanggal', $belanja->tanggal)->where('id', '<', $belanja->id)))
                ->sum('nilai');

            $lsSdIni = BelanjaLsDetails::where('rka_id', $rka->id)
                ->whereHas('belanjaLs', fn($q) => $q->where('tanggal', '<', $belanja->tanggal)
                    ->orWhere(fn($q2) => $q2->where('tanggal', $belanja->tanggal)->where('id', '<=', $belanja->id)))
                ->sum('nilai');

            $sisaSdIni = ($rka->anggaran ?? 0) - $lsSdIni;

            return [
                'detail'        => $detail,
                'rka'           => $rka,
                'ls_sebelumnya' => $lsSebelumnya,
                'ls_sd_ini'     => $lsSdIni,
                'sisa_sd_ini'   => $sisaSdIni,
            ];
        });

        return view('cetak.kwitansi-ls', compact(
            'belanja', 'tgl', 'tahun',
            'ppn', 'pph21', 'pph22', 'pph23',
            'totalPajak', 'totalBersih',
            'pa', 'bp', 'ppk', 'pb', 'pptk',
            'firstDetail', 'firstRka', 'subKeg', 'kegiatan',
            'detailsWithRealisasi',
        ));
    }
}
