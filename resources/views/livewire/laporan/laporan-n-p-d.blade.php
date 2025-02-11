<div>
    <div class="form-group">
        <label for="sub_kegiatan">Pilih Sub Kegiatan</label>
        <select id="sub_kegiatan" wire:model.live="selectedSubKegiatan" class="form-control">
            <option value="">-- Pilih Sub Kegiatan --</option>
            @foreach ($subKegiatans as $subKegiatan)
                <option value="{{ $subKegiatan->id }}">{{ $subKegiatan->nama }}</option>
            @endforeach
        </select>
    </div>

    <!-- Tampilkan data sub kegiatan yang dipilih -->
    @if ($selectedSubKegiatan)
        <div class="mt-3">
            <h5>Sub Kegiatan Terpilih:</h5>
            @php
                $selectedSub = $subKegiatans->find($selectedSubKegiatan);
            @endphp
            <p><strong>Kode:</strong> {{ $selectedSub->kode }}</p>
            <p><strong>Nama:</strong> {{ $selectedSub->nama }}</p>
        </div>
    @endif

    <button wire:click="exportLaporanNPD" class="btn btn-sm btn-primary mt-3"
        {{ !$selectedSubKegiatan ? 'disabled' : '' }}>
        <i class="fas fa-download"></i> Download Laporan NPD
    </button>
</div>
