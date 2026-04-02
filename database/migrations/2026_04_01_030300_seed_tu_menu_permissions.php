<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $menus = ['belanja-tu', 'spj-tu', 'spp-spm-tu'];

        foreach ($menus as $menu) {
            DB::table('menu_permissions')->insert([
                'role' => 'admin',
                'menu_key' => $menu,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menu_permissions')->whereIn('menu_key', ['belanja-tu', 'spj-tu', 'spp-spm-tu'])->delete();
    }
};
