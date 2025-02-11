<?php

namespace App\Livewire\Master;

use Livewire\Component;

use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RekeningBelanjaImport;
use App\Models\RekeningBelanja as ModelRekeningBelanja; // Menggunakan alias Model
#[Title('Master')]
class RekeningBelanja extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $file;
    public $search = '';
    public $paginate = 10;
    public $kode, $uraian_belanja, $rekeningId;
    public $rekening_belanjas;
    public $isEdit = false;

    public function render()
    {
        $rekening_belanjas = ModelRekeningBelanja::where(function ($query) {
            $query->where('kode', 'like', '%' . $this->search . '%')
                ->orWhere('uraian_belanja', 'like', '%' . $this->search . '%');
        })
            ->orderBy('kode', 'asc')
            ->paginate($this->paginate ?? 10);
        // dd($rekening_belanjas);
        return view('livewire.master.rekening-belanja', [
            'rek_belanja' => $rekening_belanjas,
        ]);
    }

    public function save()
    {
        $this->validate([
            'kode' => 'required|string|unique:rekening_belanjas,kode|max:255',
            'uraian_belanja' => 'required|string|max:255',
        ]);

        ModelRekeningBelanja::updateOrCreate(['id' => $this->rekeningId], [
            'kode' => $this->kode,
            'uraian_belanja' => $this->uraian_belanja,
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
        $this->js(<<<'JS'
            $('#modalForm').modal('hide');
        JS);
        $this->resetFields();
    }
    public function update()
    {
        $this->validate([
            'kode' => 'required|string|max:255',
            'uraian_belanja' => 'required|string|max:255',
        ]);

        ModelRekeningBelanja::updateOrCreate(['id' => $this->rekeningId], [
            'kode' => $this->kode,
            'uraian_belanja' => $this->uraian_belanja,
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
        $this->js(<<<'JS'
        $('#modalForm').modal('hide');
    JS);
        $this->isEdit = false;
        $this->resetFields();
    }

    public function edit($id)
    {
        $rekening = ModelRekeningBelanja::find($id);
        $this->rekeningId = $rekening->id;
        $this->kode = $rekening->kode;
        $this->uraian_belanja = $rekening->uraian_belanja;
        $this->isEdit = true;

        $this->js(<<<'JS'
        $('#modalForm').modal('show');
    JS);
    }

    public function delete_confirmation($id)
    {

        $this->rekeningId = $id;
        $this->js(<<<'JS'
        Swal.fire({
            title: 'Apakah Anda yakin?',
                text: "Apakah kamu ingin menghapus data ini? proses ini tidak dapat dikembalikan.",
                 icon: "warning",
                // imageUrl: "/icon-warning.png",
                // imageWidth: 90,
                // imageHeight: 85,
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
        // ModelRekeningBelanja::find($id)->delete();
        ModelRekeningBelanja::destroy($this->rekeningId);
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

    private function resetFields()
    {
        $this->kode = '';
        $this->uraian_belanja = '';
        $this->rekeningId = null;

        $this->js(<<<'JS'
        $('#modalForm').modal('hide');
    JS);
    }

    public function resetAndCloseModal()
    {
        $this->resetFields();
        $this->js(<<<'JS'
            $('#programModal').modal('hide');
        JS);
    }

    private function resetFormImport()
    {
        $this->file = '';
        $this->js(<<<'JS'
        $('#modalForm').modal('hide');
    JS);
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        Excel::import(new RekeningBelanjaImport, $this->file->store('temp'));
        $this->js(<<<'JS'
            $('#importModal').modal('hide');
        JS);
        // session()->flash('message', 'Data berhasil diimport.');
        $this->resetFormImport();
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
                title: "File Berhasil di Import"
            });
    JS);
    }
}
