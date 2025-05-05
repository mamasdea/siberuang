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
    public $kode_belanja, $nama_belanja, $penetapan, $perubahan, $selisih, $anggaran;
    public $rkaId;
    public $isEditMode = false;
    public $rekeningBelanjaList;
    public $selectedRekeningBelanja;

    protected $rules = [
        'selectedRekeningBelanja' => 'required',
        'penetapan' => 'required|numeric|min:0',
        'perubahan' => 'nullable|numeric|min:0',
        'selisih' => 'nullable|numeric',
        'anggaran' => 'numeric|min:0',
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
        $this->penetapan = 0;
        $this->perubahan = 0;
        $this->selisih = 0;
        $this->anggaran = 0;
        $this->rkaId = null;
        $this->isEditMode = false;
        $this->selectedRekeningBelanja = '';
    }

    public function loadRekeningBelanja()
    {
        $this->rekeningBelanjaList = RekeningBelanja::all();
    }

    public function updatedPerubahan()
    {
        // Hitung otomatis nilai selisih dan anggaran
        $this->selisih = isset($this->perubahan) ? ($this->perubahan ?? 0) - $this->penetapan : 0;
        $this->anggaran = isset($this->perubahan) ? $this->perubahan : $this->penetapan;
    }

    public function store()
    {
        $this->validate();

        list($kode, $uraian) = explode('|', $this->selectedRekeningBelanja);

        Rka::create([
            'sub_kegiatan_id' => $this->sub_kegiatan_id,
            'kode_belanja' => $kode,
            'nama_belanja' => $uraian,
            'penetapan' => $this->penetapan,
            'perubahan' => $this->perubahan,
            'selisih' => isset($this->perubahan) ? ($this->perubahan ?? 0) - $this->penetapan : 0,
            'anggaran' => $this->perubahan  ? $this->perubahan : $this->penetapan,
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
        $this->rkaId = $rka->id;
        $this->kode_belanja = $rka->kode_belanja;
        $this->nama_belanja = $rka->nama_belanja;
        $this->penetapan = $rka->penetapan;
        $this->perubahan = $rka->perubahan;
        $this->selisih = $rka->selisih;
        $this->anggaran = $rka->anggaran;
        $this->selectedRekeningBelanja = $rka->kode_belanja;

        $this->isEditMode = true;

        $this->js(<<<'JS'
            $('#rkaModal').modal('show');
        JS);
    }

    public function update()
    {
        $this->validate();


        $nama = RekeningBelanja::where('kode', $this->selectedRekeningBelanja)->first();

        $rka = Rka::findOrFail($this->rkaId);
        $rka->update([
            'kode_belanja' => $this->selectedRekeningBelanja,
            'nama_belanja' => $nama->uraian_belanja,
            'penetapan' => $this->penetapan,
            'perubahan' => $this->perubahan,
            'selisih' => isset($this->perubahan) ? ($this->perubahan ?? 0) - $this->penetapan : 0,
            'anggaran' => isset($this->perubahan) ? $this->perubahan : $this->penetapan,
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
                text: "Data ini akan dihapus dan tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, hapus!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete();
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
