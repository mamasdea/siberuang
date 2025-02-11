<?php

use App\Livewire\Dashboard;
use Illuminate\Http\Request;
use App\Livewire\TabAnggaran;
use App\Livewire\Belanja\Pajak;
use App\Livewire\Belanja\PajakLs;
use App\Livewire\ProgramHierarchy;
use App\Livewire\Belanja\BelanjaLs;
use App\Livewire\Belanja\Penerimaan;
use App\Livewire\Laporan\LaporanNPD;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Livewire\Laporan\LaporanPage;
use Illuminate\Support\Facades\Route;
use App\Livewire\SubKegiatanHierarchy;
use App\Livewire\Master\UserManagement;
use App\Livewire\Belanja\BelanjaManager;
use App\Livewire\Laporan\LaporanBelanja;
use App\Livewire\Laporan\LaporanBkuGiro;
use App\Livewire\Master\RekeningBelanja;
use App\Http\Controllers\HelperController;
use App\Livewire\Master\PengelolaKeuangan;
use App\Livewire\Penerima\PenerimaRekanan;
use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Anggaran\ProgramKegiatanForm;

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
    return view('livewire.dashboard');
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
    Route::get('belanja', BelanjaManager::class);
    Route::get('belanja_ls', BelanjaLs::class);
    Route::get('hirarki', ProgramHierarchy::class);
    Route::get('sub_hirarki', SubKegiatanHierarchy::class);
    Route::get('laporan-page', LaporanPage::class)->name('laporan.page');
    Route::get('laporan-bku-giro', LaporanBkuGiro::class)->name('laporan.bku.giro');
    Route::get('laporan-npd', LaporanNPD::class)->name('laporan-npd');
    Route::get('penerimaan/{belanjaId}', Penerimaan::class)->name('penerimaan');
    Route::get('pajak/{belanjaId}', Pajak::class)->name('pajak');
    Route::get('/pajakls/{belanjaLsId}', PajakLs::class)->name('pajakls');
    Route::get('laporanbelanja/{laporanId}', LaporanBelanja::class)->name('laporanbelanja');
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
