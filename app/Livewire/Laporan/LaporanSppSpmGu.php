<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\SppSpmGu;
use App\Jobs\ConvertToPdfSppSpmGu;
use App\Models\PengelolaKeuangan;
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanSppSpmGu extends Component
{
    public $laporan_id;
    public $pdfUrl;

    public function laporanSppSpmGu($laporanId)
    {
        $this->laporan_id = $laporanId ?: 'default_value';

        $sppSpmGu = SppSpmGu::with([
            'spjGus.belanjas.rka.subKegiatan.kegiatan',
            'spjGus.belanjas.rka.subKegiatan.pptk',
            'spjGus.belanjas.pajak',
        ])->findOrFail($laporanId);

        $templatePath = public_path('templates/template-spp-spm-gu.docx');
        if (!file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $no_spp_spm = $sppSpmGu->no_spm_sipd;
        $no_pernyataan = str_pad((int)$sppSpmGu->no_bukti + 1, 4, '0', STR_PAD_LEFT);
        $no_tanggungjawab = str_pad((int)$sppSpmGu->no_bukti + 2, 4, '0', STR_PAD_LEFT);

        $tanggal = Carbon::parse($sppSpmGu->tanggal);
        $tanggalIndo = $tanggal->translatedFormat('j F Y');
        $bulanIndo = $tanggal->translatedFormat('F');
        $tahunIndo = $tanggal->translatedFormat('Y');
        $tanggalIndoSingkat = $tanggal->format('d/m/Y');

        $nilaiTotal = $sppSpmGu->total_nilai;
        $nilaiTerbilang = ucwords($this->terbilang($nilaiTotal) . ' rupiah');

        // Hitung pajak dari semua belanja di semua SPJ GU
        $allBelanjas = $sppSpmGu->spjGus->flatMap->belanjas;
        $ppn = $allBelanjas->flatMap->pajak->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $allBelanjas->flatMap->pajak->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $allBelanjas->flatMap->pajak->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $allBelanjas->flatMap->pajak->where('jenis_pajak', 'PPh 23')->sum('nominal');

        $totalPajak = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih = $nilaiTotal - $totalPajak;

        // Jumlah pajak dari semua belanja di SPJ GU (penerimaan pajak)
        $jumlahPajak = $allBelanjas->flatMap->pajak->sum('nominal');

        // Sisa kas = 150.000.000 - nilai total
        $sp2d = 150000000;
        $sisaKas = $sp2d - $nilaiTotal;

        // Ambil detail dari belanja pertama untuk data RKA/Sub Kegiatan
        $firstBelanja = $allBelanjas->first();
        $rka = optional($firstBelanja)->rka;
        $subKegiatan = optional($rka)->subKegiatan;
        $kegiatan = optional($subKegiatan)->kegiatan;

        // Pengelola Keuangan
        $pengguna_anggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();
        $ppkSkpd = PengelolaKeuangan::where('jabatan', 'PPK-SKPD')->first();
        $bendahara_pengeluaran = PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();

        $hasSpecificCode = $rka && \Illuminate\Support\Str::startsWith($rka->kode_belanja, '5.1.02.01.');
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
            'nilai' => number_format($nilaiTotal, 2, ',', '.'),
            'nilai_terbilang' => $nilaiTerbilang,
            'uraian' => $sppSpmGu->uraian ?? '',

            'kode_rka' => optional($rka)->kode_belanja,
            'nama_belanja' => optional($rka)->nama_belanja,
            'penetapan' => number_format(optional($rka)->penetapan ?? 0, 2, ',', '.'),
            'perubahan' => number_format(optional($rka)->perubahan ?? 0, 2, ',', '.'),

            'kode_sub_kegiatan' => optional($subKegiatan)->kode,
            'nama_sub_kegiatan' => optional($subKegiatan)->nama,
            'nama_pptk' => optional(optional($subKegiatan)->pptk)->nama,
            'nip_pptk' => optional(optional($subKegiatan)->pptk)->nip,
            'kode_kegiatan' => optional($kegiatan)->kode,
            'nama_kegiatan' => optional($kegiatan)->nama,

            'ppn' => number_format($ppn, 2, ',', '.'),
            'pph21' => number_format($pph21, 2, ',', '.'),
            'pph22' => number_format($pph22, 2, ',', '.'),
            'pph23' => number_format($pph23, 2, ',', '.'),
            'total_pajak' => number_format($totalPajak, 2, ',', '.'),
            'total_bersih' => number_format($totalBersih, 2, ',', '.'),
            'jumlah_pajak' => number_format($jumlahPajak, 2, ',', '.'),
            'sisakas' => number_format($sisaKas, 2, ',', '.'),

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
        $reportFolder = storage_path('app/public/reports/spp-spm-gu');
        if (!file_exists($reportFolder)) {
            mkdir($reportFolder, 0755, true);
        }

        $fileName = 'spp_spm_gu_' . $laporanId . '.docx';
        $filePath = $reportFolder . '/' . $fileName;

        // Simpan file Word
        $templateProcessor->saveAs($filePath);

        // Konversi ke PDF
        $fileNamePdf = 'spp_spm_gu_' . $laporanId . '.pdf';
        ConvertToPdfSppSpmGu::dispatch($fileName, $fileNamePdf, 'app/public/reports/spp-spm-gu/' . $fileName)->onConnection('sync');

        return [
            'word_path' => $fileName,
            'pdf_path' => $fileNamePdf,
        ];
    }

    public function downloadLaporanSppSpmGu($laporanId)
    {
        $fileName = 'spp_spm_gu_' . $laporanId . '.docx';
        $filePath = storage_path('app/public/reports/spp-spm-gu/' . $fileName);

        if (!file_exists($filePath)) {
            // Generate dulu kalau belum ada
            $this->laporanSppSpmGu($laporanId);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function getLaporanSppSpmGuPaths($laporanId)
    {
        $paths = $this->laporanSppSpmGu($laporanId);
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
        return view('livewire.laporan.laporan-spp-spm-gu');
    }
}
