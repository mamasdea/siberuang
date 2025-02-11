        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="col-sm-12">
                        <ol class="breadcrumb float-sm-right small">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">{{ $title }}</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content mt-3">
                <div class="container-fluid">
                    {{ $slot }}
                    {{-- @yield('content') --}}
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
