<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\BelanjaTu;
use App\Models\PajakTu;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Pajak TU')]
class PajakTuPage extends Component
{
    use WithPagination;

    public $belanja_tu_id, $jenis_pajak, $no_billing, $ntpn, $ntb, $nominal, $pajakId;
    public $updateMode = false;
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
        return view('livewire.belanja.tu.pajak-tu-page', [
            'pajaks' => PajakTu::where('belanja_tu_id', $this->belanja_tu_id)->paginate(10),
        ]);
    }

    public function store()
    {
        $this->validate([
            'jenis_pajak' => 'required',
            'no_billing' => 'required',
            'nominal' => [
                'required', 'numeric', 'min:0',
                function ($attr, $value, $fail) {
                    $totalPenerimaan = \App\Models\PenerimaanTu::where('belanja_tu_id', $this->belanja_tu_id)->sum('nominal');
                    $totalPajak = PajakTu::where('belanja_tu_id', $this->belanja_tu_id)->sum('nominal');
                    if (($totalPenerimaan + $totalPajak + $value) > $this->nilai) {
                        $fail('Total penerimaan + pajak tidak boleh melebihi nilai belanja (Rp ' . number_format($this->nilai, 0, ',', '.') . ')');
                    }
                },
            ],
        ]);

        $exists = PajakTu::where('belanja_tu_id', $this->belanja_tu_id)
            ->where('jenis_pajak', $this->jenis_pajak)->first();
        if ($exists) {
            $this->js("Swal.fire({ icon: 'warning', title: 'Jenis pajak ini sudah ada!', timer: 2000, showConfirmButton: false });");
            return;
        }

        PajakTu::create([
            'belanja_tu_id' => $this->belanja_tu_id,
            'jenis_pajak' => $this->jenis_pajak,
            'no_billing' => $this->no_billing,
            'ntpn' => $this->ntpn ?: null,
            'ntb' => $this->ntb ?: null,
            'nominal' => $this->nominal,
        ]);

        $this->resetInput();
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Pajak berhasil ditambahkan!', timer: 1500, showConfirmButton: false });
            $('#formPajakTuModal').modal('hide');
        JS);
    }

    public function edit($id)
    {
        $pajak = PajakTu::findOrFail($id);
        $this->pajakId = $pajak->id;
        $this->jenis_pajak = $pajak->jenis_pajak;
        $this->no_billing = $pajak->no_billing;
        $this->ntpn = $pajak->ntpn;
        $this->ntb = $pajak->ntb;
        $this->nominal = $pajak->nominal;
        $this->updateMode = true;
        $this->js("$('#formPajakTuModal').modal('show');");
    }

    public function update()
    {
        $this->validate([
            'jenis_pajak' => 'required',
            'no_billing' => 'required',
            'nominal' => [
                'required', 'numeric', 'min:0',
                function ($attr, $value, $fail) {
                    $totalPenerimaan = \App\Models\PenerimaanTu::where('belanja_tu_id', $this->belanja_tu_id)->sum('nominal');
                    $totalPajak = PajakTu::where('belanja_tu_id', $this->belanja_tu_id)
                        ->where('id', '!=', $this->pajakId)->sum('nominal');
                    if (($totalPenerimaan + $totalPajak + $value) > $this->nilai) {
                        $fail('Total penerimaan + pajak tidak boleh melebihi nilai belanja (Rp ' . number_format($this->nilai, 0, ',', '.') . ')');
                    }
                },
            ],
        ]);

        $exists = PajakTu::where('belanja_tu_id', $this->belanja_tu_id)
            ->where('jenis_pajak', $this->jenis_pajak)
            ->where('id', '!=', $this->pajakId)->first();
        if ($exists) {
            $this->js("Swal.fire({ icon: 'warning', title: 'Jenis pajak ini sudah ada!', timer: 2000, showConfirmButton: false });");
            return;
        }

        PajakTu::findOrFail($this->pajakId)->update([
            'jenis_pajak' => $this->jenis_pajak,
            'no_billing' => $this->no_billing,
            'ntpn' => $this->ntpn ?: null,
            'ntb' => $this->ntb ?: null,
            'nominal' => $this->nominal,
        ]);

        $this->resetInput();
        $this->js(<<<'JS'
            Swal.fire({ icon: 'success', title: 'Pajak berhasil diupdate!', timer: 1500, showConfirmButton: false });
            $('#formPajakTuModal').modal('hide');
        JS);
    }

    public function delete_confirmation($id)
    {
        $this->pajakId = $id;
        $this->js(<<<'JS'
            Swal.fire({ title: 'Yakin hapus?', text: "Data tidak dapat dikembalikan!", icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", confirmButtonText: "Hapus" }).then((r) => { if(r.isConfirmed) $wire.delete() });
        JS);
    }

    public function delete()
    {
        PajakTu::destroy($this->pajakId);
        $this->js("Swal.fire({ icon: 'error', title: 'Data dihapus!', timer: 1500, showConfirmButton: false });");
    }

    private function resetInput()
    {
        $this->jenis_pajak = '';
        $this->no_billing = '';
        $this->ntpn = '';
        $this->ntb = '';
        $this->nominal = '';
        $this->pajakId = null;
        $this->updateMode = false;
    }
}
