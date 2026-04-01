     <!-- Main Footer -->
     <footer class="main-footer" style="background: #fff; border-top: 1px solid #e2e8f0; padding: 12px 20px; font-size: 13px; color: #94a3b8;">
         <strong style="color: #64748b;">Copyright &copy; {{ date('Y') }} <a href="https://github.com/mamasdea" style="color: #6366f1; text-decoration: none; font-weight: 600;">MAMAS DEA</a></strong>
         <span class="ml-1">All rights reserved.</span>
         <div class="float-right d-none d-sm-inline-block">
             <span style="background: #f1f5f9; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; color: #64748b;">Siberuang v2.0</span>
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
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <!-- Select2 -->
     <script src="{{ asset('AdminLTE-3.2.0/plugins/select2/js/select2.full.min.js') }}"></script>


     @livewireScripts
     @stack('js')
     </body>

     </html>
