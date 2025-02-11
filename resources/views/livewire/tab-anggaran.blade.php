<div>
    <style>
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h4 class="card-title">Data Rincian Belanja Rencana Kerja Anggaran Perangkat Daerah</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-pills nav-fill" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'Program' ? 'active' : '' }}" id="program"
                                        href="#" wire:click="$set('activeTab', 'Program')">Program</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'Kegiatan' ? 'active' : '' }}" id="kegiatan"
                                        href="#" wire:click="$set('activeTab', 'Kegiatan')"
                                        {{ $activeTab === 'Program' ? '' : 'disabled' }}>Kegiatan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'Sub Kegiatan' ? 'active' : '' }}"
                                        id="subKegiatan" href="#" wire:click="$set('activeTab', 'Sub Kegiatan')"
                                        {{ $activeTab === 'Kegiatan' ? '' : 'disabled' }}>Sub Kegiatan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'Rincian Belanja' ? 'active' : '' }}"
                                        id="rincianBelanja" href="#"
                                        wire:click="$set('activeTab', 'Rincian Belanja')"
                                        {{ $activeTab === 'Sub Kegiatan' ? '' : 'disabled' }}>Rincian Belanja</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane @if ($activeTab === 'program') active @endif" id="program"
                                    role="tabpanel">
                                    <livewire:anggaran.program-kegiatan-form />
                                    <p>Data Program...</p>
                                </div>
                                <div class="tab-pane @if ($activeTab === 'kegiatan') active @endif" id="kegiatan"
                                    role="tabpanel">
                                    <livewire:anggaran.kegiatan-component :program_id="$programId" />
                                    <p>Data Kegiatan...</p>
                                </div>
                                <div class="tab-pane @if ($activeTab === 'subkegiatan') active @endif" id="subkegiatan"
                                    role="tabpanel">
                                    <livewire:anggaran.sub-kegiatan-component :kegiatan_id="$kegiatanId" />
                                    <p>Data Sub Kegiatan...</p>
                                </div>
                                <div class="tab-pane @if ($activeTab === 'belanja') active @endif" id="belanja"
                                    role="tabpanel">
                                    <livewire:anggaran.r-k-a-component :sub_kegiatan_id="$subKegiatanId" :kegiatan_id="$kegiatanId"
                                        :program_id="$programId" />
                                    <p>Data Rincian Belanja...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-right">
                            <button class="btn btn-primary" wire:click="nextTab">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
