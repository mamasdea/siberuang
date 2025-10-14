<?php

namespace App\Livewire\Kontrak;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\Kontrak;
use App\Models\KontrakRealisasi;
use App\Models\RincianRealisasiKontrak;
use App\Models\BeritaAcara;

#[Title('Realisasi Kontrak')]
class RealisasiManage extends Component
{
    use WithPagination;

    public Kontrak $kontrak;
    public int $paginate = 10;

    // ========= Form Realisasi =========
    public ?int $realisasi_id = null;
    public string $realisasi_tipe = 'sekaligus'; // termin | sekaligus
    public ?int $realisasi_termin_ke = null;
    public string $realisasi_tanggal = '';

    // Tambahan: periode & progres fisik
    public ?string $periode = null;       // nullable
    public ?float  $progres_fisik = null; // 0..100, nullable (sekaligus -> 100)

    // ========= Berita Acara (opsional) =========
    public bool $ba_pemeriksaan = false, $ba_serah_terima = false, $ba_penerimaan = false, $ba_administratif = false, $ba_pembayaran = false, $ba_pekerjaan = false;
    public ?string $ba_pemeriksaan_nomor = null, $ba_pemeriksaan_tanggal = null;
    public ?string $ba_serah_terima_nomor = null, $ba_serah_terima_tanggal = null;
    public ?string $ba_pekerjaan_nomor = null, $ba_pekerjaan_tanggal = null;
    public ?string $ba_penerimaan_nomor = null, $ba_penerimaan_tanggal = null;
    public ?string $ba_administratif_nomor = null, $ba_administratif_tanggal = null;
    public ?string $ba_pembayaran_nomor = null, $ba_pembayaran_tanggal = null;

    /**
     * itemInputs untuk TERMIN:
     * [
     *   ['rincian_kontrak_id'=>..,'nama_barang'=>..,'satuan'=>..,'harga'=>..,
     *    'qty_kontrak'=>..,'qty_teralisasi'=>..,'qty_sisa'=>..,
     *    'qty_input'=>..,'total_line'=>..],
     *   ...
     * ]
     */
    public array $itemInputs = [];

    public function mount(Kontrak $kontrak): void
    {
        // /kontrak/{kontrak}/realisasi  (Route Model Binding)
        $this->kontrak = $kontrak->loadMissing('rincians');
    }

    /* ================= Helpers ================= */

    protected function roman(int $n): string
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $r = '';
        foreach ($map as $s => $v) {
            while ($n >= $v) {
                $r .= $s;
                $n -= $v;
            }
        }
        return $r ?: 'I';
    }

    protected function nextTerminNumber(): int
    {
        $max = (int) $this->kontrak->realisasis()->max('termin_ke');
        return $max + 1;
    }

    public function getTerminLabelProperty(): string
    {
        $n = $this->realisasi_termin_ke ?: $this->nextTerminNumber();
        return 'Termin ' . $this->roman(max(1, (int)$n));
    }

    /** Akumulasi qty realisasi per rincian_kontrak (exclude realisasi tertentu saat edit). */
    protected function mapQtyRealisasiPerItem(?int $excludeRealisasiId = null): array
    {
        $q = RincianRealisasiKontrak::query()
            ->whereHas('realisasi', fn($r) => $r->where('kontrak_id', $this->kontrak->id));

        if ($excludeRealisasiId) {
            $q->where('kontrak_realisasi_id', '!=', $excludeRealisasiId);
        }

        return $q->selectRaw('rincian_kontrak_id, SUM(kuantitas) as qty')
            ->groupBy('rincian_kontrak_id')
            ->pluck('qty', 'rincian_kontrak_id')
            ->map(fn($v) => (float) $v)
            ->toArray();
    }

    /** Ambil item dari termin sebelumnya yang memiliki nilai (nominal > 0). */
    protected function getPreviousTerminItems(int $currentRealisasiId): \Illuminate\Support\Collection
    {
        $previousTermin = KontrakRealisasi::where('kontrak_id', $this->kontrak->id)
            ->where('id', '!=', $currentRealisasiId)
            ->where('nominal', '>', 0)
            ->where('tipe', 'termin')
            ->orderBy('tanggal', 'desc')
            ->orderBy('termin_ke', 'desc')
            ->first();

        if (!$previousTermin) {
            return collect();
        }

        return $previousTermin->items->keyBy('rincian_kontrak_id')
            ->map(fn($item) => (float) $item->kuantitas);
    }

    /** Siapkan itemInputs untuk TERMIN (baik create maupun edit). */
    protected function buildItemInputsForTermin(?int $editingRealisasiId = null): void
    {
        $accum = $this->mapQtyRealisasiPerItem($editingRealisasiId);

        // Data realisasi yang sedang diedit
        $currentItems = collect();
        $currentRealisasi = null;
        if ($editingRealisasiId) {
            $currentRealisasi = KontrakRealisasi::with('items')->find($editingRealisasiId);
            $currentItems = $currentRealisasi->items->keyBy('rincian_kontrak_id');
        }

        // Jika editing & nominal 0, prefill dari termin sebelumnya yang punya nilai
        $previousTerminItems = collect();
        if ($editingRealisasiId && $currentRealisasi && $currentRealisasi->nominal <= 0) {
            $previousTerminItems = $this->getPreviousTerminItems($editingRealisasiId);
        }

        $this->itemInputs = [];

        foreach ($this->kontrak->rincians as $rc) {
            $qtyTerealisasiLain = (float) ($accum[$rc->id] ?? 0.0);

            $qtyRealisasiIni = 0.0;
            if ($currentItems->has($rc->id)) {
                $qtyRealisasiIni = (float) $currentItems[$rc->id]->kuantitas;
            }
            if ($editingRealisasiId && $currentRealisasi && $currentRealisasi->nominal <= 0 && $previousTerminItems->has($rc->id)) {
                $qtyRealisasiIni = (float) $previousTerminItems[$rc->id];
            }

            $qtySisa = max(0, (float) $rc->kuantitas - $qtyTerealisasiLain);

            $this->itemInputs[] = [
                'rincian_kontrak_id' => $rc->id,
                'nama_barang'        => $rc->nama_barang,
                'satuan'             => $rc->satuan,
                'harga'              => (float) $rc->harga,
                'qty_kontrak'        => (float) $rc->kuantitas,
                'qty_teralisasi'     => $qtyTerealisasiLain, // dari realisasi lain
                'qty_sisa'           => $qtySisa,
                'qty_input'          => $qtyRealisasiIni,    // prefill saat edit
                'total_line'         => $qtyRealisasiIni * (float) $rc->harga,
            ];
        }
    }

    /** Recalc total per baris ketika qty_input berubah. */
    public function updatedItemInputs(): void
    {
        foreach ($this->itemInputs as &$it) {
            $qty = (float) ($it['qty_input'] ?? 0);
            $it['total_line'] = $qty * (float) $it['harga'];
        }
        unset($it);
    }

    /* ================= Rules ================= */

    protected function rules(): array
    {
        $rules = [
            'realisasi_tipe'      => ['required', 'in:termin,sekaligus'],
            'realisasi_tanggal'   => ['required', 'date'],
            'realisasi_termin_ke' => ['nullable', 'integer', 'min:1'],

            'periode'             => ['nullable', 'string', 'max:50'],
            'progres_fisik'       => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        foreach (['pemeriksaan', 'serah_terima', 'pekerjaan', 'penerimaan', 'administratif', 'pembayaran'] as $jenis) {
            if ($this->{"ba_{$jenis}"} === true) {
                $rules["ba_{$jenis}_nomor"]   = ['required', 'string', 'max:255'];
                $rules["ba_{$jenis}_tanggal"] = ['required', 'date'];
            }
        }

        if ($this->realisasi_tipe === 'termin') {
            $rules['itemInputs'] = ['required', 'array', 'min:1'];
        }

        return $rules;
    }

    /* ================= UI Events ================= */

    public function updatedRealisasiTipe(string $value): void
    {
        $hasAny = $this->kontrak->realisasis()
            ->when($this->realisasi_id, fn($q) => $q->where('id', '!=', $this->realisasi_id))
            ->exists();

        if ($value === 'sekaligus') {
            if ($hasAny) {
                $this->realisasi_tipe = 'termin';
                $this->realisasi_termin_ke = $this->nextTerminNumber();
                $this->buildItemInputsForTermin($this->realisasi_id);
                $this->js(<<<'JS'
                    Swal.fire({icon:'error', title:'Tidak diizinkan',
                               text:'Kontrak ini sudah memiliki realisasi/termin. Pilih TERMIN berikutnya.'});
                JS);
                return;
            }
            // auto untuk sekaligus
            $this->realisasi_termin_ke = null;
            $this->periode = null;
            $this->progres_fisik = 100.0;
            $this->itemInputs = [];
        } else {
            $this->realisasi_termin_ke = $this->nextTerminNumber();
            if ($this->progres_fisik === 100.0) $this->progres_fisik = null; // kembalikan ke null
            $this->buildItemInputsForTermin($this->realisasi_id);
        }
    }

    public function createOpen(): void
    {
        // Cek apakah seluruh qty sudah terealisasi
        $accum = $this->mapQtyRealisasiPerItem(null);
        $allDone = true;
        foreach ($this->kontrak->rincians as $rc) {
            $sisa = max(0, (float)$rc->kuantitas - (float)($accum[$rc->id] ?? 0));
            if ($sisa > 0) {
                $allDone = false;
                break;
            }
        }
        if ($allDone) {
            $this->js(<<<'JS'
                Swal.fire({icon:'info', title:'Kontrak sudah terealisasi penuh',
                           text:'Seluruh kuantitas rincian telah terealisasi.'});
            JS);
            return;
        }

        $this->resetForm();

        if ($this->kontrak->realisasis()->exists()) {
            $this->realisasi_tipe = 'termin';
            $this->realisasi_termin_ke = $this->nextTerminNumber();
            $this->buildItemInputsForTermin(null);
        } else {
            $this->realisasi_tipe = 'sekaligus';
            $this->progres_fisik = 100.0;
        }

        $this->js("$('#realisasiModal').modal('show');");
    }

    public function edit(int $id): void
    {
        $r = KontrakRealisasi::with(['items', 'beritaAcaras'])->findOrFail($id);

        $this->realisasi_id        = $r->id;
        $this->realisasi_tipe      = $r->tipe;
        $this->realisasi_termin_ke = $r->termin_ke;
        $this->realisasi_tanggal   = optional($r->tanggal)->format('Y-m-d');

        $this->periode       = $r->periode;
        $this->progres_fisik = is_null($r->progres_fisik) ? null : (float)$r->progres_fisik;

        // Reset & set BA
        foreach (['pemeriksaan', 'serah_terima', 'pekerjaan', 'penerimaan', 'administratif', 'pembayaran'] as $j) {
            $this->{"ba_{$j}"} = false;
            $this->{"ba_{$j}_nomor"} = null;
            $this->{"ba_{$j}_tanggal"} = null;
        }
        foreach ($r->beritaAcaras as $ba) {
            $this->{"ba_{$ba->jenis}"} = true;
            $this->{"ba_{$ba->jenis}_nomor"} = $ba->nomor;
            $this->{"ba_{$ba->jenis}_tanggal"} = optional($ba->tanggal)->format('Y-m-d');
        }

        if ($r->tipe === 'termin') {
            $this->buildItemInputsForTermin($r->id);
        } else {
            $this->itemInputs = [];
            if ($this->progres_fisik === null) $this->progres_fisik = 100.0;
        }

        $this->js("$('#realisasiModal').modal('show');");
    }

    /* ================= Save / Delete ================= */

    protected function validateTerminQuantities(): bool
    {
        $hasQty = false;

        foreach ($this->itemInputs as $row) {
            $qtyInput = (float) ($row['qty_input'] ?? 0);
            $qtySisa  = (float) $row['qty_sisa'];

            if ($qtyInput > 0) $hasQty = true;

            if ($qtyInput > $qtySisa) {
                $this->addError(
                    'itemInputs',
                    "Qty '{$row['nama_barang']}' sebesar " . number_format($qtyInput, 2, ',', '.') .
                        " melebihi sisa yang tersedia (" . number_format($qtySisa, 2, ',', '.') . ")."
                );
                return false;
            }
        }

        if (!$hasQty) {
            $this->addError('itemInputs', 'Minimal satu baris harus memiliki qty > 0.');
            return false;
        }

        return true;
    }

    public function save(): void
    {
        $this->validate();

        // Sekaligus hanya boleh jika belum ada realisasi lain
        if ($this->realisasi_tipe === 'sekaligus') {
            $hasOther = $this->kontrak->realisasis()
                ->when($this->realisasi_id, fn($q) => $q->where('id', '!=', $this->realisasi_id))
                ->exists();
            if ($hasOther) {
                $this->js(<<<'JS'
                    Swal.fire({icon:'error', title:'Tidak diizinkan',
                               text:'Sudah ada realisasi/termin. Gunakan TERMIN berikutnya.'});
                JS);
                return;
            }
            // enforce auto values
            $this->progres_fisik = 100.0;
            if ($this->periode === '') $this->periode = null;
        } else {
            if (!$this->validateTerminQuantities()) return;
        }

        $payload = [
            'kontrak_id'    => $this->kontrak->id,
            'tipe'          => $this->realisasi_tipe,
            'termin_ke'     => $this->realisasi_tipe === 'termin'
                ? ($this->realisasi_termin_ke ?: $this->nextTerminNumber())
                : null,
            'tanggal'       => $this->realisasi_tanggal,
            'periode'       => $this->periode,
            'progres_fisik' => $this->progres_fisik,
            'nominal'       => 0, // dihitung dari items
        ];

        if ($this->realisasi_id) {
            $real = KontrakRealisasi::findOrFail($this->realisasi_id);
            $real->update($payload);
            // reset items & BA saat update
            $real->items()->delete();
            $real->beritaAcaras()->delete();
        } else {
            $real = KontrakRealisasi::create($payload);
        }

        if ($this->realisasi_tipe === 'sekaligus') {
            // copy semua rincian kontrak (qty penuh)
            $this->kontrak->loadMissing('rincians');
            foreach ($this->kontrak->rincians as $rc) {
                $harga = (float) $rc->harga;
                $kuantitas = (float) $rc->kuantitas;
                $real->items()->create([
                    'rincian_kontrak_id'   => $rc->id,
                    'nama_barang'          => $rc->nama_barang,
                    'kuantitas'            => $kuantitas,
                    'satuan'               => $rc->satuan,
                    'harga'                => $harga,
                    'total_harga'          => $kuantitas * $harga,
                ]);
            }
        } else {
            // buat item hanya yang qty_input > 0
            foreach ($this->itemInputs as $row) {
                $qty = (float) ($row['qty_input'] ?? 0);
                if ($qty <= 0) continue;

                $harga = (float) $row['harga'];
                $real->items()->create([
                    'rincian_kontrak_id'   => $row['rincian_kontrak_id'],
                    'nama_barang'          => $row['nama_barang'],
                    'kuantitas'            => $qty,
                    'satuan'               => $row['satuan'],
                    'harga'                => $harga, // harga & satuan terkunci
                    'total_harga'          => $qty * $harga,
                ]);
            }
        }

        // hitung nominal dari items
        $real->recalcNominal();

        // simpan BA (opsional)
        foreach (['pemeriksaan', 'serah_terima', 'pekerjaan', 'penerimaan', 'administratif', 'pembayaran'] as $jenis) {
            $flag = "ba_{$jenis}";
            $nom  = "ba_{$jenis}_nomor";
            $tgl  = "ba_{$jenis}_tanggal";
            if ($this->$flag && $this->$nom && $this->$tgl) {
                BeritaAcara::create([
                    'kontrak_realisasi_id' => $real->id,
                    'jenis'   => $jenis,
                    'nomor'   => $this->$nom,
                    'tanggal' => $this->$tgl,
                ]);
            }
        }

        $this->resetForm();

        $this->js(<<<'JS'
            $('#realisasiModal').modal('hide');
            const Toast = Swal.mixin({toast:true,position:"top-end",showConfirmButton:false,timer:2000,timerProgressBar:true});
            Toast.fire({icon:"success", title:"Realisasi tersimpan"});
        JS);
    }

    public function deleteConfirm(int $id): void
    {
        $this->realisasi_id = $id;
        $this->js(<<<'JS'
            Swal.fire({
                title:'Hapus realisasi?', text:'Tindakan ini tidak dapat dibatalkan.',
                icon:'warning', showCancelButton:true,
                confirmButtonColor:'#3085d6', cancelButtonColor:'#d33',
                confirmButtonText:'Ya, hapus'
            }).then((r)=>{ if(r.isConfirmed){ $wire.delete() }});
        JS);
    }

    public function delete(): void
    {
        KontrakRealisasi::destroy($this->realisasi_id);
        $this->realisasi_id = null;
        $this->js(<<<'JS'
            const Toast = Swal.mixin({toast:true,position:"top-end",showConfirmButton:false,timer:2000,timerProgressBar:true});
            Toast.fire({icon:"error", title:"Realisasi dihapus"});
        JS);
    }

    /* ============ CETAK BA ============ */

    public function printBA(string $jenis, ?int $realisasiId = null): void
    {
        $allowed = ['pemeriksaan', 'serah_terima', 'pekerjaan', 'penerimaan', 'administratif', 'pembayaran'];
        if (!in_array($jenis, $allowed, true)) {
            $this->js("Swal.fire({icon:'error',title:'Jenis BA tidak dikenal'});");
            return;
        }

        $rid = $realisasiId ?? ($this->realisasi_id ?? 0); // 0 = preview
        $nom = $this->{"ba_{$jenis}_nomor"} ?? null;
        $tgl = $this->{"ba_{$jenis}_tanggal"} ?? null;

        $url = route('realisasi.ba.html', [
            'kontrak'   => $this->kontrak->id,
            'realisasi' => $rid,
            'jenis'     => $jenis,
        ]) . ($nom || $tgl ? ('?preview=1'
            . ($nom ? '&nomor=' . urlencode($nom) : '')
            . ($tgl ? '&tanggal=' . urlencode($tgl) : '')
        ) : '');

        $this->dispatch('open-window', url: $url);
    }

    /* ============ Rendering & Reset ============ */

    public function render()
    {
        $realisasis = KontrakRealisasi::with(['beritaAcaras', 'items'])
            ->where('kontrak_id', $this->kontrak->id)
            ->orderBy('tanggal', 'desc')
            ->paginate($this->paginate);

        $total = (float) $this->kontrak->realisasis()->sum('nominal');
        $sisa  = max(0, (float) $this->kontrak->nilai - $total);

        return view('livewire.kontrak.realisasi-manage', compact('realisasis', 'total', 'sisa'));
    }

    public function updatingPaginate(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'realisasi_id',
            'realisasi_tipe',
            'realisasi_termin_ke',
            'realisasi_tanggal',
            'periode',
            'progres_fisik',
            'ba_pemeriksaan',
            'ba_serah_terima',
            'ba_pekerjaan',
            'ba_penerimaan',
            'ba_administratif',
            'ba_pembayaran',
            'ba_pemeriksaan_nomor',
            'ba_pemeriksaan_tanggal',
            'ba_serah_terima_nomor',
            'ba_serah_terima_tanggal',
            'ba_pekerjaan_nomor',
            'ba_pekerjaan_tanggal',
            'ba_penerimaan_nomor',
            'ba_penerimaan_tanggal',
            'ba_administratif_nomor',
            'ba_administratif_tanggal',
            'ba_pembayaran_nomor',
            'ba_pembayaran_tanggal',
        ]);
        $this->itemInputs = [];
    }
}
