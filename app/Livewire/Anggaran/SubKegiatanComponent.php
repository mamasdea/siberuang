<?php

namespace App\Livewire\Anggaran;

use App\Models\Program;
use Livewire\Component;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Livewire\Attributes\Title;
use App\Models\PengelolaKeuangan;
use Livewire\Attributes\Reactive;

#[Title('Anggaran')]
class SubKegiatanComponent extends Component
{
    #[Reactive]
    public $kegiatan_id;
    public $kode, $nama, $pptk_id, $pptks;
    public $subKegiatanId;
    public $isEditMode = false;

    protected $rules = [
        'kode' => 'required|unique:kegiatans,kode',
        'nama' => 'required|string|max:255',
    ];

    public function mount($kegiatanId)
    {
        $this->kegiatan_id = $kegiatanId;
        $this->pptks = PengelolaKeuangan::where('jabatan', 'PPTK')->get();
    }

    public function render()
    {
        $namaKegiatan = Kegiatan::with('program')->where('id', $this->kegiatan_id)->first();
        return view('livewire.anggaran.sub-kegiatan-component', [
            'subKegiatans' => SubKegiatan::with(['Rka'])->where('kegiatan_id', $this->kegiatan_id)->get(),
            'namaKegiatan' => $namaKegiatan,
        ]);
    }

    public function resetInput()
    {
        $this->kode = '';
        $this->nama = '';
        $this->subKegiatanId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        SubKegiatan::create([
            'kegiatan_id' => $this->kegiatan_id,
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
                title: "Data berhasil ditambahkan"
            });
            $('#subKegiatanModal').modal('hide');
        JS);

        $this->resetInput();
    }

    public function edit($id)
    {
        $subKegiatan = SubKegiatan::findOrFail($id);
        $this->kode = $subKegiatan->kode;
        $this->nama = $subKegiatan->nama;
        $this->subKegiatanId = $subKegiatan->id;
        $this->isEditMode = true;

        $this->js(<<<'JS'
            $('#subKegiatanModal').modal('show');
        JS);
    }

    public function update()
    {
        $this->validate([
            'kode' => 'required|unique:kegiatans,kode,' . $this->subKegiatanId,
            'nama' => 'required|string|max:255',
        ]);

        $subKegiatan = SubKegiatan::findOrFail($this->subKegiatanId);
        $subKegiatan->update([
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
            $('#subKegiatanModal').modal('hide');
        JS);

        $this->resetInput();
    }

    public function delete_confirmation($id)
    {

        $this->subKegiatanId = $id;
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
        SubKegiatan::destroy($this->subKegiatanId);
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
        $this->dispatch('r-k-a', $id);
        $this->js(<<<'JS'
            setTimeout(function() {
                $('#rka').trigger("click");
            }, 0);
        JS);
    }
    public function resetAndCloseModal()
    {
        $this->resetInput();
        $this->js(<<<'JS'
            $('#subKegiatanModal').modal('hide');
        JS);
    }

    public function modalPPTK($id)
    {
        $subKegiatan = SubKegiatan::findOrFail($id);
        $this->subKegiatanId = $subKegiatan->id;
        $this->pptk_id = $subKegiatan->pptk_id;

        $this->js(<<<'JS'
            $('#pptkModal').modal('show');
        JS);
    }
    public function storePPTK()
    {
        $this->validate([
            'pptk_id' => 'required',
        ], [
            'pptk_id.required' => 'PPTK harus dipilih.',
        ]);

        $subKegiatan = SubKegiatan::findOrFail($this->subKegiatanId);
        $subKegiatan->update([
            'pptk_id' => $this->pptk_id,
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
                title: "PPTK berhasil disimpan"
            });
            $('#pptkModal').modal('hide');
        JS);


        $this->closeModalPPTK();
    }

    public function closeModalPPTK()
    {
        $this->pptk_id = null;
        $this->js(<<<'JS'
            $('#pptkModal').modal('hide');
        JS);
    }
}
