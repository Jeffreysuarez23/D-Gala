/**
 * SISTEMA CENTRALIZADO DE VALIDACIONES
 * Validaciones en tiempo real para formularios
 * Restricciones: sin espacios, sin números negativos, seguridad
 */

const ValidationSystem = {
    
    /**
     * Reglas de validación
     */
    rules: {
        usuario: {
            regex: /.*/,
            mensaje: 'Usuario inválido',
            restricciones: 'Sin restricciones'
        },
        email: {
            regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            mensaje: 'Email válido requerido',
            restricciones: 'Sin espacios'
        },
        password: {
            regex: /.*/,
            mensaje: 'Contraseña inválida',
            restricciones: 'Sin restricciones'
        },
        nombre: {
            regex: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/,
            mensaje: 'Nombre: solo letras (2-50 caracteres)',
            restricciones: 'Sin números ni caracteres especiales'
        },
        telefono: {
            regex: /^\d{7,15}$/,
            mensaje: 'Teléfono: solo números (7-15 dígitos)',
            restricciones: 'Sin espacios ni caracteres especiales'
        },
        numero: {
            regex: /^\d+\.?\d*$/,
            mensaje: 'Solo números positivos',
            restricciones: 'Sin números negativos'
        },
        precio: {
            regex: /^\d+(\.\d{1,2})?$/,
            mensaje: 'Precio: solo números positivos con máximo 2 decimales',
            restricciones: 'Sin números negativos'
        },
        stock: {
            regex: /^\d+$/,
            mensaje: 'Stock: solo números enteros positivos',
            restricciones: 'Sin números negativos'
        },
        documento: {
            regex: /^\d{6,20}$/,
            mensaje: 'Documento: solo números (6-20 dígitos)',
            restricciones: 'Sin espacios'
        },
        titulo: {
            regex: /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-.,()]{3,100}$/,
            mensaje: 'Título: 3-100 caracteres',
            restricciones: 'Sin caracteres especiales prohibidos'
        },
        descripcion: {
            regex: /^[\s\S]{10,5000}$/,
            mensaje: 'Descripción: 10-5000 caracteres',
            restricciones: 'Mínimo 10 caracteres'
        },
        porcentaje: {
            regex: /^([0-9]|[1-9][0-9]|100)$/,
            mensaje: 'Porcentaje: 0-100',
            restricciones: 'Solo números enteros entre 0-100'
        }
    },

    /**
     * Validar un campo según su tipo
     */
    validar: function(valor, tipo, campoNombre = '') {
        if (!valor && tipo !== 'required') return true;
        
        const regla = this.rules[tipo];
        if (!regla) return true;

        // Eliminar espacios al inicio y final
        valor = typeof valor === 'string' ? valor.trim() : valor;

        // Validaciones específicas
        switch(tipo) {
            case 'usuario':
            case 'email':
            case 'password':
            case 'nombre':
            case 'documento':
            case 'titulo':
            case 'descripcion':
                if (!regla.regex.test(valor)) {
                    return {
                        valido: false,
                        mensaje: regla.mensaje,
                        tipo: tipo
                    };
                }
                break;

            case 'telefono':
                // Eliminar espacios del teléfono
                valor = valor.replace(/\s/g, '');
                if (!regla.regex.test(valor)) {
                    return {
                        valido: false,
                        mensaje: regla.mensaje,
                        tipo: tipo
                    };
                }
                break;

            case 'numero':
            case 'precio':
            case 'stock':
            case 'porcentaje':
                // Verificar números negativos
                if (parseFloat(valor) < 0) {
                    return {
                        valido: false,
                        mensaje: '❌ No permitidos números negativos',
                        tipo: tipo
                    };
                }
                if (!regla.regex.test(valor)) {
                    return {
                        valido: false,
                        mensaje: regla.mensaje,
                        tipo: tipo
                    };
                }
                break;

            case 'email':
                if (!regla.regex.test(valor)) {
                    return {
                        valido: false,
                        mensaje: regla.mensaje,
                        tipo: tipo
                    };
                }
                break;

            case 'required':
                if (valor === '' || valor === null) {
                    return {
                        valido: false,
                        mensaje: `${campoNombre} es requerido`,
                        tipo: tipo
                    };
                }
                break;

            case 'match':
                // Para comparar dos campos (password/repassword)
                return true;
                break;
        }

        return true;
    },

    /**
     * Validar coincidencia de contraseñas
     */
    validarCoincidencia: function(pass1, pass2, nombre1 = 'Campo', nombre2 = 'Confirmación') {
        if (pass1 !== pass2) {
            return {
                valido: false,
                mensaje: `❌ ${nombre1} y ${nombre2} no coinciden`,
                tipo: 'match'
            };
        }
        return true;
    },

    /**
     * Validar longitud mínima
     */
    validarLongitud: function(valor, minimo, maximo = null, nombre = 'Campo') {
        valor = typeof valor === 'string' ? valor.trim() : valor.toString();
        
        if (valor.length < minimo) {
            return {
                valido: false,
                mensaje: `❌ ${nombre} debe tener mínimo ${minimo} caracteres (actual: ${valor.length})`,
                tipo: 'length'
            };
        }

        if (maximo && valor.length > maximo) {
            return {
                valido: false,
                mensaje: `❌ ${nombre} no puede exceder ${maximo} caracteres (actual: ${valor.length})`,
                tipo: 'length'
            };
        }

        return true;
    },

    /**
     * Eliminar espacios innecesarios
     */
    limpiar: function(valor) {
        if (typeof valor !== 'string') return valor;
        return valor.trim().replace(/\s+/g, ' ');
    },

    /**
     * Validar sin espacios
     */
    sinEspacios: function(valor) {
        if (typeof valor !== 'string') return true;
        return !valor.includes(' ');
    }
};

/**
 * MANEJADOR DE ALERTAS
 */
const AlertSystem = {
    
    mostrar: function(tipo, titulo, mensaje, duracion = 4000) {
        // Crear contenedor si no existe
        if (!document.getElementById('alert-container')) {
            const container = document.createElement('div');
            container.id = 'alert-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            `;
            document.body.appendChild(container);
        }

        const container = document.getElementById('alert-container');

        // Crear alerta
        const alerta = document.createElement('div');
        alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
        alerta.style.cssText = `
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
        `;

        // Iconos según tipo
        const iconos = {
            'error': '❌',
            'success': '✅',
            'warning': '⚠️',
            'info': 'ℹ️',
            'validacion': '🔒'
        };

        const icono = iconos[tipo] || 'ℹ️';

        alerta.innerHTML = `
            <strong>${icono} ${titulo}</strong><br>
            <small>${mensaje}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Aplicar clase Bootstrap según tipo
        alerta.classList.remove('alert-error', 'alert-success', 'alert-warning', 'alert-info', 'alert-validacion');
        
        if (tipo === 'error') {
            alerta.classList.add('alert-danger');
        } else if (tipo === 'validacion') {
            alerta.classList.add('alert-warning');
        } else if (tipo === 'success') {
            alerta.classList.add('alert-success');
        } else if (tipo === 'warning') {
            alerta.classList.add('alert-warning');
        } else {
            alerta.classList.add('alert-info');
        }

        container.appendChild(alerta);

        // Auto cerrar
        if (duracion > 0) {
            setTimeout(() => {
                alerta.classList.remove('show');
                setTimeout(() => alerta.remove(), 150);
            }, duracion);
        }

        // Scroll hacia la alerta
        alerta.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    exito: function(titulo, mensaje = '', duracion = 3000) {
        this.mostrar('success', titulo, mensaje, duracion);
    },

    error: function(titulo, mensaje = '', duracion = 4000) {
        this.mostrar('error', titulo, mensaje, duracion);
    },

    validacion: function(titulo, mensaje = '', duracion = 3000) {
        this.mostrar('validacion', titulo, mensaje, duracion);
    },

    info: function(titulo, mensaje = '', duracion = 3000) {
        this.mostrar('info', titulo, mensaje, duracion);
    },

    warning: function(titulo, mensaje = '', duracion = 3000) {
        this.mostrar('warning', titulo, mensaje, duracion);
    }
};

/**
 * EVENTOS GLOBALES DE VALIDACIÓN
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Validar inputs en tiempo real
    document.addEventListener('blur', function(e) {
        if (!e.target.classList.contains('form-control')) return;
        
        const tipo = e.target.dataset.validationType;
        if (!tipo) return;

        const resultado = ValidationSystem.validar(e.target.value, tipo, e.target.placeholder);
        
        if (resultado !== true) {
            e.target.classList.add('is-invalid');
            e.target.classList.remove('is-valid');
            
            // Mostrar ayuda
            let helper = e.target.parentElement.querySelector('.invalid-feedback');
            if (!helper) {
                helper = document.createElement('div');
                helper.className = 'invalid-feedback d-block';
                e.target.parentElement.appendChild(helper);
            }
            helper.textContent = resultado.mensaje;
        } else {
            e.target.classList.remove('is-invalid');
            e.target.classList.add('is-valid');
            
            let helper = e.target.parentElement.querySelector('.invalid-feedback');
            if (helper) helper.textContent = '';
        }
    }, true);

    // Prevenir números negativos
    document.addEventListener('keypress', function(e) {
        const campo = e.target;
        
        if (!campo.classList.contains('form-control')) return;
        
        const tipo = campo.dataset.validationType;
        
        if (['numero', 'precio', 'stock', 'porcentaje', 'telefono', 'documento'].includes(tipo)) {
            // Si es menos (-), prevenir
            if (e.key === '-') {
                e.preventDefault();
                campo.classList.add('is-invalid');
                AlertSystem.validacion('Número Negativo', 'No se permiten números negativos');
            }
        }
    }, true);

    // Limpiar espacios al salir del campo
    document.addEventListener('blur', function(e) {
        if (!e.target.classList.contains('form-control')) return;
        if (!e.target.dataset.validationType) return;

        // Trimear valor
        if (typeof e.target.value === 'string') {
            e.target.value = e.target.value.trim();
        }
    }, true);
});

// Agregar animación CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath fill='%23dc3545' d='M6.8 3.5H5.2V7h1.6zm0 4.1H5.2V8.6h1.6z'/%3e%3c/svg%3e");
    }

    .form-control.is-valid {
        border-color: #28a745;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    #alert-container {
        position: fixed !important;
        top: 20px !important;
        right: 20px !important;
        z-index: 9999 !important;
    }
`;
document.head.appendChild(style);
