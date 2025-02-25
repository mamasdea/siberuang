<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\Belanja;
use Livewire\Component;
use App\Models\Penerima;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Penerimaan as ModelsPenerimaan;

#[Title('Penerimaan')]
class Penerimaan extends Component
{
    use WithPagination;

    public $belanja_id, $penerima_id, $nominal, $penerimaanId;
    public $updateMode = false;

    public $penerima, $belanja, $penerima_nama, $penerima_bank, $penerima_no_rekening, $belanjaId;
    public $openPenerima = false;
    public $no_bukti, $uraian, $nilai;


    protected $rules = [
        'belanja_id' => 'required|exists:belanjas,id',
        'penerima_id' => 'required|exists:penerimas,id',
        'nominal' => 'required|numeric|min:0',
    ];

    public function mount($belanjaId = null)
    {
        $this->belanja_id = $belanjaId ?: 'default_value';
        $belanja = Belanja::find($this->belanja_id);
        $this->no_bukti = $belanja->no_bukti;
        $this->uraian = $belanja->uraian;
        $this->nilai = $belanja->nilai;
    }

    public function render()
    {
        return view('livewire.belanja.gu.penerimaan', [
            'penerimaans' => ModelsPenerimaan::with('belanja', 'penerima')->where('belanja_id', $this->belanja_id)
                ->paginate(10),
            'belanjas' => Belanja::all(),
            'penerimas' => Penerima::all(),
            'no_bukti' => $this->no_bukti,
            'uraian' => $this->uraian,
            'no_bukti' => $this->no_bukti,
        ]);
    }

    public function store()
    {
        $validatedData = $this->validate([
            'belanja_id' => 'required|exists:belanjas,id',
            'penerima_id' => 'nullable|exists:penerimas,id',
            'nominal' => 'required|numeric|min:0',
        ]);
        $belanja = Belanja::find($validatedData['belanja_id']);
        $totalPenerimaan = ModelsPenerimaan::where('belanja_id', $validatedData['belanja_id'])->sum('nominal');
        if ($totalPenerimaan + $validatedData['nominal'] > $belanja->nilai) {
            $this->js(<<<'JS'
            Swal.fire({
                icon: 'error',
                title: 'Jumlah Total Penerimaan',
                text: 'Nominal penerimaan tidak boleh melebihi nilai belanja.',
                confirmButtonText: 'OK'
            });
        JS);
            return;
        }
        if (empty($this->penerima_id)) {
            $penerima = Penerima::create([
                'nama' => $this->penerima_nama,
                'bank' => $this->penerima_bank,
                'no_rekening' => $this->penerima_no_rekening,
            ]);
            $validatedData['penerima_id'] = $penerima->id;
        } else {
            $validatedData['penerima_id'] = $this->penerima_id;
        }

        ModelsPenerimaan::create([
            'belanja_id' => $validatedData['belanja_id'],
            'penerima_id' => $validatedData['penerima_id'],
            'nominal' => $validatedData['nominal'],
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
            $('#formPenerimaanModal').modal('hide');
        JS);


        $this->resetFields();
    }


    public function update()
    {
        $validatedData = $this->validate([
            'belanja_id' => 'required|exists:belanjas,id',
            'penerima_id' => 'nullable|exists:penerimas,id',
            'nominal' => 'required|numeric|min:0',
        ]);
        $belanja = Belanja::find($validatedData['belanja_id']);
        $totalPenerimaan = ModelsPenerimaan::where('belanja_id', $validatedData['belanja_id'])->where('id', '!=', $this->penerimaanId)->sum('nominal');
        if ($totalPenerimaan + $validatedData['nominal'] > $belanja->nilai) {
            $this->js(<<<'JS'
                Swal.fire({
                    icon: 'error',
                    title: 'Jumlah Total Penerimaan',
                    text: 'Nominal penerimaan tidak boleh melebihi nilai belanja.',
                    confirmButtonText: 'OK'
                });
            JS);
            return;
        }
        if (empty($this->penerima_id)) {
            $penerima = Penerima::create([
                'nama' => $this->penerima_nama,
                'bank' => $this->penerima_bank,
                'no_rekening' => $this->penerima_no_rekening,
            ]);
            $validatedData['penerima_id'] = $penerima->id;
        } else {
            $validatedData['penerima_id'] = $this->penerima_id;
        }
        ModelsPenerimaan::updateOrCreate(
            ['id' => $this->penerimaanId],
            [
                'belanja_id' => $validatedData['belanja_id'],
                'penerima_id' => $validatedData['penerima_id'],
                'nominal' => $validatedData['nominal'],
            ]
        );
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
            $('#formPenerimaanModal').modal('hide');
        JS);

        $this->resetFields();
    }



    public function edit($id)
    {
        $penerimaan = ModelsPenerimaan::findOrFail($id);
        $this->penerimaanId = $id;
        $this->belanja_id = $penerimaan->belanja_id;
        $this->penerima_id = $penerimaan->penerima_id;
        $this->nominal = $penerimaan->nominal;

        $penerima = Penerima::find($penerimaan->penerima_id);
        if ($penerima) {
            $this->penerima_nama = $penerima->nama;
            $this->penerima_bank = $penerima->bank;
            $this->penerima_no_rekening = $penerima->no_rekening;
        }

        $this->updateMode = true;
    }

    public function delete_confirmation($id)
    {

        $this->penerimaanId = $id;
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
        ModelsPenerimaan::destroy($this->penerimaanId);
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


    public function openModalPenerima()
    {
        $this->js(<<<'JS'
        $('#PenerimaanModal').modal('show');
    JS);
    }

    public function closeModalPenerima()
    {
        $this->js(<<<'JS'
        $('#PenerimaanModal').modal('hide');
    JS);
    }

    public function closeFormPenerimaan()
    {
        $this->resetFields();
    }

    #[On('kirim_penerima')]
    public function updatePostListPenerima($id)
    {
        $this->penerima = Penerima::find($id);
        $this->penerima_id = $this->penerima->id;
        $this->penerima_nama = $this->penerima->nama;
        $this->penerima_bank = $this->penerima->bank;
        $this->penerima_no_rekening = $this->penerima->no_rekening;
        $this->penerima_id = $id;
        $this->closeModalPenerima();
    }
    public function resetFields()
    {
        $this->penerima_id = '';
        $this->nominal = '';
        $this->penerimaanId = null;
        $this->updateMode = false;
        $this->resetPenerima();
    }
    public function resetPenerima()
    {
        $this->penerima_id = null;
        $this->penerima_nama = null;
        $this->penerima_bank = null;
        $this->penerima_no_rekening = null;
    }
}
