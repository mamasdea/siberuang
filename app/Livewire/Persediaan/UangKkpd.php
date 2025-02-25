<?php

namespace App\Livewire\Persediaan;

use App\Models\UangKkpd as ModelUangKkpd;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

#[Title('Uang KKPD')]
class UangKkpd extends Component
{
    public $uangGiros, $no_bukti, $tanggal, $uraian, $nominal, $uangGiroId;
    public $isEditMode = false;

    protected $rules = [
        'no_bukti' => 'required|string|max:255',
        'tanggal' => 'required|date',
        'uraian' => 'required|string|max:255',
        'nominal' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->uangGiros = ModelUangKkpd::orderBy('tanggal', 'desc')->get();
    }

    public function render()
    {
        $this->uangGiros = ModelUangKkpd::orderBy('tanggal', 'desc')->get();
        return view('livewire.persediaan.uang-kkpd');
    }

    public function resetInput()
    {
        $this->no_bukti = '';
        $this->tanggal = '';
        $this->uraian = '';
        $this->nominal = '';
        $this->uangGiroId = null;
        $this->isEditMode = false;
    }

    public function store()
    {
        $this->validate();

        ModelUangKkpd::create([
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nominal' => $this->nominal,
        ]);

        $this->resetInput();
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil disimpan!', timer: 1500, showConfirmButton: false });
            $('#uangGiroModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $uangGiro = ModelUangKkpd::findOrFail($id);
        $this->no_bukti = $uangGiro->no_bukti;
        $this->tanggal = $uangGiro->tanggal;
        $this->uraian = $uangGiro->uraian;
        $this->nominal = $uangGiro->nominal;
        $this->uangGiroId = $uangGiro->id;
        $this->isEditMode = true;
    }

    public function update()
    {
        $this->validate();

        $uangGiro = ModelUangKkpd::findOrFail($this->uangGiroId);
        $uangGiro->update([
            'no_bukti' => $this->no_bukti,
            'tanggal' => $this->tanggal,
            'uraian' => $this->uraian,
            'nominal' => $this->nominal,
        ]);

        $this->resetInput();
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Data berhasil diperbarui!', timer: 1500, showConfirmButton: false });
            $('#uangGiroModal').modal('hide');
        JS);
    }

    public function deleteConfirmation($id)
    {
        $this->uangGiroId = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.delete();
                }
            });
        JS);
    }

    public function delete()
    {
        ModelUangKkpd::destroy($this->uangGiroId);

        $this->js(<<<'JS'
            Swal.fire({ icon: 'error', title: 'Data berhasil dihapus!', timer: 1500, showConfirmButton: false });
        JS);
    }
}
