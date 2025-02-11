<?php


if (!function_exists('get_no_bku')) {
    function get_code($arrayOrg)
    {
        // Mendapatkan nomor tertinggi yang sudah ada di database
        $nomorTerakhir = 0001; // Gantilah 'nomor' dengan nama kolom yang sesuai

        // Mengambil nomor berikutnya
        $nomor = $nomorTerakhir ? $nomorTerakhir + 1 : 1;

        // Format nomor dengan 5 digit
        $nomor = str_pad($nomor, 5, '0', STR_PAD_LEFT);

        // Menghasilkan kode akhir
        return $nomor;
    }
}
