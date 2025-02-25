<?php

namespace App\Livewire\Belanja\Gu;

use App\Models\Belanja;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Pajak as ModelsPajak;

#[Title('Pajak GU')]
class Pajak extends Component
{
    use WithPagination;

    public $belanja_id, $jenis_pajak, $no_billing, $nominal, $pajakId;
    public $updateMode = false;

    public $belanja;
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
        return view('livewire.belanja.gu.pajak', [
            'pajaks' => ModelsPajak::with('belanja')->where('belanja_id', $this->belanja_id)
                ->paginate(10),
            'belanjas' => Belanja::all(),
            'no_bukti' => $this->no_bukti,
            'uraian' => $this->uraian,
            'no_bukti' => $this->no_bukti,
        ]);
    }

    public function store()
    {
        $validatedData = $this->validate([
            'belanja_id' => 'required|exists:belanjas,id',
            'jenis_pajak' => 'required|string',
            'no_billing' => 'required|string',
            'nominal' => 'required|numeric|min:0',
        ]);

        $existingPajak = ModelsPajak::where('belanja_id', $validatedData['belanja_id'])
            ->where('jenis_pajak', $validatedData['jenis_pajak'])
            ->first();

        if ($existingPajak) {
            $this->js(<<<'JS'
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Jenis pajak ini sudah dipilih untuk belanja ini.',
                confirmButtonText: 'OK'
            });
        JS);
            return;
        }
        ModelsPajak::create([
            'belanja_id' => $validatedData['belanja_id'],
            'jenis_pajak' => $validatedData['jenis_pajak'],
            'no_billing' => $validatedData['no_billing'],
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
        $('#formPotonganPajakModal').modal('hide');
    JS);

        $this->resetFields();
    }


    public function update()
    {

        $validatedData = $this->validate([
            'belanja_id' => 'required|exists:belanjas,id',
            'jenis_pajak' => 'required|string',
            'no_billing' => 'required|string',
            'nominal' => 'required|numeric|min:0',
        ]);

        $existingPajak = ModelsPajak::where('belanja_id', $validatedData['belanja_id'])
            ->where('jenis_pajak', $validatedData['jenis_pajak'])
            ->where('id', '!=', $this->pajakId)
            ->first();

        if ($existingPajak) {
            $this->js(<<<'JS'
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Jenis pajak ini sudah dipilih untuk belanja ini.',
                    confirmButtonText: 'OK'
                });
            JS);
            return;
        }

        // Update atau buat data pajak baru jika tidak ada duplikat
        ModelsPajak::updateOrCreate(
            ['id' => $this->pajakId],
            [
                'belanja_id' => $validatedData['belanja_id'],
                'jenis_pajak' => $validatedData['jenis_pajak'],
                'no_billing' => $validatedData['no_billing'],
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
            $('#formPotonganPajakModal').modal('hide');
        JS);

        $this->resetFields();
    }


    public function edit($id)
    {
        $pajak = ModelsPajak::findOrFail($id);
        $this->pajakId = $id;
        $this->belanja_id = $pajak->belanja_id;
        $this->jenis_pajak = $pajak->jenis_pajak;
        $this->no_billing = $pajak->no_billing;
        $this->nominal = $pajak->nominal;

        $this->updateMode = true;
    }



    public function delete_confirmation($id)
    {

        $this->pajakId = $id;
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
        ModelsPajak::destroy($this->pajakId);
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


    public function closeFormPajak()
    {
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->jenis_pajak = '';
        $this->no_billing = '';
        $this->nominal = '';
        $this->pajakId = null;
        $this->updateMode = false;
    }
}
