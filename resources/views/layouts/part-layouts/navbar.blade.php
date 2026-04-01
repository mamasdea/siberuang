<!-- Navbar -->
<nav class="main-header navbar navbar-expand" style="background: #fff; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); min-height: 56px;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: #475569; font-size: 18px; padding: 8px 12px; border-radius: 8px; transition: all 0.2s;">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-flex align-items-center ml-2">
            <div style="display: flex; align-items: center; background: linear-gradient(135deg, #ede9fe, #e0e7ff); padding: 6px 16px; border-radius: 8px;">
                <i class="fas fa-calendar-alt" style="color: #6366f1; margin-right: 8px; font-size: 13px;"></i>
                <span style="font-weight: 700; color: #4338ca; font-size: 13.5px;">
                    TA {{ session('tahun_anggaran', date('Y')) }}
                </span>
            </div>
        </li>
    </ul>

    <!-- Right navbar -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 8px; padding: 6px 14px; font-size: 12.5px; font-weight: 600; transition: all 0.2s;">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </form>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
