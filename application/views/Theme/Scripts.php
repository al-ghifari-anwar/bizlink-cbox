<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<!-- <script src="<?= base_url('assets') ?>/plugins/jquery/jquery.min.js"></script> -->
<!-- Bootstrap 4 -->
<script src="<?= base_url('assets') ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('assets') ?>/dist/js/adminlte.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="<?= base_url('assets') ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/jszip/jszip.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- Select2 -->
<script src="<?= base_url('assets') ?>/plugins/select2/js/select2.full.min.js"></script>
<!-- InputMask -->
<script src="<?= base_url('assets') ?>/plugins/moment/moment.min.js"></script>
<script src="<?= base_url('assets') ?>/plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="<?= base_url('assets') ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- date-range-picker -->
<script src="<?= base_url('assets') ?>/plugins/daterangepicker/daterangepicker.js"></script>
<!-- bs-custom-file-input -->
<script src="<?= base_url('assets') ?>/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- ChartJS -->
<script src="<?= base_url('assets') ?>/plugins/chart.js/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/emn178/chartjs-plugin-labels/src/chartjs-plugin-labels.js"></script>
<script>
    $(function() {
        bsCustomFileInput.init();
    });
    $(function() {
        $('#table').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });

    $("#table-print").DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        "paging": true,
        "responsive": true,
        "lengthChange": true,
        "autoWidth": true,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#table-print_wrapper .col-md-6:eq(0)');

    //Initialize Select2 Elements
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    })
    $('#select2bs4').one('select2:open', function(e) {
        $('input.select2-search__field').prop('placeholder', 'Search...');
    });
    //Initialize Select2 Elements
    $('.select2bs41').select2({
        theme: 'bootstrap4'
    })
    $('#select2bs41').one('select2:open', function(e) {
        $('input.select2-search__field').prop('placeholder', 'Search...');
    });
    //Date range picker
    $('#reservation').daterangepicker()
</script>
<!-- Loading -->
<script>
    $(document).ready(function() {
        $("#loading-screen").fadeOut();

        $("a").on("click", function(e) {
            let target = $(this).attr("href");

            if (
                !target ||
                target === "#" ||
                target.startsWith("javascript") ||
                $(this).attr("data-toggle") === "modal" ||
                $(this).attr("data-target") ||
                $(this).attr("target") === "_blank" ||
                $(this).attr("target") === "__blank"
            ) {
                return;
            }

            // Cek apakah link valid dan bukan '#' atau JavaScript void
            if (target && target !== "#" && !target.startsWith("javascript")) {
                e.preventDefault(); // Cegah perpindahan halaman langsung

                $("#loading-screen").fadeIn(); // Tampilkan loading screen

                setTimeout(function() {
                    window.location.href = target; // Setelah delay, pindah halaman
                }, 300);
            }
        });
    });
    // Solusi untuk masalah "Back" browser: hilangkan loading saat halaman ditampilkan dari cache
    $(window).on("pageshow", function(event) {
        if (event.originalEvent.persisted) {
            console.log("Back button ditekan, menghilangkan loading...");
            $("#loading-screen").fadeOut();
        }
    });
</script>
<!-- Chart Score -->

</body>

</html>