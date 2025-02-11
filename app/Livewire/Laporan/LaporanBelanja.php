<?php

namespace App\Livewire\Laporan;

use Carbon\Carbon;
use App\Models\Rka;
use App\Models\Pajak;
use App\Models\Belanja;
use Livewire\Component;
use App\Jobs\ConvertToPdf;
use Illuminate\Support\Str;
use App\Models\PengelolaKeuangan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanBelanja extends Component
{
    public $laporan_id;
    public $pdfUrl;

    public function mount($laporanId)
    {
        $this->kwitansiDinas($laporanId);
    }

    public function kwitansiDinas($laporanId)
    {
        $this->laporan_id = $laporanId ?: 'default_value';
        $belanja = Belanja::with(['rka.subKegiatan.kegiatan', 'rka.subKegiatan.pptk', 'pajak', 'penerimaan.penerima'])->findOrFail($laporanId);

        $templatePath = storage_path('app/templates/kwitansi_dinas.docx');

        if (!file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan.');
        }
        $templateProcessor = new TemplateProcessor($templatePath);
        $ppn = $belanja->pajak->where('jenis_pajak', 'PPN')->sum('nominal');
        $pph21 = $belanja->pajak->where('jenis_pajak', 'PPh 21')->sum('nominal');
        $pph22 = $belanja->pajak->where('jenis_pajak', 'PPh 22')->sum('nominal');
        $pph23 = $belanja->pajak->where('jenis_pajak', 'PPh 23')->sum('nominal');
        $kotor = $belanja->nilai;
        $totalPajak = $ppn + $pph21 + $pph22 + $pph23;
        $totalBersih = $kotor - $totalPajak;
        $tanggalIndo = Carbon::parse($belanja->tanggal)->translatedFormat('j F Y');
        $bulanIndo = Carbon::parse($belanja->tanggal)->translatedFormat('F');
        $tahunIndo = Carbon::parse($belanja->tanggal)->translatedFormat('Y');
        $tanggalIndoSingkat = Carbon::parse($belanja->tanggal)->format('d/m/Y');
        $nilaiTerbilang = $this->terbilang($belanja->nilai) . ' rupiah';

        $pengguna_anggaran = PengelolaKeuangan::where('jabatan', 'PENGGUNA ANGGARAN')->first();
        $bendahara_pengeluaran = PengelolaKeuangan::where('jabatan', 'BENDAHARA PENGELUARAN')->first();

        // $pengurus_barang = PengelolaKeuangan::where('jabatan', 'PENGURUS BARANG')->first();
        $hasSpecificCode = Str::startsWith($belanja->rka->kode_belanja, '5.1.02.01.');
        $pengurus_barang = $hasSpecificCode
            ? PengelolaKeuangan::where('jabatan', 'PENGURUS BARANG')->first()
            : (object)['nama' => '________________', 'nip' => '________________'];


        // Misalnya, $belanja adalah record transaksi GU yang sedang dihitung
        $anggaranAwal = $belanja->rka->anggaran;

        // Total realisasi dari transaksi GU:
        // Hanya menghitung transaksi dengan tanggal dan no_bukti kurang atau sama dengan transaksi saat ini.
        $realisasiGU = \App\Models\Belanja::where('rka_id', $belanja->rka->id)
            ->where('tanggal', '<=', $belanja->tanggal)
            ->where('no_bukti', '<=', $belanja->no_bukti)
            ->sum('nilai');

        // Total realisasi dari transaksi LS:
        // Menggunakan relasi ke header BelanjaLs untuk memfilter berdasarkan tanggal dan no_bukti.
        $realisasiLS = \App\Models\BelanjaLsDetails::where('rka_id', $belanja->rka->id)
            ->whereHas('belanjaLs', function ($query) use ($belanja) {
                $query->where('tanggal', '<=', $belanja->tanggal)
                    ->where('no_bukti', '<=', $belanja->no_bukti);
            })
            ->sum('nilai');

        // Total realisasi adalah jumlah transaksi GU dan LS yang memenuhi kedua kondisi di atas.
        $realisasiTotal = $realisasiGU + $realisasiLS;

        // Sisa realisasi anggaran adalah anggaran awal dikurangi total realisasi
        $sisaRealisasi = $anggaranAwal - $realisasiTotal;

        // Jika Anda ingin menghitung "sisa sebelumnya" (misalnya, sebelum transaksi ini ditambahkan),
        // Anda bisa menambahkan nilai transaksi saat ini ke sisa realisasi:
        $sisaSebelumnya = $sisaRealisasi + $belanja->nilai;



        // Hitung sisa anggaran

        $data = [
            'no_bukti' => $belanja->no_bukti,
            'tanggal' => $tanggalIndo,
            'bulan' => $bulanIndo,
            'tahun' => $tahunIndo,
            'tgl_skt' => $tanggalIndoSingkat,
            'nilai' => number_format($belanja->nilai, 0, ',', '.'),
            'nilai_terbilang' => ucwords($nilaiTerbilang),
            'uraian' => $belanja->uraian,
            'kode_rka' => $belanja->rka->kode_belanja,
            'nama_belanja' => $belanja->rka->nama_belanja,
            'anggaran' => number_format($belanja->rka->anggaran, 0, ',', '.'),
            'kode_sub_kegiatan' => $belanja->rka->subKegiatan->kode,
            'nama_sub_kegiatan' => $belanja->rka->subKegiatan->nama,
            'nama_pptk' => $belanja->rka->subKegiatan->pptk->nama,
            'nip_pptk' => $belanja->rka->subKegiatan->pptk->nip,
            'kode_kegiatan' => $belanja->rka->subKegiatan->kegiatan->kode,
            'nama_kegiatan' => $belanja->rka->subKegiatan->kegiatan->nama,
            'ppn' => number_format($ppn, 0, ',', '.'),
            'pph21' => number_format($pph21, 0, ',', '.'),
            'pph22' => number_format($pph22, 0, ',', '.'),
            'pph23' => number_format($pph23, 0, ',', '.'),
            'total_pajak' => number_format($totalPajak, 0, ',', '.'),
            'total_bersih' => number_format($totalBersih, 0, ',', '.'),
            'nama_pa' => $pengguna_anggaran->nama,
            'nip_pa' => $pengguna_anggaran->nip,
            'nama_bp' => $bendahara_pengeluaran->nama,
            'nip_bp' => $bendahara_pengeluaran->nip,
            'nama_pb' => $pengurus_barang->nama,
            'nip_pb' => $pengurus_barang->nip,
            'sisa_sebelum' => number_format($sisaSebelumnya, 0, ',', '.'),
            'sisa_sesudah' => number_format($sisaRealisasi, 0, ',', '.'),
        ];

        foreach ($data as $placeholder => $value) {
            $templateProcessor->setValue($placeholder, $value);
        }

        $templateProcessor->cloneRow('no_urut', $belanja->penerimaan->count());

        foreach ($belanja->penerimaan as $index => $penerimaan) {
            $indexPlusOne = $index + 1;
            $templateProcessor->setValue("no_urut#{$indexPlusOne}", $indexPlusOne);
            $templateProcessor->setValue("nama_penerima#{$indexPlusOne}", $penerimaan->penerima->nama);
            $templateProcessor->setValue("no_rekening#{$indexPlusOne}", $penerimaan->penerima->no_rekening);
            $templateProcessor->setValue("bank_penerima#{$indexPlusOne}", $penerimaan->penerima->bank);
            $templateProcessor->setValue("nominal_penerimaan#{$indexPlusOne}", number_format($penerimaan->nominal, 0, ',', '.'));
        }

        $pajaks = Pajak::where('belanja_id', $laporanId)->get();
        $sortedPajaks = $pajaks->sortBy(function ($pajak) {
            $order = [
                'PPN' => 1,
                'PPh 21' => 2,
                'PPh 22' => 3,
                'PPh 23' => 4,
            ];
            return $order[$pajak->jenis_pajak] ?? 999;
        })->values();

        $templateProcessor->cloneRow('jenis_pajak', $sortedPajaks->count());

        foreach ($sortedPajaks as $index => $pajak) {
            $indexPlusOne = $index + 1;
            $templateProcessor->setValue("jenis_pajak#{$indexPlusOne}", $pajak->jenis_pajak);
            $templateProcessor->setValue("no_billing#{$indexPlusOne}", $pajak->no_billing);
            $templateProcessor->setValue("nominal_pajak#{$indexPlusOne}", number_format($pajak->nominal, 0, ',', '.'));
        }



        $jumlahPenerimaan = $belanja->penerimaan->sum('nominal');
        $jumlahPajak = $belanja->pajak->sum('nominal');
        $totalNominal = $jumlahPenerimaan + $jumlahPajak;
        $templateProcessor->setValue('total_nominal', number_format($totalNominal, 0, ',', '.'));
        $totalTerbilang = $this->terbilang($totalNominal) . ' rupiah';
        $templateProcessor->setValue('total_terbilang', ucwords($totalTerbilang));
        $jumlahPenerimaan = $belanja->penerimaan->sum('nominal');
        $jumlahPajak = $belanja->pajak->sum('nominal');
        $totalNominal = $jumlahPenerimaan + $jumlahPajak;
        $templateProcessor->setValue('total_nominal', number_format($totalNominal, 0, ',', '.'));

        $pathWord = $laporanId . '.docx';

        $outputPath = storage_path('app/public/reports/laporan_belanja_' . $pathWord);
        $templateProcessor->saveAs($outputPath);

        $path_pdf = $laporanId . '.pdf';
        ConvertToPdf::dispatch($pathWord, $path_pdf, 'app/public/reports/laporan_belanja_' . $pathWord)->onConnection('sync');
        return [
            'word_path' => $pathWord,
            'pdf_path' => $path_pdf,
        ];

        // Return the file as a download response
        // return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function downloadKwitansiDinas($laporanId)
    {
        // Panggil method kwitansiDinas untuk menghasilkan file
        $this->kwitansiDinas($laporanId);

        $pathWord = storage_path('app/public/reports/laporan_belanja_' . $laporanId . '.docx');

        // Periksa apakah file ada
        if (!file_exists($pathWord)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Mengembalikan file sebagai respons download dan menghapus file setelah dikirim
        return response()->download($pathWord)->deleteFileAfterSend(true);
    }

    public function getKwitansiDinasPaths($laporanId)
    {
        // Panggil method kwitansiDinas untuk menghasilkan file
        $paths = $this->kwitansiDinas($laporanId);

        // Mengembalikan array dengan path word dan pdf
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
        return view('livewire.laporan.laporan-belanja');
    }
}
