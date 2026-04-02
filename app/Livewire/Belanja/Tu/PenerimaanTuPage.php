<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\BelanjaTu;
use App\Models\Penerima;
use App\Models\PenerimaanTu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Penerimaan TU')]
class PenerimaanTuPage extends Component
{
    use WithPagination;

    public $belanja_tu_id, $penerima_id, $nominal, $penerimaanId;
    public $updateMode = false;
    public $penerima_nama, $penerima_bank, $penerima_no_rekening;
    public $no_bukti, $uraian, $nilai, $sppSpmTuId;

    public function mount($belanjaTuId)
    {
        $this->belanja_tu_id = $belanjaTuId;
        $belanja = BelanjaTu::findOrFail($belanjaTuId);
        $this->no_bukti = $belanja->no_bukti;
        $this->uraian = $belanja->uraian;
        $this->nilai = $belanja->nilai;
        $this->sppSpmTuId = $belanja->spp_spm_tu_id;
    }

    public function render()
    {
        return view('livewire.belanja.tu.penerimaan-tu-page', [
            'penerimaans' => PenerimaanTu::where('belanja_tu_id', $this->belanja_tu_id)->paginate(10),
            'penerimas' => Penerima::all(),
        ]);
    }

    public function store()
    {
        $this->validate([
            'belanja_tu_id' => 'required',
            'penerima_id' => 'nullable',
            'nominal' => [
                'required', 'numeric', 'min:0',
                function ($attr, $value, $fail) {
                    $totalExisting = PenerimaanTu::where('belanja_tu_id', $this->belanja_tu_id)->sum('nominal');
                    $totalPajak = \App\Models\PajakTu::where('belanja_tu_id', $this->belanja_tu_id)->sum('nominal');
                    if (($totalExisting + $totalPajak + $value) > $this->nilai) {
                        $fail('Total penerimaan + pajak tidak boleh melebihi nilai belanja (Rp ' . number_format($this->nilai, 0, ',', '.') . ')');
                    }
                },
            ],
        ]);

        PenerimaanTu::create([
            'belanja_tu_id' => $this->belanja_tu_id,
            'penerima_id' => $this->penerima_id ?: null,
            'uraian' => $this->penerima_nama ?? '',
            'nominal' => $this->nominal,
        ]);

        $this->resetInput();
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Penerimaan berhasil ditambahkan!', timer: 1500, showConfirmButton: false });
            $('#formPenerimaanTuModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $penerimaan = PenerimaanTu::with('penerima')->findOrFail($id);
        $this->penerimaanId = $penerimaan->id;
        $this->penerima_id = $penerimaan->penerima_id;
        $this->penerima_nama = $penerimaan->uraian;
        $this->nominal = $penerimaan->nominal;
        if ($penerimaan->penerima) {
            $this->penerima_bank = $penerimaan->penerima->bank;
            $this->penerima_no_rekening = $penerimaan->penerima->no_rekening;
        }
        $this->updateMode = true;
        $this->js("$('#formPenerimaanTuModal').modal('show');");
    }

    public function update()
    {
        $this->validate([
            'nominal' => [
                'required', 'numeric', 'min:0',
                function ($attr, $value, $fail) {
                    $totalExisting = PenerimaanTu::where('belanja_tu_id', $this->belanja_tu_id)
                        ->where('id', '!=', $this->penerimaanId)->sum('nominal');
                    $totalPajak = \App\Models\PajakTu::where('belanja_tu_id', $this->belanja_tu_id)->sum('nominal');
                    if (($totalExisting + $totalPajak + $value) > $this->nilai) {
                        $fail('Total penerimaan + pajak tidak boleh melebihi nilai belanja (Rp ' . number_format($this->nilai, 0, ',', '.') . ')');
                    }
                },
            ],
        ]);

        $penerimaan = PenerimaanTu::findOrFail($this->penerimaanId);
        $penerimaan->update([
            'penerima_id' => $this->penerima_id ?: null,
            'uraian' => $this->penerima_nama ?? '',
            'nominal' => $this->nominal,
        ]);

        $this->resetInput();
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Penerimaan berhasil diupdate!', timer: 1500, showConfirmButton: false });
            $('#formPenerimaanTuModal').modal('hide');
        JS);
    }

    public function delete_confirmation($id)
    {
        $this->penerimaanId = $id;
        $this->js(<<<'JS'
            Swal.fire({ title: 'Yakin hapus?', text: "Data tidak dapat dikembalikan!", icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", confirmButtonText: "Hapus" }).then((r) => { if(r.isConfirmed) $wire.delete() });
        JS);
    }

    public function delete()
    {
        PenerimaanTu::destroy($this->penerimaanId);
        $this->js("Swal.fire({ icon: 'error', title: 'Data dihapus!', timer: 1500, showConfirmButton: false });");
    }

    public function selectPenerima($id)
    {
        $penerima = Penerima::find($id);
        if ($penerima) {
            $this->penerima_id = $penerima->id;
            $this->penerima_nama = $penerima->nama;
            $this->penerima_bank = $penerima->bank;
            $this->penerima_no_rekening = $penerima->no_rekening;
        }
        $this->js("$('#modalPenerimaTu').modal('hide');");
    }

    public function resetPenerima()
    {
        $this->penerima_id = null;
        $this->penerima_nama = '';
        $this->penerima_bank = '';
        $this->penerima_no_rekening = '';
    }

    private function resetInput()
    {
        $this->penerima_id = null;
        $this->penerima_nama = '';
        $this->penerima_bank = '';
        $this->penerima_no_rekening = '';
        $this->nominal = '';
        $this->penerimaanId = null;
        $this->updateMode = false;
    }
}
