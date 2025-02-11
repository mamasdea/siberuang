<?php

namespace App\Livewire\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\PengelolaKeuangan as ModelsPengelolaKeuangan;

#[Title('Master')]
class PengelolaKeuangan extends Component
{
    use WithPagination;
    public $search = '';
    public $paginate = 10;
    public $pengelola, $nama, $nip, $jabatan, $bidang, $pengelola_id;
    public $isEdit = false;

    public function render()
    {
        // Ensure $this->search and $this->paginate are defined somewhere in the component
        $this->search = $this->search ?? '';
        $this->paginate = $this->paginate ?? 10; // Default pagination

        $pengelola = ModelsPengelolaKeuangan::where(function ($query) {
            $query->where('jabatan', 'like', '%' . $this->search . '%')
                ->orWhere('nama', 'like', '%' . $this->search . '%')
                ->orWhere('nip', 'like', '%' . $this->search . '%');
        })
            ->orderBy('id', 'asc') // Adjusted to 'nama' assuming 'kode' does not exist
            ->paginate($this->paginate);


        return view('livewire.master.pengelola-keuangan', [
            'asu' => $pengelola
        ]);
    }


    public function resetInputFields()
    {
        $this->nama = '';
        $this->nip = '';
        $this->jabatan = '';
        $this->bidang = '';
        $this->pengelola_id = null;
    }

    public function store()
    {
        $validatedData = $this->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'bidang' => 'required|string|max:255'
        ]);

        ModelsPengelolaKeuangan::create($validatedData);
        $this->resetInputFields();
        $this->js(<<<'JS'
        $('#pengelolaModal').modal('hide');
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
        title: "Data berhasil disimpan"
    });
    JS);
    }

    public function edit($id)
    {
        $pengelola = ModelsPengelolaKeuangan::findOrFail($id);
        $this->pengelola_id = $id;
        $this->nama = $pengelola->nama;
        $this->nip = $pengelola->nip;
        $this->jabatan = $pengelola->jabatan;
        $this->bidang = $pengelola->bidang;
        $this->isEdit = true;
        $this->js(<<<'JS'
        $('#pengelolaModal').modal('show');
    JS);
    }

    public function update()
    {
        $validatedData = $this->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'bidang' => 'required|string|max:255'
        ]);

        if ($this->pengelola_id) {
            $pengelola = ModelsPengelolaKeuangan::find($this->pengelola_id);
            $pengelola->update($validatedData);
            $this->resetInputFields();
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
            $this->isEdit = false;
            $this->js(<<<'JS'
        $('#pengelolaModal').modal('hide');
    JS);
        }
    }
    public function delete_confirmation($id)
    {

        $this->pengelola_id = $id;
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
        ModelsPengelolaKeuangan::destroy($this->pengelola_id);
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
}
