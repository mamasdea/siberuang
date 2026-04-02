<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuPermission extends Model
{
    protected $fillable = ['role', 'menu_key'];

    /**
     * Cek apakah role punya akses ke menu tertentu.
     * Hasil di-cache 60 detik agar tidak query terus.
     */
    public static function hasAccess(string $role, string $menuKey): bool
    {
        $permissions = Cache::remember("menu_permissions_{$role}", 60, function () use ($role) {
            return self::where('role', $role)->pluck('menu_key')->toArray();
        });

        return in_array($menuKey, $permissions);
    }

    /**
     * Clear cache saat permissions berubah.
     */
    public static function clearCache(): void
    {
        Cache::forget('menu_permissions_admin');
        Cache::forget('menu_permissions_user');
    }

    /**
     * Daftar semua menu yang bisa dikelola.
     */
    public static function allMenus(): array
    {
        return [
            'anggaran' => 'Anggaran',
            'uang-persediaan' => 'Uang Persediaan',
            'belanja' => 'Belanja (GU & KKPD)',
            'spj' => 'SPJ GU',
            'spp-spm-up' => 'SPP-SPM UP',
            'spp-spm-gu' => 'SPP-SPM GU',
            'spp-spm-tu' => 'SPP-SPM TU',
            'belanja-tu' => 'Belanja TU',
            'spj-tu' => 'SPJ TU',
            'spp-spm-ls' => 'SPP-SPM LS',
            'kontrak' => 'Kontrak',
            'laporan' => 'Laporan',
            'master' => 'Master',
        ];
    }
}
