<?php

namespace App\Livewire\Anggaran;

use App\Models\Rka;
use Livewire\Component;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
use App\Models\RekeningBelanja;
use Livewire\Attributes\Reactive;

#[Title('Anggaran')]
class RkaComponent extends Component
{
    #[Reactive]
    public $sub_kegiatan_id;
    public $kegiatan_id;
    public $kode_belanja, $nama_belanja, $anggaran;
    public $rkaId;
    public $isEditMode = false;
    public $rekeningBelanjaList;

    protected $rules = [
        'kode_belanja' => 'required|unique:rkas,kode_belanja',
        'nama_belanja' => 'required|string|max:255',
        'anggaran' => 'required|numeric|min:0',
    ];

    public function mount($subKegiatanId)
    {
        $this->sub_kegiatan_id = $subKegiatanId;
        $this->loadRekeningBelanja();
    }

    public function render()
    {

        $namaSubKegiatan = SubKegiatan::with('kegiatan.program')->find($this->sub_kegiatan_id);

        $namaKegiatan = $namaSubKegiatan ? $namaSubKegiatan->kegiatan : null;

        return view('livewire.anggaran.r-k-a-component', [
            'rkas' => Rka::where('sub_kegiatan_id', $this->sub_kegiatan_id)->get(),
            'namaSubKegiatan' => $namaSubKegiatan,
            'namaKegiatan' => $namaKegiatan,
            'namaProgram' => $namaKegiatan ? $namaKegiatan->program->nama : 'Tidak Ditemukan',
        ]);
    }

    public function resetInput()
    {
        $this->kode_belanja = '';
        $this->nama_belanja = '';
        $this->anggaran = '';
        $this->rkaId = null;
        $this->isEditMode = false;
    }


    public function loadRekeningBelanja()
    {
        $this->rekeningBelanjaList = RekeningBelanja::all();
    }


    public function store()
    {
        $this->validate([
            'selectedRekeningBelanja' => 'required',
            'anggaran' => 'required|numeric|min:0',
        ]);

        // Pisahkan kode dan nama dari Select2
        list($kode, $uraian) = explode('|', $this->selectedRekeningBelanja);

        Rka::create([
            'sub_kegiatan_id' => $this->sub_kegiatan_id,
            'kode_belanja' => $kode,
            'nama_belanja' => $uraian,
            'anggaran' => $this->anggaran,
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
                title: "Data berhasil ditambahkan"
            });
            $('#rkaModal').modal('hide');
        JS);

        $this->resetInput();
    }

    public function edit($id)
    {
        $rka = Rka::findOrFail($id);
        $this->kode_belanja = $rka->kode_belanja;
        $this->nama_belanja = $rka->nama_belanja;
        $this->anggaran = $rka->anggaran;
        $this->rkaId = $rka->id;
        $this->isEditMode = true;

        $this->js(<<<'JS'
            $('#rkaModal').modal('show');
        JS);
    }

    public function update()
    {
        $this->validate([
            'kode_belanja' => 'required|unique:rkas,kode_belanja,' . $this->rkaId,
            'nama_belanja' => 'required|string|max:255',
            'anggaran' => 'required|numeric|min:0',
        ]);

        $rka = Rka::findOrFail($this->rkaId);
        $rka->update([
            'kode_belanja' => $this->kode_belanja,
            'nama_belanja' => $this->nama_belanja,
            'anggaran' => $this->anggaran,
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
            $('#rkaModal').modal('hide');
        JS);

        $this->resetInput();
    }


    public function delete_confirmation($id)
    {

        $this->rkaId = $id;
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
        Rka::destroy($this->rkaId);
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



    public function resetAndCloseModal()
    {
        $this->resetInput();
        $this->js(<<<'JS'
            $('#rkaModal').modal('hide');
        JS);
    }
}
