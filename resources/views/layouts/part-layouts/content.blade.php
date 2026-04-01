        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="background: #f1f5f9;">
            <!-- Content Header (Page header) -->
            <div class="content-header" style="padding: 16px 24px 8px;">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 style="margin: 0; font-weight: 700; color: #1e293b; font-size: 1.15rem;">{{ $title }}</h4>
                        </div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb" style="margin: 0; background: transparent; padding: 0; font-size: 12.5px;">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('dashboard') }}" style="color: #6366f1; text-decoration: none; font-weight: 500;">
                                        <i class="fas fa-home" style="font-size: 11px; margin-right: 3px;"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" style="color: #94a3b8; font-weight: 500;">{{ $title }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content" style="padding: 0 12px 20px;">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
