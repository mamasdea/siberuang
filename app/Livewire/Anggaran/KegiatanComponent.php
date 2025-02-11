<?php

namespace App\Livewire\Anggaran;

use App\Models\Program;
use Livewire\Component;
use App\Models\Kegiatan;
use Livewire\Attributes\Title;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\DB;

#[Title('Anggaran')]
class KegiatanComponent extends Component
{
    #[Reactive]
    public $program_id;
    public $kode, $nama;
    public $kegiatanId;
    public $isEditMode = false;

    protected $rules = [
        'kode' => 'required|unique:kegiatans,kode',
        'nama' => 'required|string|max:255',
    ];

    public function mount($programId)
    {

        $this->program_id = $programId;
    }

    public function render()
    {
        $namaProgram = Program::where('id', $this->program_id)->value('nama');
        $results = DB::table('kegiatans as b')
            ->leftJoin('sub_kegiatans as c', 'c.kegiatan_id', '=', 'b.id')
            ->leftJoin('rkas as d', 'd.sub_kegiatan_id', '=', 'c.id')
            ->select('b.id', 'b.kode', 'b.nama', DB::raw('SUM(d.anggaran) as total'))
            ->where('b.program_id', $this->program_id)
            ->groupBy('b.id', 'b.kode', 'b.nama')
            ->get();

        return view('livewire.anggaran.kegiatan-component', [
            'kegiatans' => $results,
            'namaProgram' => $namaProgram,
        ]);
    }

    public function resetInput()
    {
        $this->kode = '';
        $this->nama = '';
        $this->kegiatanId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        Kegiatan::create([
            'program_id' => $this->program_id,
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
        $('#kegiatanModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $this->kode = $kegiatan->kode;
        $this->nama = $kegiatan->nama;
        $this->kegiatanId = $kegiatan->id;
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate([
            'kode' => 'required|unique:kegiatans,kode,' . $this->kegiatanId,
            'nama' => 'required|string|max:255',
        ]);

        $kegiatan = Kegiatan::findOrFail($this->kegiatanId);
        $kegiatan->update([
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
        $('#kegiatanModal').modal('hide');
        JS);
    }

    public function delete_confirmation($id)
    {

        $this->kegiatanId = $id;
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
        Kegiatan::destroy($this->kegiatanId);
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

    public function next($id)
    {
        $this->dispatch('sub-kegiatan', $id);
        $this->js(<<<'JS'
            setTimeout(function() {
                $('#subkegiatan').trigger("click");
            }, 0);
        JS);
    }
    public function resetAndCloseModal()
    {
        $this->resetInput();
        $this->js(<<<'JS'
            $('#kegiatanModal').modal('hide');
        JS);
    }
}
