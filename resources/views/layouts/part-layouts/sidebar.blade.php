<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('dashboard') }}" class="brand-link">
        <img src="assets/logo/beruang.png" alt="AdminLTE Logo" class="brand-image">
        <span class="brand-text font-weight-light">SiberUANG</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://diskominfo.wonosobokab.go.id/uploads/kominfo.png" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Hi, {{ Auth::user()->name ?? 'Guest' }}</a>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm btn-block">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="true">
                <!-- Dashboard Menu -->
                <li class="nav-item">
                    <a href="{{ url('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @if (Auth::user()->role == 'admin')
                    <!-- Anggaran Menu -->
                    <li class="nav-item">
                        <a href="{{ url('program') }}" class="nav-link {{ request()->is('program') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-dollar-sign"></i>
                            <p>Anggaran</p>
                        </a>
                    </li>

                    <!-- Uang Persediaan -->
                    <li class="nav-item has-treeview {{ request()->is('up-giro', 'up-kkpd') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="fas fa-money-check-alt nav-icon"></i>
                            <p>
                                Uang Persediaan
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('up-giro') }}"
                                    class="nav-link {{ request()->is('up-giro') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i>
                                    <p>UP/GU</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('up-kkpd') }}"
                                    class="nav-link {{ request()->is('up-kkpd') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i>
                                    <p>KKPD</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Belanja Menu -->
                <li class="nav-item has-treeview {{ request()->is('belanja*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>
                            Belanja
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('belanja') }}"
                                class="nav-link {{ request()->is('belanja') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>GU</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('belanja-kkpd') }}"
                                class="nav-link {{ request()->is('belanja-kkpd') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>KPPD</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('belanja_ls') }}"
                                class="nav-link {{ request()->is('belanja_ls') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>LS</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Laporan Menu -->
                <li class="nav-item has-treeview {{ request()->is('laporan*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Laporan
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('laporan-page') }}"
                                class="nav-link {{ request()->is('laporan-page') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>NPD</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('laporan-bkugiro') }}"
                                class="nav-link {{ request()->is('laporan-bkugiro') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>BKU GU GIRO</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('laporan-bkukkpd') }}"
                                class="nav-link {{ request()->is('laporan-bkukkpd') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>BKU GU KKPD</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.realisasi') }}" class="nav-link">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Laporan Realisasi</p>
                            </a>
                        </li>
                    </ul>
                </li>

                @if (Auth::user()->role == 'admin')
                    <!-- Master Menu -->
                    <li
                        class="nav-item has-treeview {{ request()->is('rekening-belanja', 'penerima', 'pengelola-keuangan', 'user-management') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Master
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('rekening-belanja') }}"
                                    class="nav-link {{ request()->is('rekening-belanja') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i>
                                    <p>Rekening Belanja</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('penerima') }}"
                                    class="nav-link {{ request()->is('penerima') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i>
                                    <p>Penerima/Rekanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('pengelola-keuangan') }}"
                                    class="nav-link {{ request()->is('pengelola-keuangan') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i>
                                    <p>Pengelola Keuangan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('user-management') }}"
                                    class="nav-link {{ request()->is('user-management') ? 'active' : '' }}">
                                    <i class="far fa-dot-circle nav-icon"></i>
                                    <p>User Management</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
