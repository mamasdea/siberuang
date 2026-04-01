<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('menu_key');
            $table->timestamps();
            $table->unique(['role', 'menu_key']);
        });

        // Seed default permissions
        $adminMenus = [
            'anggaran', 'uang-persediaan', 'belanja', 'spj',
            'spp-spm-up', 'spp-spm-gu', 'spp-spm-ls',
            'kontrak', 'laporan', 'master',
        ];

        $userMenus = [
            'belanja', 'spp-spm-ls', 'kontrak', 'laporan',
        ];

        $now = now();

        foreach ($adminMenus as $menu) {
            DB::table('menu_permissions')->insert([
                'role' => 'admin',
                'menu_key' => $menu,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($userMenus as $menu) {
            DB::table('menu_permissions')->insert([
                'role' => 'user',
                'menu_key' => $menu,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permissions');
    }
};
