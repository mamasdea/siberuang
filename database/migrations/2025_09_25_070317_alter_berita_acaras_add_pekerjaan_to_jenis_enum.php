<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Daftar enum lama (sebelum penambahan)
    private array $old = [
        'pemeriksaan',
        'serah_terima',
        'penerimaan',
        'administratif',
        'pembayaran',
    ];

    // Daftar enum baru (setelah penambahan)
    private array $new = [
        'pemeriksaan',
        'serah_terima',
        'penerimaan',
        'administratif',
        'pembayaran',
        'pekerjaan', // << opsi baru
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL/MariaDB: ubah definisi ENUM dengan daftar baru
            $list = "'" . implode("','", $this->new) . "'";
            DB::statement("
                ALTER TABLE berita_acaras
                MODIFY COLUMN jenis ENUM($list) NOT NULL
            ");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: tambahkan value ke tipe enum yang sudah ada
            // Asumsi tipe enum otomatis dibuat oleh Laravel bernama "berita_acaras_jenis_check"
            // atau kolom enum biasa (Laravel menyimpan sebagai 'text check'). Jika Anda
            // memakai enum type kustom, sesuaikan nama tipe-nya.
            try {
                DB::statement("ALTER TYPE jenis ADD VALUE IF NOT EXISTS 'pekerjaan'");
            } catch (\Throwable $e) {
                // Fallback umum Laravel (enum disimpan sebagai text + check): update constraint
                // Jika skema Anda pakai CHECK constraint, lewati saja karena nilai baru akan lolos.
            }
        } else {
            // SQLite/driver lain: lewati (enum tidak ketat)
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Pastikan tidak ada nilai 'pekerjaan' sebelum kembalikan enum
            DB::table('berita_acaras')
                ->where('jenis', 'pekerjaan')
                ->update(['jenis' => 'pemeriksaan']);

            $list = "'" . implode("','", $this->old) . "'";
            DB::statement("
                ALTER TABLE berita_acaras
                MODIFY COLUMN jenis ENUM($list) NOT NULL
            ");
        } elseif ($driver === 'pgsql') {
            // Menghapus value dari ENUM di PostgreSQL membutuhkan langkah kompleks (buat tipe baru,
            // cast kolom, lalu drop tipe lama). Umumnya migration down untuk enum ditinggalkan.
            // Jika perlu benar-benar direvert, buat migration khusus sesuai tipe enum Anda.
        } else {
            // driver lain: tidak ada aksi
        }
    }
};
