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
    public $jenis = 'ALL';
    public $laporan = [];
    public $ppnTotal = 0;
    public $pph21Total = 0;
    public $pph22Total = 0;
    public $pph23Total = 0;
    public $pajakBulanIni = 0;
    public $pajakBulanLalu = 0;

    // Ketika properti 'bulan' diperbarui, ambil data laporan
    public function updatedBulan()
    {
        if ($this->bulan) {
            $this->ambilLaporan();
        }
    }

    // Ketika properti 'jenis' diperbarui, juga ambil data laporan
    public function updatedJenis()
    {
        if ($this->bulan) {
            $this->ambilLaporan();
        }
    }

    // Fungsi untuk mengambil data laporan berdasarkan bulan dan tahun saat ini
    public function ambilLaporan()
    {
        if (!$this->bulan) {
            $this->resetData();
            return;
        }

        $tahun = date('Y'); // Ambil tahun dari sesi atau default ke tahun ini
        $bulan = (int)$this->bulan;

        // Ambil data laporan untuk bulan dan jenis pajak yang dipilih
        if ($this->jenis == 'ALL') {
            $this->laporan = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL((p.no_billing), ''), ')') AS uraian,
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
                    CONCAT('Pengeluaran PFK - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL((p.ntpn), ''), ')' ,' - NTB : ' ,' (' , IFNULL((p.ntb), ''), ')' ) AS uraian,
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
        ", [$bulan, $tahun, $bulan, $tahun]);
        } else {
            $this->laporan = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL((p.no_billing), ''), ')') AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM pajaks p
                JOIN belanjas b ON p.belanja_id = b.id
                WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ? AND p.jenis_pajak = ?
            ),
            PajakSetor AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                     CONCAT('Pengeluaran PFK - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL((p.ntpn), ''), ')' ,' - NTB : ' ,' (' , IFNULL((p.ntb), ''), ')' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM pajaks p
                JOIN belanjas b ON p.belanja_id = b.id
                WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ? AND p.jenis_pajak = ?
            )
            SELECT * FROM (
                SELECT * FROM PajakUtama
                UNION ALL
                SELECT * FROM PajakSetor
            ) AS laporan
            ORDER BY tgl_bukti, no_bukti;
        ", [$bulan, $tahun, $this->jenis, $bulan, $tahun, $this->jenis]);
        }
        // Hitung total untuk semua jenis pajak
        $this->hitungTotalPajak($tahun, $bulan);

        // Hitung total untuk jenis pajak saat ini di bulan ini
        $this->hitungPajakBulanIni($tahun, $bulan);

        // Hitung total untuk jenis pajak saat ini di bulan-bulan sebelumnya
        $this->hitungPajakBulanLalu($tahun, $bulan);
    }

    // Fungsi untuk menghitung total semua jenis pajak
    private function hitungTotalPajak($tahun, $bulan)
    {
        // Hitung total PPN
        $this->ppnTotal = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPN')
            ->whereMonth('belanjas.tanggal', $bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Hitung total PPh 21
        $this->pph21Total = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPh 21')
            ->whereMonth('belanjas.tanggal', $bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Hitung total PPh 22
        $this->pph22Total = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPh 22')
            ->whereMonth('belanjas.tanggal', $bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');

        // Hitung total PPh 23
        $this->pph23Total = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id')
            ->where('pajaks.jenis_pajak', 'PPh 23')
            ->whereMonth('belanjas.tanggal', $bulan)
            ->whereYear('belanjas.tanggal', $tahun)
            ->sum('pajaks.nominal');
    }

    // Fungsi untuk menghitung total pajak bulan ini sesuai jenis pajak yang dipilih
    private function hitungPajakBulanIni($tahun, $bulan)
    {
        $data = DB::table('pajaks')
            ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id');
        if ($this->jenis != 'ALL') {
            $data->where('pajaks.jenis_pajak', $this->jenis);
        }

        $data->whereMonth('belanjas.tanggal', $bulan)
            ->whereYear('belanjas.tanggal', $tahun);
        $this->pajakBulanIni = $data->sum('pajaks.nominal');
    }

    // Fungsi untuk menghitung total pajak bulan lalu sesuai jenis pajak yang dipilih
    private function hitungPajakBulanLalu($tahun, $bulan)
    {
        if ($bulan === 1) {
            // Jika bulan yang dipilih adalah Januari, ambil data dari bulan Desember tahun sebelumnya
            $data = DB::table('pajaks')
                ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id');
            if ($this->jenis != 'ALL') {
                $data->where('pajaks.jenis_pajak', $this->jenis);
            }

            $data->whereMonth('belanjas.tanggal', 12)
                ->whereYear('belanjas.tanggal', $tahun - 1);
            $this->pajakBulanLalu = $data->sum('pajaks.nominal');
        } else {
            // Jika bulan yang dipilih > Januari, ambil data dari bulan Januari sampai bulan sebelum bulan yang dipilih
            $data = DB::table('pajaks')
                ->join('belanjas', 'pajaks.belanja_id', '=', 'belanjas.id');
            if ($this->jenis != 'ALL') {
                $data->where('pajaks.jenis_pajak', $this->jenis);
            }

            $data->whereMonth('belanjas.tanggal', '<', $bulan)
                ->whereYear('belanjas.tanggal', $tahun);
            $this->pajakBulanLalu = $data->sum('pajaks.nominal');
        }
    }

    // Reset data ketika tidak ada bulan yang dipilih
    private function resetData()
    {
        $this->laporan = [];
        $this->ppnTotal = 0;
        $this->pph21Total = 0;
        $this->pph22Total = 0;
        $this->pph23Total = 0;
        $this->pajakBulanIni = 0;
        $this->pajakBulanLalu = 0;
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
