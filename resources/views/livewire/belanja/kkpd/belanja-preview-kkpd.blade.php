<div>
    @if ($belanja)
        <div id="printArea" class="container">
            <div class="border border-dark p-3">
                <!-- Header -->
                <div class="row text-center">
                    <div class="col-2">
                        {{-- <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d6/Lambang_Kabupaten_Wonosobo.webp/1200px-Lambang_Kabupaten_Wonosobo.webp.png"
                            alt="Logo" width="80" height="auto"> --}}
                        <img src="{{ asset('assets/logo/siberuang.png') }}" alt="Logo" width="80" height="auto">
                    </div>
                    <div class="col-8">
                        <h5 class="mb-0">PEMERINTAH KABUPATEN WONOSOBO</h5>
                        <h4 class="mb-0"><strong>SURAT BUKTI PENGELUARAN / BELANJA</strong></h4>
                        <strong>Nomor :</strong> TBP - {{ $belanja->no_bukti }} /Diskominfo/2025<br>
                        <strong>Tanggal :</strong>
                        {{ \Carbon\Carbon::parse($belanja->tanggal)->translatedFormat('d F Y') }}


                    </div>
                    {{-- <div class="col-2 text-center border border-dark">
                        <strong>FORMULIR <br> KAS <br> KELUAR</strong>
                    </div> --}}
                </div>

                <hr class="border border-dark">

                <div class="mb-2">
                    <strong>Sub Unit Organisasi :</strong> 2-16.2-20.2-21.1.1.0 Dinas Komunikasi dan Informatika
                </div>

                <hr class="border border-dark">


                <!-- Uang yang diterima -->
                <p>Sudah diterima dari Bendahara Pengeluaran, uang sejumlah
                    <strong>Rp{{ number_format($belanja->nilai, 2, ',', '.') }}</strong> secara Bank (No Rek :
                    <strong>{{ $penerimaan->isNotEmpty() ? $penerimaan[0]->penerima->no_rekening : '-' }}</strong>)
                </p>

                <p><strong>Terbilang:</strong> {{ ucwords(terbilang($belanja->nilai)) }} Rupiah</p>

                <hr class="border border-dark">

                <!-- Detail Pembayaran -->
                <strong>Yaitu untuk pembayaran:</strong>
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 120px;">Program</th>
                        <td>:</td>
                        <td>{{ $program ? $program->kode : '-' }} - {{ $program ? $program->nama : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kegiatan</th>
                        <td>:</td>
                        <td>{{ $kegiatan ? $kegiatan->kode : '-' }} - {{ $kegiatan ? $kegiatan->nama : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Sub Kegiatan</th>
                        <td>:</td>
                        <td>{{ $subKegiatan ? $subKegiatan->kode : '-' }} -
                            {{ $subKegiatan ? $subKegiatan->nama : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kode Rekening</th>
                        <td>:</td>
                        <td>{{ $rka ? $rka->kode_belanja : '-' }} - {{ $rka ? $rka->nama_belanja : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Untuk Keperluan</th>
                        <td>:</td>
                        <td>{{ $belanja->uraian }}</td>
                    </tr>
                </table>


                <hr class="border border-dark">

                <!-- Data Penerima -->
                <strong>Diterima oleh:</strong>
                <div class="table-responsive">
                    @if ($penerimaan->isNotEmpty())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Penerima</th>
                                    <th>No Rekening</th>
                                    <th>Bank</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penerimaan as $p)
                                    <tr>
                                        <td>{{ $p->penerima->nama }}</td>
                                        <td>{{ $p->penerima->no_rekening }}</td>
                                        <td>{{ $p->penerima->bank }}</td>
                                        <td>Rp{{ number_format($p->nominal, 2, ',', '.') }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Tidak ada data penerimaan.</p>
                    @endif
                </div>

                <hr class="border border-dark">

                <!-- Pajak -->
                <strong>Informasi Potongan Pajak:</strong>
                @if ($pajak->isNotEmpty())
                    <table class="table table-bordered ">
                        <thead>
                            <tr>
                                <th>Jenis Pajak</th>
                                <th>No Billing</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pajak as $p)
                                <tr>
                                    <td>{{ $p->jenis_pajak }}</td>
                                    <td>{{ $p->no_billing }}</td>
                                    <td>Rp{{ number_format($p->nominal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Tidak ada potongan pajak.</p>
                @endif

                <hr class="border border-dark">


            </div>
        </div>
    @else
        <p class="text-center">Belanja tidak ditemukan.</p>
    @endif
    <!-- Tombol Print -->
    <div class="text-center no-print mt-3">
        <button type="button" class="btn btn-success" onclick="printDiv()">
            <i class="fas fa-print"></i> Print
        </button>
        <button type="button" class="btn btn-danger" onclick="downloadPDF()">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>
</div>

@push('css')
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }

            .table thead {
                background-color: #6c757d !important;
                color: white !important;
            }

            .table-bordered td,
            .table-bordered th {
                border: 2px solid black !important;
                padding: 5px !important;
            }
        }

            {
            border-width: 2px !important;
        }

        .table td,
        .table th {
            padding: 8px !important;
            line-height: 1.3 !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        function printDiv() {
            var printContents = document.getElementById("printArea").innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
    @push('js')
        <script>
            function printDiv() {
                var printContents = document.getElementById("printArea").innerHTML;
                var originalContents = document.body.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload(); // Reload halaman agar tampilan kembali normal setelah print
            }

            function downloadPDF() {
                const element = document.getElementById("printArea");

                html2pdf()
                    .set({
                        margin: 10,
                        filename: 'Surat_Bukti_Belanja.pdf',
                        image: {
                            type: 'jpeg',
                            quality: 0.98
                        },
                        html2canvas: {
                            scale: 2,
                            logging: true,
                            dpi: 192,
                            letterRendering: true
                        },
                        jsPDF: {
                            unit: 'mm',
                            format: 'a4',
                            orientation: 'portrait'
                        }
                    })
                    .from(element)
                    .save();
            }
        </script>
    @endpush

@endpush
