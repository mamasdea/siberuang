<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>

    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <div class="flex justify-center">
            <form method="GET" action="{{ route('set-tahun-anggaran') }}">
                <label for="tahun" class="">Tahun Anggaran:</label>
                <select name="tahun" id="tahun" class="border rounded" onchange="this.form.submit()">
                    @foreach (range(date('Y') - 3, date('Y') + 1) as $tahun)
                        <option value="{{ $tahun }}"
                            {{ session('tahun_anggaran', date('Y')) == $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>


    </ul>
</nav>
<!-- /.navbar -->
