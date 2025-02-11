<?php

namespace App\Livewire\Laporan;

use App\Models\Belanja;
use App\Models\Rka;
use App\Models\SubKegiatan;
use Livewire\Component;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Table;

class LaporanNPD extends Component
{
    public $selectedSubKegiatan;
    public $bulanList = [        // Daftar bulan untuk dropdown
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public $selectedBulan;


    public function exportLaporanNPD($selectedBulan)
    {
        // Ambil data sub kegiatan yang dipilih berdasarkan ID
        $subKegiatan = SubKegiatan::with('kegiatan.program')->find($this->selectedSubKegiatan);

        if (!$subKegiatan) {
            abort(404, 'Sub Kegiatan tidak ditemukan.');
        }

        // Load template PHPWord
        $templatePath = storage_path('app/templates/npd.docx');

        // Cek apakah template ada
        if (!file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        // Membuat instance TemplateProcessor
        $templateProcessor = new TemplateProcessor($templatePath);

        // Data yang akan dimasukkan ke template
        $data = [
            'id' => $subKegiatan->id,
            'kegiatan_id' => $subKegiatan->kegiatan_id,
            'kode' => $subKegiatan->kode,
            'nama' => $subKegiatan->nama,
            'nama_kegiatan' => $subKegiatan->kegiatan->nama,
            'kode_kegiatan' => $subKegiatan->kegiatan->kode,
            'nama_program' => $subKegiatan->kegiatan->program->nama,
            'kode_program' => $subKegiatan->kegiatan->program->kode,
        ];


        foreach ($data as $placeholder => $value) {
            $templateProcessor->setValue($placeholder, $value);
        }


        $rkas = Rka::where('sub_kegiatan_id', $subKegiatan->id)->get();

        // $belanja = Belanja::with('rka')->whereIn('rka_id', $rkas->pluck('id'))->get();

        $kampret2 = [];

        foreach ($rkas as $index => $rka) {
            // dd($selectedBulan);

            // dd($rka->rka_id);
            // $realisasi = $belanja->where('rka_id', $rka->id)->sum('nilai');
            // $realisasi_bulan = $belanja->where('rka_id', $rka->id)->whereMonth('tanggal', $selectedBulan)->whereYear('tanggal', date('Y'))->sum('nilai');
            // $realisasi_bulan_sebelum = $belanja->where('rka_id', $rka->id)->whereMonth('tanggal', '<', $selectedBulan)->whereYear('tanggal', date('Y'))->sum('nilai');
            $results = Belanja::join('rkas', 'belanjas.rka_id', '=', 'rkas.id')
                ->where('belanjas.rka_id', $rka->id)
                ->whereMonth('belanjas.tanggal', $selectedBulan)->whereYear('belanjas.tanggal', date('Y'))
                ->select('rkas.kode_belanja', 'rkas.nama_belanja', 'rkas.anggaran', DB::raw('SUM(belanjas.nilai) as nilai'))
                ->groupBy('rkas.kode_belanja', 'rkas.nama_belanja', 'rkas.anggaran')
                ->first();
            // dd($results);
            $lama = Belanja::join('rkas', 'belanjas.rka_id', '=', 'rkas.id')
                ->where('belanjas.rka_id', $rka->id)
                ->whereMonth('belanjas.tanggal', '<', $selectedBulan)->whereYear('belanjas.tanggal', date('Y'))
                ->select('rkas.kode_belanja', 'rkas.nama_belanja', 'rkas.anggaran', DB::raw('SUM(belanjas.nilai) as nilai'))
                ->groupBy('rkas.kode_belanja', 'rkas.nama_belanja', 'rkas.anggaran')
                ->first();



            $dewa = [
                'no' => $index + 1,
                'kode_belanja' => $rka->kode_belanja,
                'nama_belanja' => $rka->nama_belanja,
                'anggaran_rka' => number_format($rka->anggaran ?? 0, 0, ',', '.'),
                'realisasi_bln_ini' => number_format($results->nilai ?? 0, 0, ',', '.'),
                'realisasi_bln_sebelumnya' => number_format($lama->nilai ?? 0, 0, ',', '.'),
            ];


            array_push($kampret2, $dewa);
        }

        // Simpan file yang dihasilkan
        $fileName = 'sub_kegiatan_report_' . $subKegiatan->kode . '.docx';
        $outputPath = storage_path('app/public/reports/' . $fileName);
        $templateProcessor->cloneBlock('block_name', count($kampret2), true, false, $kampret2);
        $templateProcessor->saveAs($outputPath);

        // Return response untuk mendownload file
        return response()->download($outputPath);
    }

    public function anyar()
    {
        $subKegiatan = SubKegiatan::with([
            'kegiatan.program',
            'rkas' => function ($query) {
                $query->withSum(['belanjas as baru' => function ($subQuery) {
                    $subQuery->whereMonth('tanggal', $this->selectedBulan);
                }], 'nilai')
                    ->withSum(['belanjas as lama' => function ($subQuery) {
                        $subQuery->whereMonth('tanggal', '<', $this->selectedBulan);
                    }], 'nilai')
                    ->with(['belanjas' => function ($a) {
                        $a->whereMonth('tanggal', $this->selectedBulan)->whereYear('tanggal', date('Y'));
                    }]);
            }
        ])->find($this->selectedSubKegiatan);

        // Buat dokumen Word
        $phpWord = new PhpWord();
        $sectionStyle = array(
            'orientation' => 'landscape', // Set to landscape
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600
        );
        $section = $phpWord->addSection($sectionStyle);

        // Tambahkan judul di tengah
        $section->addText(
            "NOTA PENCAIRAN DANA (NPD)",
            ['bold' => true, 'size' => 16],
            ['align' => 'center'] // Rata tengah
        );

        // Tabel untuk detail informasi program dengan 3 kolom (label, pemisah ":", nilai)
        $detailTableStyle = [
            'cellMargin' => 50
        ];
        $phpWord->addTableStyle('DetailTable', $detailTableStyle);
        $detailTable = $section->addTable('DetailTable');

        // Tambahkan baris dan sel untuk setiap informasi detail
        // Add a row with reduced spacing
        $detailTable->addRow(null, ['exactHeight' => true, 'height' => 200]);

        $detailTable->addCell(2000)->addText("PROGRAM", ['bold' => true], ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(500)->addText(":", null, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(15000)->addText($subKegiatan->kegiatan->program->kode . ' - ' . $subKegiatan->kegiatan->program->nama, null, ['spaceAfter' => 0, 'spaceBefore' => 0]);

        // Repeat for other rows
        $detailTable->addRow(null, ['exactHeight' => true, 'height' => 200]);
        $detailTable->addCell(2000)->addText("KEGIATAN", ['bold' => true], ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(500)->addText(":", null, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(15000)->addText($subKegiatan->kegiatan->kode . ' - ' . $subKegiatan->kegiatan->nama, null, ['spaceAfter' => 0, 'spaceBefore' => 0]);

        // Add more rows with the same style to control spacing
        $detailTable->addRow(null, ['exactHeight' => true, 'height' => 200]);
        $detailTable->addCell(2000)->addText("SUB KEGIATAN", ['bold' => true], ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(500)->addText(":", null, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(15000)->addText($subKegiatan->kode . ' - ' . $subKegiatan->nama, null, ['spaceAfter' => 0, 'spaceBefore' => 0]);

        // PPTK row
        $detailTable->addRow(null, ['exactHeight' => true, 'height' => 200]);
        $detailTable->addCell(2000)->addText("PPTK / NIP", ['bold' => true], ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(500)->addText(":", null, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(15000)->addText($subKegiatan->pptk->nama . ' / ' . $subKegiatan->pptk->nip, null, ['spaceAfter' => 0, 'spaceBefore' => 0]);

        // BULAN row
        $detailTable->addRow(null, ['exactHeight' => true, 'height' => 200]);
        $detailTable->addCell(2000)->addText("BULAN", ['bold' => true], ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(500)->addText(":", null, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $detailTable->addCell(15000)->addText($this->bulanList[$this->selectedBulan] . ' ' . date('Y'), null, ['spaceAfter' => 0, 'spaceBefore' => 0]);


        // Tabel dengan border untuk rincian anggaran
        $tableStyle = [
            'borderSize' => 6, // Ukuran border
            'borderColor' => '000000', // Warna border hitam
            'cellMargin' => 50 // Margin sel agar lebih rapi
        ];

        $phpWord->addTableStyle('CustomTable', $tableStyle);
        $table = $section->addTable('CustomTable');

        // Header tabel
        $table->addRow();
        $table->addCell(2000)->addText("KODE REKENING", ['bold' => true], ['align' => 'center']);
        $table->addCell(6000)->addText("URAIAN", ['bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText("ANGGARAN", ['bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText("AKUMULASI PENCAIRAN", ['bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText("PENCAIRAN SAAT INI", ['bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText("RINCIAN SPJ", ['bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText("SISA ANGGARAN", ['bold' => true], ['align' => 'center']);

        // Inisialisasi variabel total
        $totalAnggaran = 0;
        $totalLama = 0;
        $totalBaru = 0;
        $totalSisaAnggaran = 0;

        // Isi tabel dengan data dari $subKegiatan
        foreach ($subKegiatan->rkas as $rka) {
            $table->addRow();
            // Menampilkan data RKA utama
            $table->addCell(2000)->addText($rka->kode_belanja, ['bold' => true], ['align' => 'center']);
            $table->addCell(6000)->addText($rka->nama_belanja, ['bold' => true]);
            $table->addCell(2000)->addText(number_format($rka->anggaran), ['bold' => true], ['align' => 'right']);
            $table->addCell(2000)->addText(number_format($rka->lama), ['bold' => true], ['align' => 'right']);
            $table->addCell(2000)->addText(number_format($rka->baru), ['bold' => true], ['align' => 'right']);
            $table->addCell(2000)->addText(" ");
            $table->addCell(2000)->addText(number_format($rka->anggaran - $rka->lama - $rka->baru), ['bold' => true], ['align' => 'right']);

            // Menambahkan nilai ke variabel total
            $totalAnggaran += $rka->anggaran;
            $totalLama += $rka->lama;
            $totalBaru += $rka->baru;
            $totalSisaAnggaran += ($rka->anggaran - $rka->lama - $rka->baru);

            // Iterasi untuk setiap belanja di dalam RKA
            foreach ($rka->belanjas as $belanja) {
                $table->addRow();
                // Baris kosong untuk mengindikasikan sub-item dari uraian utama
                $table->addCell(2000)->addText(""); // Empty cell
                $table->addCell(10000, ['gridSpan' => 4])->addText('  -  ' . $belanja->uraian); // Indentasi untuk sub-item
                $table->addCell(2000)->addText(number_format($belanja->nilai), ['bold' => false], ['align' => 'right']);
                $table->addCell(2000)->addText("");
            }
        }

        // Tambahkan footer dengan total
        $table->addRow();
        $table->addCell(2000)->addText("");
        $table->addCell(5000)->addText("Jumlah yang diminta", ['bold' => true], ['align' => 'center']);
        $table->addCell(2000)->addText(number_format($totalAnggaran), ['bold' => true], ['align' => 'right']);
        $table->addCell(3000)->addText(number_format($totalLama), ['bold' => true], ['align' => 'right']);
        $table->addCell(2000)->addText(number_format($totalBaru), ['bold' => true], ['align' => 'right']);
        $table->addCell(2000)->addText("", ['bold' => true], ['align' => 'right']);
        $table->addCell(2000)->addText(number_format($totalSisaAnggaran), ['bold' => true], ['align' => 'right']);

        // Simpan file Word
        $fileName = 'Nota_Pencairan_Dana_' . \Carbon\Carbon::now()->format('Y_m_d_His') . '.docx';
        $path = storage_path($fileName);

        // Save as a Word document
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($path);

        // Download file
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.laporan.laporan-n-p-d');
    }
}
