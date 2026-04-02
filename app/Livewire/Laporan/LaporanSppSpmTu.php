<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\SppSpmTu;
use App\Jobs\ConvertToPdfSppSpmTu;
use App\Models\PengelolaKeuangan;
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanSppSpmTu extends Component
{
    public $laporan_id;

    public function laporanSppSpmTu($laporanId)
    {
        $sppSpmTu = SppSpmTu::with(['details.rka.subKegiatan.kegiatan', 'details.rka.subKegiatan.pptk', 'belanjaTus.pajakTus'])
            ->findOrFail($laporanId);

        $templatePath = public_path('templates/template-spp-spm-tu.docx');
        if (!file_exists($templatePath)) abort(404, 'Template tidak ditemukan.');

        $templateProcessor = new TemplateProcessor($templatePath);

        $tanggal = Carbon::parse($sppSpmTu->tanggal);
        $nilaiTotal = $sppSpmTu->total_nilai;

        $detail = $sppSpmTu->details->first();
        $rka = optional($detail)->rka;
        $subKegiatan = optional($rka)->subKegiatan;

        $pengguna_anggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();
        $ppkSkpd = PengelolaKeuangan::where('jabatan', 'PPK-SKPD')->first();
        $bendahara = PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();

        $data = [
            'no_spp_spm' => $sppSpmTu->no_spm_sipd,
            'no_pernyataan' => str_pad((int)$sppSpmTu->no_bukti + 1, 4, '0', STR_PAD_LEFT),
            'no_tanggungjawab' => str_pad((int)$sppSpmTu->no_bukti + 2, 4, '0', STR_PAD_LEFT),
            'tanggal' => $tanggal->translatedFormat('j F Y'),
            'bulan' => $tanggal->translatedFormat('F'),
            'tahun' => $tanggal->translatedFormat('Y'),
            'tgl_skt' => $tanggal->format('d/m/Y'),
            'nilai' => number_format($nilaiTotal, 2, ',', '.'),
            'nilai_terbilang' => ucwords($this->terbilang($nilaiTotal) . ' rupiah'),
            'uraian' => $sppSpmTu->uraian ?? '',
            'kode_rka' => optional($rka)->kode_belanja,
            'nama_belanja' => optional($rka)->nama_belanja,
            'penetapan' => number_format(optional($rka)->penetapan ?? 0, 2, ',', '.'),
            'perubahan' => number_format(optional($rka)->perubahan ?? 0, 2, ',', '.'),
            'kode_sub_kegiatan' => optional($subKegiatan)->kode,
            'nama_sub_kegiatan' => optional($subKegiatan)->nama,
            'nama_pptk' => optional(optional($subKegiatan)->pptk)->nama,
            'nip_pptk' => optional(optional($subKegiatan)->pptk)->nip,
            'kode_kegiatan' => optional(optional($subKegiatan)->kegiatan)->kode,
            'nama_kegiatan' => optional(optional($subKegiatan)->kegiatan)->nama,
            'ppn' => '0,00', 'pph21' => '0,00', 'pph22' => '0,00', 'pph23' => '0,00',
            'total_pajak' => '0,00', 'total_bersih' => number_format($nilaiTotal, 2, ',', '.'),
            'jumlah_pajak' => '0,00', 'sisakas' => '0,00',
            'nama_pa' => $pengguna_anggaran->nama ?? '', 'nip_pa' => $pengguna_anggaran->nip ?? '',
            'nama_bp' => $bendahara->nama ?? '', 'nip_bp' => $bendahara->nip ?? '',
            'nama_ppk' => $ppkSkpd->nama ?? '', 'nip_ppk' => $ppkSkpd->nip ?? '',
            'nama_pb' => '________________', 'nip_pb' => '________________',
        ];

        foreach ($data as $k => $v) $templateProcessor->setValue($k, $v);

        $reportFolder = storage_path('app/public/reports/spp-spm-tu');
        if (!file_exists($reportFolder)) mkdir($reportFolder, 0755, true);

        $fileName = 'spp_spm_tu_' . $laporanId . '.docx';
        $templateProcessor->saveAs($reportFolder . '/' . $fileName);

        $fileNamePdf = 'spp_spm_tu_' . $laporanId . '.pdf';
        ConvertToPdfSppSpmTu::dispatch($fileName, $fileNamePdf, 'app/public/reports/spp-spm-tu/' . $fileName)->onConnection('sync');

        return ['word_path' => $fileName, 'pdf_path' => $fileNamePdf];
    }

    public function downloadLaporanSppSpmTu($laporanId)
    {
        $fileName = 'spp_spm_tu_' . $laporanId . '.docx';
        $filePath = storage_path('app/public/reports/spp-spm-tu/' . $fileName);
        if (!file_exists($filePath)) $this->laporanSppSpmTu($laporanId);
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function getLaporanSppSpmTuPaths($laporanId)
    {
        return $this->laporanSppSpmTu($laporanId);
    }

    public function terbilang($angka)
    {
        $angka = abs($angka);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';
        if ($angka < 12) $temp = $huruf[$angka];
        elseif ($angka < 20) $temp = $this->terbilang($angka - 10) . ' belas';
        elseif ($angka < 100) $temp = $this->terbilang((int)($angka / 10)) . ' puluh ' . $this->terbilang($angka % 10);
        elseif ($angka < 200) $temp = 'seratus ' . $this->terbilang($angka - 100);
        elseif ($angka < 1000) $temp = $this->terbilang((int)($angka / 100)) . ' ratus ' . $this->terbilang($angka % 100);
        elseif ($angka < 2000) $temp = 'seribu ' . $this->terbilang($angka - 1000);
        elseif ($angka < 1000000) $temp = $this->terbilang((int)($angka / 1000)) . ' ribu ' . $this->terbilang($angka % 1000);
        elseif ($angka < 1000000000) $temp = $this->terbilang((int)($angka / 1000000)) . ' juta ' . $this->terbilang($angka % 1000000);
        return trim($temp);
    }

    public function render()
    {
        return view('livewire.laporan.laporan-spp-spm-tu');
    }
}
