// FUNCIÓN GENÉRICA DE SOPORTE PARA ERRORES

function mostrarError(idElemento, mensaje) {
    const pError = document.getElementById(idElemento);
    if (!pError) return; // Salvaguarda en caso de que falte el contenedor en el HTML

    if (mensaje) {
        pError.textContent = mensaje;
        pError.style.color = "red";
    } else {
        pError.textContent = "";
    }
}
// FUNCIONES DE VALIDACIÓN ESPECÍFICAS

// 1. Validar Campos de Texto
function validaTexto(idInput, idError) {
    const valor = document.getElementById(idInput).value.trim();

    if (valor === "") {
        mostrarError(idError, "Este campo no puede estar vacío.");
    } else if (valor.length < 3) {
        mostrarError(idError, "Debe tener al menos 3 caracteres.");
    } else {
        mostrarError(idError, "");
    }
}

// 2. Validar Email
function validaEmail(idInput, idError) {
    const valor = document.getElementById(idInput).value.trim();
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (valor === "") {
        mostrarError(idError, "Este campo no puede estar vacío.");
    } else if (!regexEmail.test(valor)) {
        mostrarError(idError, "Email no válido.");
    } else {
        mostrarError(idError, "");
    }
}

// 3. Validar Contraseña
function validaPassword(idInput, idError) {
    const valor = document.getElementById(idInput).value;
    const regexPass = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

    if (valor === "") {
        mostrarError(idError, "Este campo no puede estar vacío.");
    } else if (!regexPass.test(valor)) {
        mostrarError(idError, "Mínimo 8 caracteres, una mayúscula y un número.");
    } else {
        mostrarError(idError, "");
    }
}

// 4. Validar Fecha de Nacimiento
function validaFechaNacimiento(idInput, idError) {
    const valor = document.getElementById(idInput).value;
    
    if (valor === "") {
        mostrarError(idError, "Este campo no puede estar vacío.");
        return;
    }

    const fechaInput = new Date(valor);
    fechaInput.setHours(0, 0, 0, 0);
    
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (fechaInput > hoy) {
        mostrarError(idError, "La fecha no puede ser futura.");
    } else {
        mostrarError(idError, "");
    }
}

// 5. Validar Fecha de Cita
function validaFechaCita(idInput, idError) {
    const valor = document.getElementById(idInput).value;

    if (!valor) {
        mostrarError(idError, "La fecha es obligatoria.");
        return;
    }

    const fechaInput = new Date(valor);
    fechaInput.setHours(0, 0, 0, 0);
    
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (fechaInput < hoy) {
        mostrarError(idError, "La fecha no puede ser anterior a hoy.");
    } else {
        mostrarError(idError, "");
    }
}