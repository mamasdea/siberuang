<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\SppSpmUp;
use App\Jobs\ConvertToPdfSppSpmUp;
use App\Models\PengelolaKeuangan;
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanSppSpmUp extends Component
{
    public $laporan_id;
    public $pdfUrl;

    public function laporanSppSpmUp($laporanId)
    {
        $this->laporan_id = $laporanId ?: 'default_value';

        $sppSpmUp = SppSpmUp::findOrFail($laporanId);

        $templatePath = public_path('templates/template-spp-spm-up.docx');
        if (!file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $no_spp_spm = $sppSpmUp->no_spm_sipd;
        $no_pernyataan = str_pad((int)$sppSpmUp->no_bukti + 1, 4, '0', STR_PAD_LEFT);
        $no_tanggungjawab = str_pad((int)$sppSpmUp->no_bukti + 2, 4, '0', STR_PAD_LEFT);

        $tanggal = Carbon::parse($sppSpmUp->tanggal);
        $tanggalIndo = $tanggal->translatedFormat('j F Y');
        $bulanIndo = $tanggal->translatedFormat('F');
        $tahunIndo = $tanggal->translatedFormat('Y');
        $tanggalIndoSingkat = $tanggal->format('d/m/Y');

        $nilaiTotal = $sppSpmUp->total_nilai;
        $nilaiTerbilang = ucwords($this->terbilang($nilaiTotal) . ' rupiah');

        // Pengelola Keuangan
        $pengguna_anggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();
        $ppkSkpd = PengelolaKeuangan::where('jabatan', 'PPK-SKPD')->first();
        $bendahara_pengeluaran = PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();
        $pengurus_barang = (object)['nama' => '________________', 'nip' => '________________'];

        // Data untuk template
        $data = [
            'no_spp_spm' => $no_spp_spm,
            'no_pernyataan' => $no_pernyataan,
            'no_tanggungjawab' => $no_tanggungjawab,
            'tanggal' => $tanggalIndo,
            'bulan' => $bulanIndo,
            'tahun' => $tahunIndo,
            'tgl_skt' => $tanggalIndoSingkat,
            'nilai' => number_format($nilaiTotal, 2, ',', '.'),
            'nilai_terbilang' => $nilaiTerbilang,
            'uraian' => $sppSpmUp->uraian ?? 'Uang Persediaan',

            'kode_rka' => '',
            'nama_belanja' => '',
            'penetapan' => '0,00',
            'perubahan' => '0,00',

            'kode_sub_kegiatan' => '',
            'nama_sub_kegiatan' => '',
            'nama_pptk' => '',
            'nip_pptk' => '',
            'kode_kegiatan' => '',
            'nama_kegiatan' => '',

            'ppn' => '0,00',
            'pph21' => '0,00',
            'pph22' => '0,00',
            'pph23' => '0,00',
            'total_pajak' => '0,00',
            'total_bersih' => number_format($nilaiTotal, 2, ',', '.'),
            'jumlah_pajak' => '0,00',
            'sisakas' => '0,00',

            'nama_pa' => $pengguna_anggaran->nama ?? '',
            'nip_pa' => $pengguna_anggaran->nip ?? '',
            'nama_bp' => $bendahara_pengeluaran->nama ?? '',
            'nip_bp' => $bendahara_pengeluaran->nip ?? '',
            'nama_ppk' => $ppkSkpd->nama ?? '',
            'nip_ppk' => $ppkSkpd->nip ?? '',
            'nama_pb' => $pengurus_barang->nama ?? '',
            'nip_pb' => $pengurus_barang->nip ?? '',
        ];

        foreach ($data as $placeholder => $value) {
            $templateProcessor->setValue($placeholder, $value);
        }

        $reportFolder = storage_path('app/public/reports/spp-spm-up');
        if (!file_exists($reportFolder)) {
            mkdir($reportFolder, 0755, true);
        }

        $fileName = 'spp_spm_up_' . $laporanId . '.docx';
        $filePath = $reportFolder . '/' . $fileName;

        $templateProcessor->saveAs($filePath);

        $fileNamePdf = 'spp_spm_up_' . $laporanId . '.pdf';
        ConvertToPdfSppSpmUp::dispatch($fileName, $fileNamePdf, 'app/public/reports/spp-spm-up/' . $fileName)->onConnection('sync');

        return [
            'word_path' => $fileName,
            'pdf_path' => $fileNamePdf,
        ];
    }

    public function downloadLaporanSppSpmUp($laporanId)
    {
        $fileName = 'spp_spm_up_' . $laporanId . '.docx';
        $filePath = storage_path('app/public/reports/spp-spm-up/' . $fileName);

        if (!file_exists($filePath)) {
            $this->laporanSppSpmUp($laporanId);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function getLaporanSppSpmUpPaths($laporanId)
    {
        $paths = $this->laporanSppSpmUp($laporanId);
        return [
            'word_path' => $paths['word_path'],
            'pdf_path' => $paths['pdf_path'],
        ];
    }

    public function terbilang($angka)
    {
        $angka = abs($angka);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';

        if ($angka < 12) {
            $temp = $huruf[$angka];
        } elseif ($angka < 20) {
            $temp = $this->terbilang($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            $temp = $this->terbilang((int)($angka / 10)) . ' puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            $temp = 'seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $temp = $this->terbilang((int)($angka / 100)) . ' ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $temp = 'seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $temp = $this->terbilang((int)($angka / 1000)) . ' ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $temp = $this->terbilang((int)($angka / 1000000)) . ' juta ' . $this->terbilang($angka % 1000000);
        }

        return trim($temp);
    }

    public function render()
    {
        return view('livewire.laporan.laporan-spp-spm-up');
    }
}
