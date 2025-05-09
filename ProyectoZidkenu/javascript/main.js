function validateEmail(email) {
    const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return re.test(email);
}

function validateDNI(dni) {
    const re = /^\d{8}$/;
    return re.test(dni);
}

function validateCelular(celular) {
    const re = /^\d{9}$/;
    return re.test(celular);
}

document.querySelector('form').addEventListener('submit', function(event) {
    let valid = true;

    // Obtener valores
    const email = document.getElementById('email').value.trim();
    const dni = document.getElementById('dni').value.trim();
    const celular = document.getElementById('celular').value.trim();

    // Limpiar mensajes anteriores
    document.getElementById('email-error').textContent = '';
    document.getElementById('dni-error').textContent = '';
    document.getElementById('celular-error').textContent = '';

    // Validar email
    if (!validateEmail(email)) {
        document.getElementById('email-error').textContent = 'Correo electrónico no válido.';
        valid = false;
    }

    // Validar DNI
    if (!validateDNI(dni)) {
        document.getElementById('dni-error').textContent = 'El DNI debe tener exactamente 8 dígitos.';
        valid = false;
    }

    // Validar celular
    if (!validateCelular(celular)) {
        document.getElementById('celular-error').textContent = 'El número de celular debe tener 9 dígitos.';
        valid = false;
    }

    if (!valid) {
        event.preventDefault();
    }
});
