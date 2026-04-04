<?php

use App\Livewire\Dashboard;
use App\Models\BelanjaKkpd;
use Illuminate\Http\Request;
use App\Livewire\TabAnggaran;
use App\Livewire\Belanja\Gu\Pajak;
use App\Livewire\ProgramHierarchy;
use App\Livewire\Belanja\Ls\PajakLs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Livewire\Laporan\BukuPajakLs;
use App\Livewire\Laporan\LaporanPage;
use App\Livewire\Persediaan\UangGiro;
use App\Livewire\Persediaan\UangKkpd;
use Illuminate\Support\Facades\Route;
use App\Livewire\Belanja\Ls\BelanjaLs;
use App\Livewire\SubKegiatanHierarchy;
use App\Livewire\Belanja\Gu\Penerimaan;
use App\Livewire\Kontrak\KontrakManage;
use App\Livewire\Laporan\BukuPajakGiro;
use App\Livewire\Laporan\BukuPajakKkpd;
use App\Livewire\Master\UserManagement;
use App\Livewire\Belanja\Kkpd\PajakKkpd;
use App\Livewire\Laporan\LaporanBelanja;
use App\Livewire\Master\RekeningBelanja;
use App\Livewire\Master\MenuAccessManager;
use App\Livewire\Kontrak\RealisasiManage;
use App\Livewire\Laporan\BukuKasUmumGiro;
use App\Livewire\Laporan\BukuKasUmumKkpd;
use App\Http\Controllers\HelperController;
use App\Livewire\Laporan\LaporanRealisasi;
use App\Livewire\Master\PengelolaKeuangan;
use App\Livewire\Penerima\PenerimaRekanan;
use App\Livewire\Belanja\Gu\BelanjaManager;
use App\Livewire\Belanja\Gu\SpjGuManager;
use App\Livewire\Belanja\Gu\SppSpmGuManager;
use App\Livewire\Belanja\Gu\SppSpmUpManager;
use App\Livewire\Laporan\LaporanBelanjaKkpd;
use App\Livewire\Belanja\Kkpd\PenerimaanKkpd;
use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Anggaran\ProgramKegiatanForm;
use App\Livewire\Belanja\Kkpd\BelanjaKkpdsManager;
use App\Http\Controllers\BeritaAcaraHtmlController;

Route::get('/', fn() => view('auth.login'));

Route::get('template', fn() => File::get(public_path() . '/documentation.html'));

// ==== BA HTML PRINT ====
Route::middleware(['auth'])->group(function () {
    Route::get(
        '/kontrak/{kontrak}/realisasi/{realisasi}/ba/{jenis}',
        [BeritaAcaraHtmlController::class, 'show']
    )
        ->whereNumber('kontrak')
        ->where('realisasi', '[0-9]+')
        ->where('jenis', 'pemeriksaan|serah_terima|pekerjaan|penerimaan|administratif|pembayaran')
        ->name('realisasi.ba.html');
});

// Logged-in routes (semua user)
Route::middleware(['auth'])->group(function () {
    Route::get('/set-tahun-anggaran', function (Request $request) {
        session(['tahun_anggaran' => $request->tahun]);
        return back();
    })->name('set-tahun-anggaran');

    Route::get('show-picture', [HelperController::class, 'showPicture'])->name('helper.show-picture');
    Route::get('dashboard', Dashboard::class);
    Route::get('hirarki', ProgramHierarchy::class);
    Route::get('sub_hirarki', SubKegiatanHierarchy::class);

    // -- Anggaran (menu:anggaran) --
    Route::middleware(['menu:anggaran'])->group(function () {
        Route::get('program', ProgramKegiatanForm::class);
        Route::get('tab-anggaran', TabAnggaran::class);
    });

    // -- Uang Persediaan (menu:uang-persediaan) --
    Route::middleware(['menu:uang-persediaan'])->group(function () {
        Route::get('up-giro', UangGiro::class);
        Route::get('up-kkpd', UangKkpd::class);
    });

    // -- Belanja (menu:belanja) --
    Route::middleware(['menu:belanja'])->group(function () {
        Route::get('belanja', BelanjaManager::class);
        Route::get('belanja-kkpd', BelanjaKkpdsManager::class)->name('belanja_kkpd');
        Route::get('penerimaan/{belanjaId}', Penerimaan::class)->name('penerimaan');
        Route::get('pajak/{belanjaId}', Pajak::class)->name('pajak');
        Route::get('penerimaan_kkpd/{belanjaId}', PenerimaanKkpd::class)->name('penerimaan_kkpd');
        Route::get('pajak_kkpd/{belanjaId}', PajakKkpd::class)->name('pajak_kkpd');
        Route::get('laporanbelanja/{laporanId}', LaporanBelanja::class)->name('laporanbelanja');
        Route::get('laporanbelanja-kkpd/{laporanId}', LaporanBelanjaKkpd::class)->name('laporanbelanja_kkpd');
    });

    // -- SPJ (menu:spj) --
    Route::middleware(['menu:spj'])->group(function () {
        Route::get('spj-gu', SpjGuManager::class)->name('spj-gu');
    });

    // -- SPP-SPM UP (menu:spp-spm-up) --
    Route::middleware(['menu:spp-spm-up'])->group(function () {
        Route::get('spp-spm-up', SppSpmUpManager::class)->name('spp-spm-up');
    });

    // -- SPP-SPM GU (menu:spp-spm-gu) --
    Route::middleware(['menu:spp-spm-gu'])->group(function () {
        Route::get('spp-spm-gu', SppSpmGuManager::class)->name('spp-spm-gu');
        Route::get('gu-nihil', \App\Livewire\Belanja\Gu\GuNihilList::class)->name('gu-nihil-list');
        Route::get('gu-nihil/{spjGuId}', \App\Livewire\Belanja\Gu\GuNihilManager::class)->name('gu-nihil');
    });

    // -- SPP-SPM TU (menu:spp-spm-tu) --
    Route::middleware(['menu:spp-spm-tu'])->group(function () {
        Route::get('spp-spm-tu', \App\Livewire\Belanja\Tu\SppSpmTuManager::class)->name('spp-spm-tu');
    });

    // -- Belanja TU (menu:belanja-tu) --
    Route::middleware(['menu:belanja-tu'])->group(function () {
        Route::get('belanja-tu', \App\Livewire\Belanja\Tu\BelanjaTuList::class)->name('belanja-tu-list');
        Route::get('belanja-tu/{sppSpmTuId}', \App\Livewire\Belanja\Tu\BelanjaTuManager::class)->name('belanja-tu');
        Route::get('penerimaan-tu/{belanjaTuId}', \App\Livewire\Belanja\Tu\PenerimaanTuPage::class)->name('penerimaan-tu');
        Route::get('pajak-tu/{belanjaTuId}', \App\Livewire\Belanja\Tu\PajakTuPage::class)->name('pajak-tu');
    });

    // -- SPJ TU (menu:spj-tu) --
    Route::middleware(['menu:spj-tu'])->group(function () {
        Route::get('spj-tu', \App\Livewire\Belanja\Tu\SpjTuList::class)->name('spj-tu-list');
        Route::get('spj-tu/{sppSpmTuId}', \App\Livewire\Belanja\Tu\SpjTuManager::class)->name('spj-tu');
    });

    // -- TU Nihil (menu:spp-spm-tu) --
    Route::middleware(['menu:spp-spm-tu'])->group(function () {
        Route::get('tu-nihil', \App\Livewire\Belanja\Tu\TuNihilList::class)->name('tu-nihil-list');
        Route::get('tu-nihil/{sppSpmTuId}', \App\Livewire\Belanja\Tu\SppSpmTuNihilManager::class)->name('tu-nihil');
    });

    // -- SPP-SPM LS (menu:spp-spm-ls) --
    Route::middleware(['menu:spp-spm-ls'])->group(function () {
        Route::get('spp-spm-ls', BelanjaLs::class);
        Route::get('/pajakls/{belanjaLsId}', PajakLs::class)->name('pajakls');
    });

    // -- Kontrak (menu:kontrak) --
    Route::middleware(['menu:kontrak'])->group(function () {
        Route::get('kontrak', KontrakManage::class)->name('kontrak');
        Route::get('/kontrak/{kontrak}/realisasi', RealisasiManage::class)->name('kontrak.realisasi');
    });

    // -- Laporan (menu:laporan) --
    Route::middleware(['menu:laporan'])->group(function () {
        Route::get('laporan-page', LaporanPage::class)->name('laporan.page');
        Route::get('laporan-bkugiro', BukuKasUmumGiro::class)->name('laporan.bkugiro');
        Route::get('laporan-bkukkpd', BukuKasUmumKkpd::class)->name('laporan.bkukkpd');
        Route::get('laporan-bkutu', \App\Livewire\Laporan\BukuKasUmumTu::class)->name('laporan.bkutu');
        Route::get('laporan-bkuall', \App\Livewire\Laporan\BukuKasUmumAll::class)->name('laporan.bkuall');
        Route::get('laporan-bukupajakgiro', BukuPajakGiro::class)->name('laporan.bukupajakgiro');
        Route::get('laporan-bukupajakkkpd', BukuPajakKkpd::class)->name('laporan.bukupajakkkpd');
        Route::get('laporan-bukupajakls', BukuPajakLs::class)->name('laporan.bukupajakls');
        Route::get('laporan-bukupajakall', \App\Livewire\Laporan\BukuPajakAll::class)->name('laporan.bukupajakall');
        Route::get('laporan-bukupajak', \App\Livewire\Laporan\BukuPajakManager::class)->name('laporan.bukupajak');
        Route::get('laporan-realisasi', LaporanRealisasi::class)->name('laporan.realisasi');
        Route::get('laporan-rincian-obyek', \App\Livewire\Laporan\LaporanRincianObyek::class)->name('laporan.rincian-obyek');
    });

    // -- Master (menu:master) --
    Route::middleware(['menu:master'])->group(function () {
        Route::get('rekening-belanja', RekeningBelanja::class);
        Route::get('penerima', PenerimaRekanan::class);
        Route::get('pengelola-keuangan', PengelolaKeuangan::class);
        Route::get('user-management', UserManagement::class)->name('user-management');
        Route::get('menu-access', MenuAccessManager::class)->name('menu-access');
    });
});

Route::middleware(['auth'])
    ->get('/realisasi/{kontrak}/{realisasi}/ba/semua', [BeritaAcaraHtmlController::class, 'printAll'])
    ->name('realisasi.ba.all');

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout')->middleware('auth');
