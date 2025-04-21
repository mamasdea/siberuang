<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Buku Pajak Ls')]
class BukuPajakLs extends Component
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
                p.id AS pajak_ls_id,
                b.tanggal AS tgl_bukti,
                CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                CONCAT('Penerimaan PFK - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL(LEFT(p.no_billing, 15), ''), ')') AS uraian,
                p.nominal AS pemotongan,
                NULL AS penyetoran
            FROM pajak_ls p
            JOIN belanja_ls b ON p.belanja_ls_id = b.id
            WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ?
        ),
        Pajak_lsetor AS (
            SELECT
                p.id AS pajak_ls_id,
                b.tanggal AS tgl_bukti,
                CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                CONCAT('Pengeluaran PFK - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(RIGHT(p.no_billing, 15), ''), ')' ) AS uraian,
                NULL AS pemotongan,
                p.nominal AS penyetoran
            FROM pajak_ls p
            JOIN belanja_ls b ON p.belanja_ls_id = b.id
            WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ?
        )
        SELECT * FROM (
            SELECT * FROM PajakUtama
            UNION ALL
            SELECT * FROM Pajak_lsetor
        ) AS laporan
        ORDER BY tgl_bukti, no_bukti;
    ", [$bulan, $tahun, $bulan, $tahun]);
        } else {
            $this->laporan = DB::select("
        WITH PajakUtama AS (
            SELECT
                p.id AS pajak_ls_id,
                b.tanggal AS tgl_bukti,
                CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                CONCAT('Penerimaan PFK - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL(LEFT(p.no_billing, 15), ''), ')') AS uraian,
                p.nominal AS pemotongan,
                NULL AS penyetoran
            FROM pajak_ls p
            JOIN belanja_ls b ON p.belanja_ls_id = b.id
            WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ? AND p.jenis_pajak = ?
        ),
        Pajak_lsetor AS (
            SELECT
                p.id AS pajak_ls_id,
                b.tanggal AS tgl_bukti,
                CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                CONCAT('Pengeluaran PFK - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(RIGHT(p.no_billing, 15), ''), ')' ) AS uraian,
                NULL AS pemotongan,
                p.nominal AS penyetoran
            FROM pajak_ls p
            JOIN belanja_ls b ON p.belanja_ls_id = b.id
            WHERE MONTH(b.tanggal) = ? AND YEAR(b.tanggal) = ? AND p.jenis_pajak = ?
        )
        SELECT * FROM (
            SELECT * FROM PajakUtama
            UNION ALL
            SELECT * FROM Pajak_lsetor
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
        $this->ppnTotal = DB::table('pajak_ls')
            ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id')
            ->where('pajak_ls.jenis_pajak', 'PPN')
            ->whereMonth('belanja_ls.tanggal', $bulan)
            ->whereYear('belanja_ls.tanggal', $tahun)
            ->sum('pajak_ls.nominal');

        // Hitung total PPh 21
        $this->pph21Total = DB::table('pajak_ls')
            ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id')
            ->where('pajak_ls.jenis_pajak', 'PPh 21')
            ->whereMonth('belanja_ls.tanggal', $bulan)
            ->whereYear('belanja_ls.tanggal', $tahun)
            ->sum('pajak_ls.nominal');

        // Hitung total PPh 22
        $this->pph22Total = DB::table('pajak_ls')
            ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id')
            ->where('pajak_ls.jenis_pajak', 'PPh 22')
            ->whereMonth('belanja_ls.tanggal', $bulan)
            ->whereYear('belanja_ls.tanggal', $tahun)
            ->sum('pajak_ls.nominal');

        // Hitung total PPh 23
        $this->pph23Total = DB::table('pajak_ls')
            ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id')
            ->where('pajak_ls.jenis_pajak', 'PPh 23')
            ->whereMonth('belanja_ls.tanggal', $bulan)
            ->whereYear('belanja_ls.tanggal', $tahun)
            ->sum('pajak_ls.nominal');
    }

    // Fungsi untuk menghitung total pajak bulan ini sesuai jenis pajak yang dipilih
    private function hitungPajakBulanIni($tahun, $bulan)
    {
        $data = DB::table('pajak_ls')
            ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id');
        if ($this->jenis != 'ALL') {
            $data->where('pajak_ls.jenis_pajak', $this->jenis);
        }

        $data->whereMonth('belanja_ls.tanggal', $bulan)
            ->whereYear('belanja_ls.tanggal', $tahun);
        $this->pajakBulanIni = $data->sum('pajak_ls.nominal');
    }

    // Fungsi untuk menghitung total pajak bulan lalu sesuai jenis pajak yang dipilih
    private function hitungPajakBulanLalu($tahun, $bulan)
    {
        if ($bulan === 1) {
            // Jika bulan yang dipilih adalah Januari, ambil data dari bulan Desember tahun sebelumnya
            $data = DB::table('pajak_ls')
                ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id');
            if ($this->jenis != 'ALL') {
                $data->where('pajak_ls.jenis_pajak', $this->jenis);
            }

            $data->whereMonth('belanja_ls.tanggal', 12)
                ->whereYear('belanja_ls.tanggal', $tahun - 1);
            $this->pajakBulanLalu = $data->sum('pajak_ls.nominal');
        } else {
            // Jika bulan yang dipilih > Januari, ambil data dari bulan Januari sampai bulan sebelum bulan yang dipilih
            $data = DB::table('pajak_ls')
                ->join('belanja_ls', 'pajak_ls.belanja_ls_id', '=', 'belanja_ls.id');
            if ($this->jenis != 'ALL') {
                $data->where('pajak_ls.jenis_pajak', $this->jenis);
            }

            $data->whereMonth('belanja_ls.tanggal', '<', $bulan)
                ->whereYear('belanja_ls.tanggal', $tahun);
            $this->pajakBulanLalu = $data->sum('pajak_ls.nominal');
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
        $jmlpajak_lsampaibulanini = $this->pajakBulanIni + $this->pajakBulanLalu;

        return view('livewire.laporan.buku-pajak-ls', [
            'laporan'       => $this->laporan,
            'ppnTotal'      => $this->ppnTotal,
            'pph21Total'    => $this->pph21Total,
            'pph22Total'    => $this->pph22Total,
            'pph23Total'    => $this->pph23Total,
            'pajakBulanIni' => $this->pajakBulanIni,
            'pajakBulanLalu' => $this->pajakBulanLalu,
            'totalpajak' => $jmlpajak_lsampaibulanini
        ]);
    }
}
