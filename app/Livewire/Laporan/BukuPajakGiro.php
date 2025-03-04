<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Buku Pajak Giro')]
class BukuPajakGiro extends Component
{
    public $bulan = '';
    public $laporan = [];
    public $ppnTotal = 0;
    public $pph21Total = 0;
    public $pph22Total = 0;
    public $pph23Total = 0;
    public $pajakBulanIni = 0; // Jumlah total dari PPN, PPh 21, PPh 22, dan PPh 23
    public $pajakBulanLalu = 0; // Jumlah pajak bulan lalu (PPN+PPh21,22,23)

    // Ketika properti 'bulan' diperbarui, ambil data laporan
    public function updatedBulan()
    {
        if ($this->bulan) {
            $this->ambilLaporan();
        }
    }

    // Fungsi untuk mengambil data laporan berdasarkan bulan dan tahun saat ini
    public function ambilLaporan()
    {
        if (!$this->bulan) {
            $this->laporan = [];
            $this->ppnTotal = 0;
            $this->pph21Total = 0;
            $this->pph22Total = 0;
            $this->pph23Total = 0;
            $this->pajakBulanIni = 0;
            $this->pajakBulanLalu = 0;
            return;
        }

        $tahun = date('Y'); // Ambil tahun dari sesi atau default ke tahun ini

        $this->laporan = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK - ', p.jenis_pajak, '(' , p.no_billing , ')' ) AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM pajaks p
                JOIN belanjas b ON p.belanja_id = b.id
                WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ?
            ),
            PajakSetor AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Pengeluaran PFK - ', p.jenis_pajak, '(' , p.no_billing , ')' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM pajaks p
                JOIN belanjas b ON p.belanja_id = b.id
                WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ?
            )
            SELECT * FROM (
                SELECT * FROM PajakUtama
                UNION ALL
                SELECT * FROM PajakSetor
            ) AS laporan
            ORDER BY tgl_bukti, no_bukti;
        ", [$this->bulan, $tahun, $this->bulan, $tahun]);

        // Hitung total PPN
        $this->ppnTotal = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPN')
            ->whereMonth('belanjas.tanggal', $this->bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Hitung total PPh 21
        $this->pph21Total = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPh 21')
            ->whereMonth('belanjas.tanggal', $this->bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Hitung total PPh 22
        $this->pph22Total = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPh 22')
            ->whereMonth('belanjas.tanggal', $this->bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Hitung total PPh 23
        $this->pph23Total = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPh 23')
            ->whereMonth('belanjas.tanggal', $this->bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Jumlahkan Pajak Bulan Ini
        $this->pajakBulanIni = $this->ppnTotal + $this->pph21Total + $this->pph22Total + $this->pph23Total;

        // Hitung Jumlah Pajak Bulan Lalu
        // Jika bulan yang dipilih adalah Januari, bulan lalu adalah Desember tahun sebelumnya
        $selectedMonth = (int)$this->bulan;
        if ($selectedMonth === 1) {
            // Jika bulan yang dipilih adalah Januari, ambil data dari bulan Desember tahun sebelumnya
            $this->pajakBulanLalu = DB::table('pajaks')
                ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
                ->whereIn('pajaks.jenis_pajak', ['PPN', 'PPh 21', 'PPh 22', 'PPh 23'])
                ->whereMonth('belanjas.tanggal', 12)
                ->whereYear('belanjas.tanggal', $tahun - 1)
                ->sum('pajaks.nominal');
        } else {
            // Jika bulan yang dipilih > Januari, ambil data dari bulan Januari sampai bulan sebelum bulan yang dipilih
            $this->pajakBulanLalu = DB::table('pajaks')
                ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
                ->whereIn('pajaks.jenis_pajak', ['PPN', 'PPh 21', 'PPh 22', 'PPh 23'])
                ->whereMonth('belanjas.tanggal', '<', $selectedMonth)
                ->whereYear('belanjas.tanggal', $tahun)
                ->sum('pajaks.nominal');
        }
    }

    public function render()
    {
        $jmlpajaksampaibulanini = $this->pajakBulanIni + $this->pajakBulanLalu;

        return view('livewire.laporan.buku-pajak-giro', [
            'laporan'       => $this->laporan,
            'ppnTotal'      => $this->ppnTotal,
            'pph21Total'    => $this->pph21Total,
            'pph22Total'    => $this->pph22Total,
            'pph23Total'    => $this->pph23Total,
            'pajakBulanIni' => $this->pajakBulanIni,
            'pajakBulanLalu' => $this->pajakBulanLalu,
            'totalpajak' => $jmlpajaksampaibulanini
        ]);
    }
}
