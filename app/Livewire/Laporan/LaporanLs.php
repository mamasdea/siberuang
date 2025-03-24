<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\BelanjaLs;
use Illuminate\Support\Str;
use App\Jobs\ConvertToPdfLs;
use App\Models\PengelolaKeuangan;
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanLs extends Component
{
    public $laporan_id;
    public $pdfUrl;

    public function mount($laporanId)
    {
        $this->laporanls($laporanId);
    }

    public function laporanls($laporanId)
    {
        $this->laporan_id = $laporanId ?: 'default_value';

        $belanja = BelanjaLs::with([
            'pajakLs',
            'details',
            'details.rka.subKegiatan.kegiatan',
            'details.rka.subKegiatan.pptk'
        ])->findOrFail($laporanId);

        $templatePath = public_path('templates/template-ls.docx');
        if (!file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $no_spp_spm = $belanja->no_bukti;
        $no_pernyataan = str_pad((int)$belanja->no_bukti + 1, 4, '0', STR_PAD_LEFT);
        $no_tanggungjawab = str_pad((int)$belanja->no_bukti + 2, 4, '0', STR_PAD_LEFT);


        $tanggal = Carbon::parse($belanja->tanggal);
        $tanggalIndo = $tanggal->translatedFormat('j F Y');
        $bulanIndo = $tanggal->translatedFormat('F');
        $tahunIndo = $tanggal->translatedFormat('Y');
        $tanggalIndoSingkat = $tanggal->format('d/m/Y');

        $nilaiTotal = $belanja->total_nilai;
        $nilaiTerbilang = ucwords($this->terbilang($nilaiTotal) . ' rupiah');

        $ppn = $belanja->pajakLs->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $belanja->pajakLs->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $belanja->pajakLs->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $belanja->pajakLs->where('jenis_pajak', 'PPh 23')->sum('nominal');

        $totalPajak = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih = $nilaiTotal - $totalPajak;

        $detail = $belanja->details->first();
        $hasSpecificCode = $detail && isset($detail->rka) && Str::startsWith($detail->rka->kode_belanja, '5.1.02.01.');

        // Pengelola Keuangan
        $pengguna_anggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();
        $ppkSkpd = PengelolaKeuangan::where('jabatan', 'PPK-SKPD')->first();
        $bendahara_pengeluaran = PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();
        $pengurus_barang = $hasSpecificCode
            ? PengelolaKeuangan::where('jabatan', 'PENGURUS BARANG')->first()
            : (object)['nama' => '________________', 'nip' => '________________'];

        // Data untuk template
        $data = [
            'no_spp_spm' => $no_spp_spm,
            'no_pernyataan' => $no_pernyataan,
            'no_tanggungjawab' => $no_tanggungjawab,
            'tanggal' => $tanggalIndo,
            'bulan' => $bulanIndo,
            'tahun' => $tahunIndo,
            'tgl_skt' => $tanggalIndoSingkat,
            'nilai' => number_format($nilaiTotal, 0, ',', '.'),
            'nilai_terbilang' => $nilaiTerbilang,
            'uraian' => $belanja->uraian,

            'kode_rka' => optional($detail->rka)->kode_belanja,
            'nama_belanja' => optional($detail->rka)->nama_belanja,
            'penetapan' => number_format(optional($detail->rka)->penetapan ?? 0, 0, ',', '.'),
            'perubahan' => number_format(optional($detail->rka)->perubahan ?? 0, 0, ',', '.'),

            'kode_sub_kegiatan' => optional($detail->rka->subKegiatan)->kode,
            'nama_sub_kegiatan' => optional($detail->rka->subKegiatan)->nama,
            'nama_pptk' => optional($detail->rka->subKegiatan->pptk)->nama,
            'nip_pptk' => optional($detail->rka->subKegiatan->pptk)->nip,
            'kode_kegiatan' => optional($detail->rka->subKegiatan->kegiatan)->kode,
            'nama_kegiatan' => optional($detail->rka->subKegiatan->kegiatan)->nama,

            'ppn' => number_format($ppn, 0, ',', '.'),
            'pph21' => number_format($pph21, 0, ',', '.'),
            'pph22' => number_format($pph22, 0, ',', '.'),
            'pph23' => number_format($pph23, 0, ',', '.'),
            'total_pajak' => number_format($totalPajak, 0, ',', '.'),
            'total_bersih' => number_format($totalBersih, 0, ',', '.'),

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

        // Buat folder jika belum ada
        $reportFolder = storage_path('app/public/reports/ls');
        if (!file_exists($reportFolder)) {
            mkdir($reportFolder, 0755, true);
        }

        $fileName = 'ls_' . $laporanId . '.docx';
        $filePath = $reportFolder . '/' . $fileName;

        // Simpan file Word
        $templateProcessor->saveAs($filePath);

        // Konversi ke PDF
        $fileNamePdf = 'ls_' . $laporanId . '.pdf';
        ConvertToPdfLs::dispatch($fileName, $fileNamePdf, 'app/public/reports/ls/' . $fileName)->onConnection('sync');

        return [
            'word_path' => $fileName,
            'pdf_path' => $fileNamePdf,
        ];
    }

    public function downloadLaporanLs($laporanId)
    {
        $fileName = 'ls_' . $laporanId . '.docx';
        $filePath = storage_path('app/public/reports/ls/' . $fileName);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function getLaporanlsPaths($laporanId)
    {
        $paths = $this->laporanls($laporanId);
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
        return view('livewire.laporan.laporan-ls');
    }
}
