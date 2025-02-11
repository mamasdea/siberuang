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
        $this->js(<<<'JS'
        setTimeout(function() {
            $('#kegiatan').trigger("click");
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

        return view('livewire.anggaran.program-kegiatan-form', [
            'programs' => $results,
            'tahun_anggaran' => $tahun,
            'tahun_list' => DB::table('programs')->select('tahun_anggaran')->distinct()->pluck('tahun_anggaran'),
        ]);
    }



    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls', // Pastikan hanya file Excel yang dapat diunggah
        ]);

        // Mengimpor semua sheet dari file Excel menggunakan MultiSheetImport
        Excel::import(new MultipleImportProgram, $this->file->store('temp'));

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
