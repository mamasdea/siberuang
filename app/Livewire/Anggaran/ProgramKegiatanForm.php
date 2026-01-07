<?php

namespace App\Livewire\Anggaran;

use App\Models\Program;
use Livewire\Component;
use App\Imports\RkaImport;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Imports\ProgramImport;
use Livewire\Attributes\Title;
use App\Imports\KegiatanImport;
use App\Imports\SubKegiatanImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MultipleImportProgram;

#[Title('Anggaran')]
class ProgramKegiatanForm extends Component
{
    use WithFileUploads;

    public $file;
    public $kode, $nama;
    public $programId;
    public $kegiatanId;
    public $subKegiatanId;
    public $isEditMode = false;

    // Properties for import with preview
    public $showPreview = false;
    public $fileDetected = false;
    public $formatInfo = [];
    public $previewData = [];
    public $uploadedFilePath = null;
    public $fileName = null;
    public $fileSize = null;
    public $tahun_anggaran_import;

    protected $listeners = ['resetInput'];

    protected $rules = [
        'kode' => 'required|unique:programs,kode',
        'nama' => 'required|string|max:255',
    ];
    public function resetInput()
    {
        $this->kode = '';
        $this->nama = '';
        $this->programId = null;
        $this->isEditMode = false;
    }
    public function resetAndCloseModal()
    {
        $this->resetInput();
        $this->js(<<<'JS'
            $('#programModal').modal('hide');
        JS);
    }
    public function next($id)
    {
        $this->programId = $id;

        // Dispatch browser event to switch tab
        $this->dispatch('switch-tab', tab: 'sasaran');

        // Also use JS for backup
        $this->js(<<<'JS'
        setTimeout(function() {
            console.log('Attempting to switch tab to Kegiatan');
            if (typeof switchToTab === 'function') {
                switchToTab('sasaran');
            } else {
                $('#kegiatan').trigger("click");
            }
        }, 100);
    JS);
    }

    #[On('sub-kegiatan')]
    public function subKegiatan($id)
    {
        $this->kegiatanId = $id;
        $this->js(<<<'JS'
        setTimeout(function() {
            $('#subkegiatantab').trigger("click");
        }, 100);
    JS);
    }
    #[On('r-k-a')]
    public function rka($id)
    {
        $this->subKegiatanId = $id;
        $this->js(<<<'JS'
        setTimeout(function() {
            $('#Rkatab').trigger("click");
        }, 100);
    JS);
    }
    public function store()
    {
        $this->validate();

        Program::create([
            'kode' => $this->kode,
            'nama' => $this->nama,
        ]);

        $this->js(<<<'JS'
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: "Data berhasil disimpan"
        });
        JS);
        $this->resetInput();
        $this->js(<<<'JS'
                $('#programModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $program = Program::find($id);
        $this->kode = $program->kode;
        $this->nama = $program->nama;
        $this->programId = $program->id;

        $this->isEditMode = true;
        // Trigger event to open the modal
        $this->js(<<<'JS'
            $('#programModal').modal('show');
        JS);
    }

    public function update()
    {
        $this->validate([
            'kode' => 'required|unique:programs,kode,' . $this->programId,
            'nama' => 'required|string|max:255',
        ]);

        $program = Program::findOrFail($this->programId);
        $program->update([
            'kode' => $this->kode,
            'nama' => $this->nama,
        ]);

        $this->js(<<<'JS'
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: "Data berhasil diupdate"
        });
        JS);
        $this->resetInput();
        $this->js(<<<'JS'
        $('#programModal').modal('hide');
    JS);
    }

    public function delete_confirmation($id)
    {

        $this->programId = $id;
        $this->js(<<<'JS'
        Swal.fire({
            title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus data ini? proses ini tidak dapat dikembalikan.",
                 icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete()
                }
                });
        JS);
    }
    public function delete()
    {
        Program::destroy($this->programId);
        $this->js(<<<'JS'
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "error",
                title: "Data berhasil dihapus"
            });
            JS);
    }
    /**
     * Upload file and detect format
     */
    public function uploadAndDetect()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls',
            'tahun_anggaran_import' => 'required|numeric|min:2020|max:2100',
        ]);

        // Store file temporarily
        $this->uploadedFilePath = $this->file->store('temp');
        $fullPath = storage_path('app/' . $this->uploadedFilePath);

        // Detect format
        $converterService = new \App\Services\ExcelConverterService();
        $this->formatInfo = $converterService->detectFormat($fullPath);
        $this->fileDetected = true;

        // If needs conversion, get preview data
        if ($this->formatInfo['needs_conversion']) {
            $convertedData = $converterService->convertAndPreview($fullPath);
            $this->previewData = $convertedData['summary'];
            $this->showPreview = true;
        } else if ($this->formatInfo['format'] == 'template') {
            $this->showPreview = false;
            // File sudah dalam format yang benar, bisa langsung import
        }
    }

    /**
     * Download converted file
     */
    public function downloadConverted()
    {
        if (!$this->uploadedFilePath) {
            return;
        }

        $fullPath = storage_path('app/' . $this->uploadedFilePath);
        $converterService = new \App\Services\ExcelConverterService();

        // Convert and create file
        $convertedData = $converterService->convertAndPreview($fullPath);
        $outputPath = storage_path('app/temp/converted_' . time() . '.xlsx');

        $converterService->createExcelFile($convertedData, $outputPath);

        // Return download response
        return response()->download($outputPath, 'anggaran_converted.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Import converted file directly
     */
    public function importConverted()
    {
        if (!$this->uploadedFilePath) {
            return;
        }

        $fullPath = storage_path('app/' . $this->uploadedFilePath);
        $converterService = new \App\Services\ExcelConverterService();

        // If file needs conversion, convert it first
        if ($this->formatInfo['needs_conversion']) {
            $convertedData = $converterService->convertAndPreview($fullPath);
            $tempPath = storage_path('app/temp/temp_converted_' . time() . '.xlsx');
            $converterService->createExcelFile($convertedData, $tempPath);

            // Import the converted file
            Excel::import(new MultipleImportProgram($this->tahun_anggaran_import), $tempPath);

            // Clean up temp file
            @unlink($tempPath);
        } else {
            // Import directly
            Excel::import(new MultipleImportProgram($this->tahun_anggaran_import), $fullPath);
        }

        // Reset state
        $this->resetImportState();

        // Hide modal and show success notification
        $this->js(<<<'JS'
            $('#importModalProgram').modal('hide');
        JS);

        $this->js(<<<'JS'
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: "Data berhasil diimport"
        });
        JS);
    }

    /**
     * Reset import state
     */
    public function resetImportState()
    {
        $this->showPreview = false;
        $this->fileDetected = false;
        $this->formatInfo = [];
        $this->previewData = [];
        $this->file = null;
        $this->fileName = null;
        $this->fileSize = null;
        $this->tahun_anggaran_import = null;

        // Clean up uploaded file
        if ($this->uploadedFilePath) {
            @unlink(storage_path('app/' . $this->uploadedFilePath));
            $this->uploadedFilePath = null;
        }
    }

    /**
     * Handle file update
     */
    public function updatedFile()
    {
        if ($this->file) {
            $this->fileName = $this->file->getClientOriginalName();
            $this->fileSize = round($this->file->getSize() / 1024 / 1024, 2); // Convert to MB
        }
    }

    /**
     * Remove selected file
     */
    public function removeFile()
    {
        $this->file = null;
        $this->fileName = null;
        $this->fileSize = null;
    }

    public function render()
    {
        $tahun = session('tahun_anggaran', date('Y')); // Ambil tahun anggaran dari session

        $results = DB::table('programs as a')
            ->leftJoin('kegiatans as b', 'a.id', '=', 'b.program_id')
            ->leftJoin('sub_kegiatans as c', 'c.kegiatan_id', '=', 'b.id')
            ->leftJoin('rkas as d', 'd.sub_kegiatan_id', '=', 'c.id')
            ->select('a.id', 'a.kode', 'a.nama', DB::raw('SUM(d.anggaran) as total'))
            ->where('a.tahun_anggaran', $tahun) // Filter berdasarkan tahun anggaran
            ->groupBy('a.id', 'a.kode', 'a.nama')
            ->get();

        // Statistics
        $totalPrograms = DB::table('programs')->where('tahun_anggaran', $tahun)->count();
        $totalKegiatans = DB::table('kegiatans')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->where('programs.tahun_anggaran', $tahun)
            ->count();
        $totalSubKegiatans = DB::table('sub_kegiatans')
            ->join('kegiatans', 'kegiatans.id', '=', 'sub_kegiatans.kegiatan_id')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->where('programs.tahun_anggaran', $tahun)
            ->count();
        $totalAnggaran = DB::table('rkas')
            ->join('sub_kegiatans', 'sub_kegiatans.id', '=', 'rkas.sub_kegiatan_id')
            ->join('kegiatans', 'kegiatans.id', '=', 'sub_kegiatans.kegiatan_id')
            ->join('programs', 'programs.id', '=', 'kegiatans.program_id')
            ->where('programs.tahun_anggaran', $tahun)
            ->sum('rkas.anggaran');

        return view('livewire.anggaran.program-kegiatan-form', [
            'programs' => $results,
            'tahun_anggaran' => $tahun,
            'tahun_list' => DB::table('programs')->select('tahun_anggaran')->distinct()->pluck('tahun_anggaran'),
            'totalPrograms' => $totalPrograms,
            'totalKegiatans' => $totalKegiatans,
            'totalSubKegiatans' => $totalSubKegiatans,
            'totalAnggaran' => $totalAnggaran,
        ]);
    }



    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls', // Pastikan hanya file Excel yang dapat diunggah
            'tahun_anggaran_import' => 'required|numeric|min:2020|max:2100',
        ]);

        // Mengimpor semua sheet dari file Excel menggunakan MultiSheetImport
        Excel::import(new MultipleImportProgram($this->tahun_anggaran_import), $this->file->store('temp'));

        // Menggunakan JavaScript untuk menyembunyikan modal dan menampilkan notifikasi
        $this->js(<<<'JS'
            $('#importModalProgram').modal('hide');
        JS);

        $this->js(<<<'JS'
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: "Data berhasil diupload"
        });
        JS);
    }
}
