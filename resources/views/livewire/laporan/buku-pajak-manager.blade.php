@push('css')
    <x-styles.modern-ui />
@endpush

<div>
    <div class="modern-card fade-in-up mb-4">
        <div class="card-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                     <h3 class="page-title">Laporan Buku Pajak</h3>
                     <p class="page-subtitle mb-0">Kelola dan lihat laporan pajak sesuai kategori</p>
                </div>
                <div>
                    <select wire:model.live="jenisLaporan" class="form-control custom-select-modern font-weight-bold shadow-sm" style="min-width: 250px; border-radius: 8px;">
                        <option value="all">📁 Buku Pajak All</option>
                        <option value="giro">🏦 Buku Pajak Giro</option>
                        <option value="kkpd">💳 Buku Pajak KKPD</option>
                        <option value="ls">📑 Buku Pajak LS</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Component Loading -->
    <div class="fade-in-up" style="animation-delay: 0.1s;">
        @if ($jenisLaporan == 'all')
            <livewire:laporan.buku-pajak-all />
        @elseif ($jenisLaporan == 'giro')
            <livewire:laporan.buku-pajak-giro />
        @elseif ($jenisLaporan == 'kkpd')
            <livewire:laporan.buku-pajak-kkpd />
        @elseif ($jenisLaporan == 'ls')
            <livewire:laporan.buku-pajak-ls />
        @endif
    </div>
</div>
