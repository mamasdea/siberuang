<?php

namespace App\Livewire\Belanja\Tu;

use App\Models\BelanjaTu;
use App\Models\PajakTu;
use App\Models\PenerimaanTu;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Penerimaan & Pajak TU')]
class PenerimaanPajakTu extends Component
{
    public $belanjaTuId;
    public $belanjaTu = [];

    // Penerimaan
    public $pen_uraian, $pen_nominal;

    // Pajak
    public $paj_jenis, $paj_no_billing, $paj_ntpn, $paj_ntb, $paj_nominal;

    public function mount($belanjaTuId)
    {
        $this->belanjaTuId = $belanjaTuId;
        $bt = BelanjaTu::findOrFail($belanjaTuId);
        $this->belanjaTu = [
            'no_bukti' => $bt->no_bukti,
            'uraian' => $bt->uraian,
            'nilai' => $bt->nilai,
            'spp_spm_tu_id' => $bt->spp_spm_tu_id,
        ];
    }

    public function render()
    {
        return view('livewire.belanja.tu.penerimaan-pajak-tu', [
            'penerimaans' => PenerimaanTu::where('belanja_tu_id', $this->belanjaTuId)->get(),
            'pajaks' => PajakTu::where('belanja_tu_id', $this->belanjaTuId)->get(),
        ]);
    }

    // === PENERIMAAN ===
    public function openPenerimaanForm()
    {
        $this->pen_uraian = '';
        $this->pen_nominal = '';
        $this->js("$('#penerimaanModal').modal('show');");
    }

    public function closePenerimaanForm()
    {
        $this->js("$('#penerimaanModal').modal('hide');");
    }

    public function storePenerimaan()
    {
        $this->validate([
            'pen_uraian' => 'required|string',
            'pen_nominal' => 'required|numeric|min:0',
        ]);

        PenerimaanTu::create([
            'belanja_tu_id' => $this->belanjaTuId,
            'uraian' => $this->pen_uraian,
            'nominal' => $this->pen_nominal,
        ]);

        $this->pen_uraian = '';
        $this->pen_nominal = '';
        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: "success", title: "Penerimaan berhasil disimpan" });
            $('#penerimaanModal').modal('hide');
        JS);
    }

    public function deletePenerimaan($id)
    {
        PenerimaanTu::destroy($id);
        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: "error", title: "Penerimaan dihapus" });
        JS);
    }

    // === PAJAK ===
    public function openPajakForm()
    {
        $this->paj_jenis = '';
        $this->paj_no_billing = '';
        $this->paj_ntpn = '';
        $this->paj_ntb = '';
        $this->paj_nominal = '';
        $this->js("$('#pajakModal').modal('show');");
    }

    public function closePajakForm()
    {
        $this->js("$('#pajakModal').modal('hide');");
    }

    public function storePajak()
    {
        $this->validate([
            'paj_jenis' => 'required|string',
            'paj_no_billing' => 'required|string',
            'paj_nominal' => 'required|numeric|min:0',
        ]);

        // Cek duplikat jenis pajak
        $exists = PajakTu::where('belanja_tu_id', $this->belanjaTuId)
            ->where('jenis_pajak', $this->paj_jenis)->first();
        if ($exists) {
            $this->js(<<<'JS'
                const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 2000, timerProgressBar: true });
                Toast.fire({ icon: "warning", title: "Jenis pajak ini sudah ada" });
            JS);
            return;
        }

        PajakTu::create([
            'belanja_tu_id' => $this->belanjaTuId,
            'jenis_pajak' => $this->paj_jenis,
            'no_billing' => $this->paj_no_billing,
            'ntpn' => $this->paj_ntpn ?: null,
            'ntb' => $this->paj_ntb ?: null,
            'nominal' => $this->paj_nominal,
        ]);

        $this->paj_jenis = '';
        $this->paj_no_billing = '';
        $this->paj_ntpn = '';
        $this->paj_ntb = '';
        $this->paj_nominal = '';
        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: "success", title: "Pajak berhasil disimpan" });
            $('#pajakModal').modal('hide');
        JS);
    }

    public function deletePajak($id)
    {
        PajakTu::destroy($id);
        $this->js(<<<'JS'
            const Toast = Swal.mixin({ toast: true, position: "top-end", showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: "error", title: "Pajak dihapus" });
        JS);
    }
}
