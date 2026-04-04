@php
    use App\Models\MenuPermission;
    $role = Auth::user()->role ?? 'user';
@endphp

<!-- Main Sidebar Container -->
<aside class="main-sidebar elevation-4" style="background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);">
    <!-- Brand Logo -->
    <a href="{{ url('dashboard') }}" class="brand-link" style="border-bottom: 1px solid rgba(255,255,255,0.08); padding: 14px 16px;">
        <img src="{{ asset('assets/logo/beruang.png') }}" alt="Logo" class="brand-image" style="opacity: .9; margin-top: 2px;">
        <span class="brand-text font-weight-bold" style="color: #f8fafc; font-size: 1.1rem; letter-spacing: 0.5px;">SiberUANG</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
            <div class="image">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 15px;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'G', 0, 1)) }}
                </div>
            </div>
            <div class="info" style="padding-left: 4px;">
                <a href="#" class="d-block" style="color: #e2e8f0; font-weight: 600; font-size: 13px; line-height: 1.3;">{{ Auth::user()->name ?? 'Guest' }}</a>
                <span style="color: #94a3b8; font-size: 11px;">{{ ucfirst(Auth::user()->role ?? 'user') }}</span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-1">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="true">

                {{-- Label Section --}}
                <li class="nav-header" style="color: #64748b; font-size: 10px; letter-spacing: 1.5px; padding: 12px 16px 6px; text-transform: uppercase;">Menu Utama</li>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ url('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-th-large" style="font-size: 14px;"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @if (MenuPermission::hasAccess($role, 'anggaran'))
                    <li class="nav-item">
                        <a href="{{ url('program') }}" class="nav-link {{ request()->is('program') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-coins" style="font-size: 14px;"></i>
                            <p>Anggaran</p>
                        </a>
                    </li>
                @endif

                @if (MenuPermission::hasAccess($role, 'uang-persediaan'))
                    <li class="nav-item has-treeview {{ request()->is('up-giro', 'up-kkpd') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-wallet" style="font-size: 14px;"></i>
                            <p>Uang Persediaan <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('up-giro') }}" class="nav-link {{ request()->is('up-giro') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>UP/GU</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('up-kkpd') }}" class="nav-link {{ request()->is('up-kkpd') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>KKPD</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Label Transaksi --}}
                <li class="nav-header" style="color: #64748b; font-size: 10px; letter-spacing: 1.5px; padding: 12px 16px 6px; text-transform: uppercase;">Transaksi</li>

                @if (MenuPermission::hasAccess($role, 'belanja') || MenuPermission::hasAccess($role, 'belanja-tu'))
                    <li class="nav-item has-treeview {{ request()->is('belanja', 'belanja-kkpd', 'belanja-tu*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-shopping-bag" style="font-size: 14px;"></i>
                            <p>Belanja <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if (MenuPermission::hasAccess($role, 'belanja'))
                                <li class="nav-item">
                                    <a href="{{ url('belanja') }}" class="nav-link {{ request()->is('belanja') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>GU</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('belanja-kkpd') }}" class="nav-link {{ request()->is('belanja-kkpd') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>KKPD</p>
                                    </a>
                                </li>
                            @endif
                            @if (MenuPermission::hasAccess($role, 'belanja-tu'))
                                <li class="nav-item">
                                    <a href="{{ url('belanja-tu') }}" class="nav-link {{ request()->is('belanja-tu*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>TU</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (MenuPermission::hasAccess($role, 'spj') || MenuPermission::hasAccess($role, 'spj-tu'))
                    <li class="nav-item has-treeview {{ request()->is('spj*', 'tu-nihil*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-check" style="font-size: 14px;"></i>
                            <p>SPJ <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if (MenuPermission::hasAccess($role, 'spj'))
                                <li class="nav-item">
                                    <a href="{{ url('spj-gu') }}" class="nav-link {{ request()->is('spj-gu') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>SPJ GU</p>
                                    </a>
                                </li>
                            @endif
                            @if (MenuPermission::hasAccess($role, 'spj-tu'))
                                <li class="nav-item">
                                    <a href="{{ url('spj-tu') }}" class="nav-link {{ request()->is('spj-tu*', 'tu-nihil*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>SPJ TU</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (MenuPermission::hasAccess($role, 'spp-spm-up') || MenuPermission::hasAccess($role, 'spp-spm-gu') || MenuPermission::hasAccess($role, 'spp-spm-tu') || MenuPermission::hasAccess($role, 'spp-spm-ls'))
                    <li class="nav-item has-treeview {{ request()->is('spp-spm*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-invoice-dollar" style="font-size: 14px;"></i>
                            <p>SPP-SPM <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if (MenuPermission::hasAccess($role, 'spp-spm-up'))
                                <li class="nav-item">
                                    <a href="{{ url('spp-spm-up') }}" class="nav-link {{ request()->is('spp-spm-up') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>UP</p>
                                    </a>
                                </li>
                            @endif
                            @if (MenuPermission::hasAccess($role, 'spp-spm-gu'))
                                <li class="nav-item">
                                    <a href="{{ url('spp-spm-gu') }}" class="nav-link {{ request()->is('spp-spm-gu') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>GU</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('gu-nihil') }}" class="nav-link {{ request()->is('gu-nihil*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>GU Nihil</p>
                                    </a>
                                </li>
                            @endif
                            @if (MenuPermission::hasAccess($role, 'spp-spm-tu'))
                                <li class="nav-item">
                                    <a href="{{ url('spp-spm-tu') }}" class="nav-link {{ request()->is('spp-spm-tu') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>TU</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('tu-nihil') }}" class="nav-link {{ request()->is('tu-nihil*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>TU Nihil</p>
                                    </a>
                                </li>
                            @endif
                            @if (MenuPermission::hasAccess($role, 'spp-spm-ls'))
                                <li class="nav-item">
                                    <a href="{{ url('spp-spm-ls') }}" class="nav-link {{ request()->is('spp-spm-ls') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                        <p>LS</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (MenuPermission::hasAccess($role, 'kontrak'))
                    <li class="nav-item">
                        <a href="{{ url('kontrak') }}" class="nav-link {{ request()->is('kontrak') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-handshake" style="font-size: 14px;"></i>
                            <p>Kontrak</p>
                        </a>
                    </li>
                @endif

                {{-- Label Laporan --}}
                <li class="nav-header" style="color: #64748b; font-size: 10px; letter-spacing: 1.5px; padding: 12px 16px 6px; text-transform: uppercase;">Pelaporan</li>

                @if (MenuPermission::hasAccess($role, 'laporan'))
                    <li class="nav-item has-treeview {{ request()->is('laporan*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-chart-bar" style="font-size: 14px;"></i>
                            <p>Laporan <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('laporan-page') }}" class="nav-link {{ request()->is('laporan-page') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>NPD</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('laporan-bkuall') }}" class="nav-link {{ request()->is('laporan-bkuall') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>BKU</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('laporan-bukupajak') }}" class="nav-link {{ request()->is('laporan-bukupajak') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Laporan Pajak</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('laporan.realisasi') }}" class="nav-link {{ request()->is('laporan-realisasi') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Laporan Realisasi</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('laporan.rincian-obyek') }}" class="nav-link {{ request()->is('laporan-rincian-obyek') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Rincian Obyek</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (MenuPermission::hasAccess($role, 'master'))
                    {{-- Label Pengaturan --}}
                    <li class="nav-header" style="color: #64748b; font-size: 10px; letter-spacing: 1.5px; padding: 12px 16px 6px; text-transform: uppercase;">Pengaturan</li>

                    <li class="nav-item has-treeview {{ request()->is('rekening-belanja', 'penerima', 'pengelola-keuangan', 'user-management', 'menu-access') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-sliders-h" style="font-size: 14px;"></i>
                            <p>Master <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('rekening-belanja') }}" class="nav-link {{ request()->is('rekening-belanja') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Rekening Belanja</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('penerima') }}" class="nav-link {{ request()->is('penerima') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Penerima/Rekanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('pengelola-keuangan') }}" class="nav-link {{ request()->is('pengelola-keuangan') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Pengelola Keuangan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('user-management') }}" class="nav-link {{ request()->is('user-management') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>User Management</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('menu-access') }}" class="nav-link {{ request()->is('menu-access') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-minus" style="font-size: 8px;"></i>
                                    <p>Pengaturan Akses</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>

<style>
    /* Modern Sidebar Styling */
    .main-sidebar {
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    }
    .main-sidebar .nav-sidebar .nav-link {
        color: #cbd5e1;
        border-radius: 8px;
        margin: 2px 10px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(99, 102, 241, 0.12);
        color: #e2e8f0;
    }
    .main-sidebar .nav-sidebar .nav-link.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        font-weight: 600;
    }
    .main-sidebar .nav-sidebar .nav-link.active .nav-icon {
        color: #fff !important;
    }
    .main-sidebar .nav-sidebar .nav-link .nav-icon {
        color: #94a3b8;
        transition: color 0.2s ease;
        width: 22px;
        text-align: center;
    }
    .main-sidebar .nav-sidebar .nav-link:hover .nav-icon {
        color: #a5b4fc;
    }
    .main-sidebar .nav-sidebar .nav-link > .right,
    .main-sidebar .nav-sidebar .nav-link > p > .right {
        color: #64748b;
        transition: transform 0.3s ease;
    }

    /* Sub menu indent & styling */
    .main-sidebar .nav-sidebar .nav-treeview {
        padding-left: 8px;
    }
    .main-sidebar .nav-sidebar .nav-treeview .nav-link {
        font-size: 13px;
        padding: 8px 14px;
        color: #94a3b8;
        margin: 1px 10px;
        border-radius: 6px;
    }
    .main-sidebar .nav-sidebar .nav-treeview .nav-link:hover {
        color: #e2e8f0;
        background: rgba(255, 255, 255, 0.05);
    }
    .main-sidebar .nav-sidebar .nav-treeview .nav-link.active {
        background: rgba(99, 102, 241, 0.2) !important;
        color: #a5b4fc !important;
        box-shadow: none;
        font-weight: 600;
    }

    /* Nested sub-sub menu (BKU) */
    .main-sidebar .nav-sidebar .nav-treeview .nav-treeview {
        padding-left: 10px;
    }
    .main-sidebar .nav-sidebar .nav-treeview .nav-treeview .nav-link {
        font-size: 12.5px;
        padding: 7px 14px;
    }

    /* Menu open state */
    .main-sidebar .nav-sidebar .nav-item.menu-open > .nav-link {
        background: rgba(255, 255, 255, 0.04);
        color: #e2e8f0;
    }

    /* Section headers */
    .main-sidebar .nav-header {
        padding: 12px 16px 6px !important;
    }

    /* Brand link hover */
    .main-sidebar .brand-link:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    /* Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }
    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.2);
    }
</style>
