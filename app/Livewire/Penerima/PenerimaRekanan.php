<?php

namespace App\Livewire\Penerima;

use Livewire\Component;
use App\Models\Penerima;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Search;

#[Title('Penerima/Rekanan')]
class PenerimaRekanan extends Component
{
    use WithPagination;
    public $search = '';
    public $paginate = 10;
    public $penerimas, $nama, $no_rekening, $bank, $penerima_id;
    public $isEdit = false;

    public function render()
    {
        // Ensure $this->search and $this->paginate are defined somewhere in the component
        $this->search = $this->search ?? '';
        $this->paginate = $this->paginate ?? 10; // Default pagination

        $rekenan = Penerima::where(function ($query) {
            $query->where('bank', 'like', '%' . $this->search . '%')
                ->orWhere('nama', 'like', '%' . $this->search . '%')
                ->orWhere('no_rekening', 'like', '%' . $this->search . '%');
        })
            ->orderBy('nama', 'asc') // Adjusted to 'nama' assuming 'kode' does not exist
            ->paginate($this->paginate);

        // Debugging line, uncomment if you need to check what $rekenan contains
        // dd($rekenan);

        return view(
            'livewire.penerima.penerima-rekanan',
            ['asu' => $rekenan] // Ensure this matches what your view expects
        );
    }


    public function resetInputFields()
    {
        $this->nama = '';
        $this->no_rekening = '';
        $this->bank = '';
        $this->penerima_id = null;
    }

    public function store()
    {
        $validatedData = $this->validate([
            'nama' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'bank' => 'required|string|max:255'
        ]);

        Penerima::create($validatedData);
        $this->resetInputFields();
        $this->js(<<<'JS'
        $('#penerimaModal').modal('hide');
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
        $penerima = Penerima::findOrFail($id);
        $this->penerima_id = $id;
        $this->nama = $penerima->nama;
        $this->no_rekening = $penerima->no_rekening;
        $this->bank = $penerima->bank;
        $this->isEdit = true;
        $this->js(<<<'JS'
        $('#penerimaModal').modal('show');
    JS);
    }

    public function update()
    {
        $validatedData = $this->validate([
            'nama' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'bank' => 'required|string|max:255'
        ]);

        if ($this->penerima_id) {
            $penerima = Penerima::find($this->penerima_id);
            $penerima->update($validatedData);
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
        $('#penerimaModal').modal('hide');
    JS);
        }
    }
    public function delete_confirmation($id)
    {

        $this->penerima_id = $id;
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
        Penerima::destroy($this->penerima_id);
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
