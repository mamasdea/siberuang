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
use App\Livewire\Laporan\LaporanPage;
use App\Livewire\Persediaan\UangGiro;
use Illuminate\Support\Facades\Route;
use App\Livewire\Belanja\Ls\BelanjaLs;
use App\Livewire\SubKegiatanHierarchy;
use App\Livewire\Belanja\Gu\Penerimaan;
use App\Livewire\Master\UserManagement;
use App\Livewire\Laporan\LaporanBelanja;
use App\Livewire\Master\RekeningBelanja;
use App\Livewire\Laporan\BukuKasUmumGiro;
use App\Http\Controllers\HelperController;
use App\Livewire\Laporan\LaporanRealisasi;
use App\Livewire\Master\PengelolaKeuangan;
use App\Livewire\Penerima\PenerimaRekanan;
use App\Livewire\Belanja\Gu\BelanjaManager;
use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Anggaran\ProgramKegiatanForm;
use App\Livewire\Belanja\Kkpd\BelanjaKkpdsManager;
use App\Livewire\Belanja\Kkpd\PajakKkpd;
use App\Livewire\Belanja\Kkpd\PenerimaanKkpd;
use App\Livewire\Laporan\BukuKasUmumKkpd;
use App\Livewire\Laporan\LaporanBelanjaKkpd;
use App\Livewire\Persediaan\UangKkpd;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('template', function () {
    return File::get(public_path() . '/documentation.html');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('program', ProgramKegiatanForm::class);
    Route::get('pengelola-keuangan', PengelolaKeuangan::class);
    Route::get('tab-anggaran', TabAnggaran::class);
    Route::get('rekening-belanja', RekeningBelanja::class);
    Route::get('user-management', UserManagement::class)->name('user-management');
    Route::get('penerima', PenerimaRekanan::class);
});

// Middleware untuk user dan admin (wajib login)
Route::middleware(['auth'])->group(function () {
    Route::get('/set-tahun-anggaran', function (Request $request) {
        session(['tahun_anggaran' => $request->tahun]);
        return back();
    })->name('set-tahun-anggaran');

    Route::get('show-picture', [HelperController::class, 'showPicture'])->name('helper.show-picture');
    Route::get('dashboard', Dashboard::class);
    Route::get('up-giro', UangGiro::class);
    Route::get('up-kkpd', UangKkpd::class);
    Route::get('belanja', BelanjaManager::class);
    Route::get('belanja_ls', BelanjaLs::class);
    Route::get('belanja-kkpd', BelanjaKkpdsManager::class)->name('belanja_kkpd');
    Route::get('hirarki', ProgramHierarchy::class);
    Route::get('sub_hirarki', SubKegiatanHierarchy::class);
    Route::get('laporan-page', LaporanPage::class)->name('laporan.page');
    Route::get('laporan-bkugiro', BukuKasUmumGiro::class)->name('laporan.bkugiro');
    Route::get('laporan-bkukkpd', BukuKasUmumKkpd::class)->name('laporan.bkukkpd');
    Route::get('/laporan-realisasi', LaporanRealisasi::class)->name('laporan.realisasi');
    Route::get('penerimaan/{belanjaId}', Penerimaan::class)->name('penerimaan');
    Route::get('pajak/{belanjaId}', Pajak::class)->name('pajak');
    Route::get('penerimaan_kkpd/{belanjaId}', PenerimaanKkpd::class)->name('penerimaan_kkpd');
    Route::get('pajak_kkpd/{belanjaId}', PajakKkpd::class)->name('pajak_kkpd');
    Route::get('/pajakls/{belanjaLsId}', PajakLs::class)->name('pajakls');
    Route::get('laporanbelanja/{laporanId}', LaporanBelanja::class)->name('laporanbelanja');

    Route::get('laporanbelanja-kkpd/{laporanId}', LaporanBelanjaKkpd::class)->name('laporanbelanja_kkpd');
});

// Halaman Login (GET)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');

// Proses Login (POST)
Route::post('/login', [LoginController::class, 'login']);

// Logout (POST)
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout')->middleware('auth');
