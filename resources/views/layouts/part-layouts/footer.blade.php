     <!-- Main Footer -->
     <footer class="main-footer">
         <strong>Copyright &copy; 2025 <a href="https://github.com/mamasdea">MAMAS DEA</a>.</strong>
         All rights reserved.
         <div class="float-right d-none d-sm-inline-block">
             <b>Siberuang V</b> 1.0
         </div>
     </footer>
     </div>
     <!-- ./wrapper -->

     <!-- REQUIRED SCRIPTS -->

     <!-- jQuery -->
     <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
     <!-- Bootstrap -->
     <script src="{{ asset('AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
     <!-- AdminLTE -->
     <script src="{{ asset('AdminLTE-3.2.0/dist/js/adminlte.min.js') }}"></script>

     <!-- OPTIONAL SCRIPTS -->
     <script src="{{ asset('AdminLTE-3.2.0/plugins/chart.js/Chart.min.js') }}" defer></script>

     <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
     <script src="{{ asset('AdminLTE-3.2.0/dist/js/pages/dashboard3.js') }}" data-navigate></script>
     {{-- @stack('js') --}}
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <!-- Select2 -->
     <script src="{{ asset('AdminLTE-3.2.0/plugins/select2/js/select2.full.min.js') }}"></script>


     @livewireScripts
     @stack('js')
     </body>

     </html>
