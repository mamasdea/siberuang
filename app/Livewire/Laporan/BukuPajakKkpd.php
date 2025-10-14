<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[Title('Buku Pajak KKPD')]
class BukuPajakKkpd extends Component
{
    public $tanggal_awal = '';
    public $tanggal_akhir = '';
    public $jenis = 'ALL';
    public $laporan = [];
    public $ppnTotal = 0;
    public $pph21Total = 0;
    public $pph22Total = 0;
    public $pph23Total = 0;
    public $pajakPeriodeIni = 0;
    public $saldoAwal = 0;
    public $saldoAwalPemotongan = 0;
    public $saldoAwalPenyetoran = 0;

    // Ketika properti 'tanggal_awal' diperbarui
    public function updatedTanggalAwal()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            $this->ambilLaporan();
        }
    }

    // Ketika properti 'tanggal_akhir' diperbarui
    public function updatedTanggalAkhir()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            $this->ambilLaporan();
        }
    }

    // Ketika properti 'jenis' diperbarui
    public function updatedJenis()
    {
        if ($this->tanggal_awal && $this->tanggal_akhir) {
            $this->ambilLaporan();
        }
    }

    // Fungsi untuk mengambil data laporan berdasarkan range tanggal
    public function ambilLaporan()
    {
        if (!$this->tanggal_awal || !$this->tanggal_akhir) {
            $this->resetData();
            return;
        }

        // Validasi tanggal
        if ($this->tanggal_awal > $this->tanggal_akhir) {
            $this->resetData();
            return;
        }

        // Ambil data laporan untuk range tanggal dan jenis pajak yang dipilih
        if ($this->jenis == 'ALL') {
            $this->laporan = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL(LEFT(p.no_billing, 15), ''), ')') AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ?
            ),
            PajakSetor AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Pengeluaran PFK - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(RIGHT(p.no_billing, 15), ''), ')' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ?
            )
            SELECT * FROM (
                SELECT * FROM PajakUtama
                UNION ALL
                SELECT * FROM PajakSetor
            ) AS laporan
            ORDER BY tgl_bukti, no_bukti;
        ", [$this->tanggal_awal, $this->tanggal_akhir, $this->tanggal_awal, $this->tanggal_akhir]);
        } else {
            $this->laporan = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Penerimaan PFK - ', p.jenis_pajak, ' - ID BILLING : ', ' (', IFNULL(LEFT(p.no_billing, 15), ''), ')') AS uraian,
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND p.jenis_pajak = ?
            ),
            PajakSetor AS (
                SELECT
                    p.id AS pajak_id,
                    b.tanggal AS tgl_bukti,
                    CONCAT('TBP - ', b.no_bukti) AS no_bukti,
                    CONCAT('Pengeluaran PFK - ', p.jenis_pajak, ' - NTPN : ' ,' (' , IFNULL(RIGHT(p.no_billing, 15), ''), ')' ) AS uraian,
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND p.jenis_pajak = ?
            )
            SELECT * FROM (
                SELECT * FROM PajakUtama
                UNION ALL
                SELECT * FROM PajakSetor
            ) AS laporan
            ORDER BY tgl_bukti, no_bukti;
        ", [$this->tanggal_awal, $this->tanggal_akhir, $this->jenis, $this->tanggal_awal, $this->tanggal_akhir, $this->jenis]);
        }

        // Hitung total untuk semua jenis pajak di periode ini
        $this->hitungTotalPajak();

        // Hitung total untuk jenis pajak saat ini di periode ini
        $this->hitungPajakPeriodeIni();

        // Hitung saldo awal (akumulasi dari awal tahun sampai sebelum tanggal awal)
        $this->hitungSaldoAwal();
    }

    // Fungsi untuk menghitung total semua jenis pajak di periode yang dipilih
    private function hitungTotalPajak()
    {
        // Hitung total PPN
        $this->ppnTotal = DB::table('pajak_kkpds')
            ->join('belanja_kkpds', 'pajak_kkpds.belanja_id', '=', 'belanja_kkpds.id')
            ->where('pajak_kkpds.jenis_pajak', 'PPN')
            ->whereBetween('belanja_kkpds.tanggal', [$this->tanggal_awal, $this->tanggal_akhir])
            ->sum('pajak_kkpds.nominal');

        // Hitung total PPh 21
        $this->pph21Total = DB::table('pajak_kkpds')
            ->join('belanja_kkpds', 'pajak_kkpds.belanja_id', '=', 'belanja_kkpds.id')
            ->where('pajak_kkpds.jenis_pajak', 'PPh 21')
            ->whereBetween('belanja_kkpds.tanggal', [$this->tanggal_awal, $this->tanggal_akhir])
            ->sum('pajak_kkpds.nominal');

        // Hitung total PPh 22
        $this->pph22Total = DB::table('pajak_kkpds')
            ->join('belanja_kkpds', 'pajak_kkpds.belanja_id', '=', 'belanja_kkpds.id')
            ->where('pajak_kkpds.jenis_pajak', 'PPh 22')
            ->whereBetween('belanja_kkpds.tanggal', [$this->tanggal_awal, $this->tanggal_akhir])
            ->sum('pajak_kkpds.nominal');

        // Hitung total PPh 23
        $this->pph23Total = DB::table('pajak_kkpds')
            ->join('belanja_kkpds', 'pajak_kkpds.belanja_id', '=', 'belanja_kkpds.id')
            ->where('pajak_kkpds.jenis_pajak', 'PPh 23')
            ->whereBetween('belanja_kkpds.tanggal', [$this->tanggal_awal, $this->tanggal_akhir])
            ->sum('pajak_kkpds.nominal');
    }

    // Fungsi untuk menghitung total pajak periode ini sesuai jenis pajak yang dipilih
    private function hitungPajakPeriodeIni()
    {
        $data = DB::table('pajak_kkpds')
            ->join('belanja_kkpds', 'pajak_kkpds.belanja_id', '=', 'belanja_kkpds.id');

        if ($this->jenis != 'ALL') {
            $data->where('pajak_kkpds.jenis_pajak', $this->jenis);
        }

        $data->whereBetween('belanja_kkpds.tanggal', [$this->tanggal_awal, $this->tanggal_akhir]);
        $this->pajakPeriodeIni = $data->sum('pajak_kkpds.nominal');
    }

    // Fungsi untuk menghitung saldo awal (akumulasi dari awal tahun sampai sebelum tanggal awal)
    private function hitungSaldoAwal()
    {
        $tahun = Carbon::parse($this->tanggal_awal)->year;
        $awalTahun = $tahun . '-01-01';
        $sebelumTanggalAwal = Carbon::parse($this->tanggal_awal)->subDay()->format('Y-m-d');

        // Reset nilai
        $this->saldoAwalPemotongan = 0;
        $this->saldoAwalPenyetoran = 0;

        // Cek apakah ada data sebelum tanggal awal
        if ($sebelumTanggalAwal < $awalTahun) {
            $this->saldoAwal = 0;
            return;
        }

        // Ambil data untuk saldo awal menggunakan query yang sama seperti laporan
        if ($this->jenis == 'ALL') {
            $dataSaldoAwal = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ?
            ),
            PajakSetor AS (
                SELECT
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ?
            )
            SELECT * FROM (
                SELECT * FROM PajakUtama
                UNION ALL
                SELECT * FROM PajakSetor
            ) AS saldo_awal;
        ", [$awalTahun, $sebelumTanggalAwal, $awalTahun, $sebelumTanggalAwal]);
        } else {
            $dataSaldoAwal = DB::select("
            WITH PajakUtama AS (
                SELECT
                    p.nominal AS pemotongan,
                    NULL AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND p.jenis_pajak = ?
            ),
            PajakSetor AS (
                SELECT
                    NULL AS pemotongan,
                    p.nominal AS penyetoran
                FROM pajak_kkpds p
                JOIN belanja_kkpds b ON p.belanja_id = b.id
                WHERE b.tanggal BETWEEN ? AND ? AND p.jenis_pajak = ?
            )
            SELECT * FROM (
                SELECT * FROM PajakUtama
                UNION ALL
                SELECT * FROM PajakSetor
            ) AS saldo_awal;
        ", [$awalTahun, $sebelumTanggalAwal, $this->jenis, $awalTahun, $sebelumTanggalAwal, $this->jenis]);
        }

        // Hitung total pemotongan dan penyetoran
        $this->saldoAwalPemotongan = collect($dataSaldoAwal)->sum('pemotongan');
        $this->saldoAwalPenyetoran = collect($dataSaldoAwal)->sum('penyetoran');

        // Saldo awal = Total Pemotongan - Total Penyetoran
        $this->saldoAwal = $this->saldoAwalPemotongan - $this->saldoAwalPenyetoran;
    }

    // Reset data ketika tidak ada tanggal yang dipilih
    private function resetData()
    {
        $this->laporan = [];
        $this->ppnTotal = 0;
        $this->pph21Total = 0;
        $this->pph22Total = 0;
        $this->pph23Total = 0;
        $this->pajakPeriodeIni = 0;
        $this->saldoAwal = 0;
        $this->saldoAwalPemotongan = 0;
        $this->saldoAwalPenyetoran = 0;
    }

    public function render()
    {
        // Hitung saldo akhir periode
        $totalPemotonganPeriode = collect($this->laporan)->sum('pemotongan');
        $totalPenyetoranPeriode = collect($this->laporan)->sum('penyetoran');
        $saldoAkhir = $this->saldoAwal + $totalPemotonganPeriode - $totalPenyetoranPeriode;

        return view('livewire.laporan.buku-pajak-kkpd', [
            'laporan'               => $this->laporan,
            'ppnTotal'              => $this->ppnTotal,
            'pph21Total'            => $this->pph21Total,
            'pph22Total'            => $this->pph22Total,
            'pph23Total'            => $this->pph23Total,
            'pajakPeriodeIni'       => $this->pajakPeriodeIni,
            'saldoAwal'             => $this->saldoAwal,
            'saldoAwalPemotongan'   => $this->saldoAwalPemotongan,
            'saldoAwalPenyetoran'   => $this->saldoAwalPenyetoran,
            'saldoAkhir'            => $saldoAkhir
        ]);
    }
}
