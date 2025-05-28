    <!-- jQuery -->
    <script src="js/jquery-3.6.1.min.js"></script>
    <!-- Materialize JS -->
    <script src="js/materialize.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    
    <script>
        $(document).ready(function(){
            $('.sidenav').sidenav();
            $('select').formSelect();
            $('.modal').modal();
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                i18n: {
                    months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    weekdays: ['Domingo','Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    weekdaysShort: ['Dom','Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                    weekdaysAbbrev: ['D','L', 'M', 'M', 'J', 'V', 'S'],
                    today: 'Hoy',
                    clear: 'Limpiar',
                    cancel: 'Cancelar',
                    done: 'Ok'
                }
            });
        });
    </script>
</body>
</html>