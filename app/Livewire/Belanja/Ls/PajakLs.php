<?php

namespace App\Livewire\Belanja\Ls;

use Livewire\Component;
use App\Models\BelanjaLs;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\PajakLs as ModelsPajakLs;

#[Title('Pajak LS')]
class PajakLs extends Component
{
    use WithPagination;

    public $belanja_ls_id, $jenis_pajak, $no_billing, $nominal, $pajakId;
    public $updateMode = false;

    public $belanjaLs; // Header transaksi LS
    public $no_bukti, $uraian, $nilai;

    protected $rules = [
        'belanja_ls_id' => 'required|exists:belanja_ls,id',
        'nominal'       => 'required|numeric|min:0',
    ];

    public function mount($belanjaLsId = null)
    {
        $this->belanja_ls_id = $belanjaLsId ?: null;
        $header = BelanjaLs::find($this->belanja_ls_id);
        if ($header) {
            $this->belanjaLs = $header;
            $this->no_bukti = $header->no_bukti;
            $this->uraian   = $header->uraian;
            $this->nilai    = $header->total_nilai;
        }
    }

    public function render()
    {
        return view('livewire.belanja.ls.pajak-ls', [
            'pajaks' => ModelsPajakLs::with('belanjaLs')
                ->where('belanja_ls_id', $this->belanja_ls_id)
                ->paginate(10),
            'belanjaLsList' => BelanjaLs::all(), // Jika diperlukan
            'no_bukti' => $this->no_bukti,
            'uraian'   => $this->uraian,
        ]);
    }

    public function store()
    {
        $validatedData = $this->validate([
            'belanja_ls_id' => 'required|exists:belanja_ls,id',
            'jenis_pajak'   => 'required|string',
            'no_billing'    => 'required|string',
            'nominal'       => 'required|numeric|min:0',
        ]);

        $existingPajak = ModelsPajakLs::where('belanja_ls_id', $validatedData['belanja_ls_id'])
            ->where('jenis_pajak', $validatedData['jenis_pajak'])
            ->first();

        if ($existingPajak) {
            $this->js(<<<'JS'
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Jenis pajak ini sudah dipilih untuk transaksi LS ini.',
                    confirmButtonText: 'OK'
                });
            JS);
            return;
        }
        ModelsPajakLs::create($validatedData);
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
            'belanja_ls_id' => 'required|exists:belanja_ls,id',
            'jenis_pajak'   => 'required|string',
            'no_billing'    => 'required|string',
            'nominal'       => 'required|numeric|min:0',
        ]);

        $existingPajak = ModelsPajakLs::where('belanja_ls_id', $validatedData['belanja_ls_id'])
            ->where('jenis_pajak', $validatedData['jenis_pajak'])
            ->where('id', '!=', $this->pajakId)
            ->first();

        if ($existingPajak) {
            $this->js(<<<'JS'
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Jenis pajak ini sudah dipilih untuk transaksi LS ini.',
                    confirmButtonText: 'OK'
                });
            JS);
            return;
        }

        ModelsPajakLs::updateOrCreate(
            ['id' => $this->pajakId],
            $validatedData
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
        $pajak = ModelsPajakLs::findOrFail($id);
        $this->pajakId = $id;
        $this->belanja_ls_id = $pajak->belanja_ls_id;
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
                text: "Data ini akan dihapus dan proses ini tidak dapat dikembalikan.",
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
        ModelsPajakLs::destroy($this->pajakId);
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

    // public function js($script)
    // {
    //     $this->dispatch('runJs', ['script' => $script]);
    // }

    public function renderPajak()
    {
        return view('livewire.belanja.pajak-ls');
    }
}
