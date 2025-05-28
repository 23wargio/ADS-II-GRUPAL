// Funciones globales
function showSuccessMessage(title, message, callback) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'success',
        confirmButtonColor: '#1565c0'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
}

function showErrorMessage(title, message) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'error',
        confirmButtonColor: '#d33'
    });
}

function confirmAction(title, message, confirmCallback, cancelCallback) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1565c0',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && typeof confirmCallback === 'function') {
            confirmCallback();
        } else if (result.dismiss === Swal.DismissReason.cancel && typeof cancelCallback === 'function') {
            cancelCallback();
        }
    });
}

// Inicialización de componentes
$(document).ready(function(){
    // Inicializar dropdowns
    $('.dropdown-trigger').dropdown();
    
    // Inicializar tooltips
    $('.tooltipped').tooltip();
    
    // Confirmación antes de eliminar
    $('.delete-btn').on('click', function(e){
        e.preventDefault();
        const url = $(this).attr('href');
        
        confirmAction(
            '¿Eliminar registro?',
            'Esta acción no se puede deshacer',
            function() {
                window.location.href = url;
            }
        );
    });
    
    // Confirmación antes de marcar como completado
    $('.complete-btn').on('click', function(e){
        e.preventDefault();
        const url = $(this).attr('href');
        
        confirmAction(
            '¿Marcar como completado?',
            '¿Estás seguro de que deseas marcar esta tarea como completada?',
            function() {
                window.location.href = url;
            }
        );
    });
});