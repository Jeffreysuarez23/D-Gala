<?php
/**
 * SISTEMA CENTRALIZADO DE VALIDACIONES PHP
 * Validaciones del lado servidor para máxima seguridad
 */

class ValidadorFormularios {
    
    private static $errores = [];
    
    /**
     * Reglas de validación
     */
    private static $reglas = [
        'usuario' => [
            'patron' => '/.*/',
            'mensaje' => 'Usuario inválido'
        ],
        'email' => [
            'patron' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            'mensaje' => 'Email válido requerido'
        ],
        'password' => [
            'patron' => '/.*/',
            'mensaje' => 'Contraseña inválida'
        ],
        'nombre' => [
            'patron' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/',
            'mensaje' => 'Nombre: solo letras (2-50 caracteres)'
        ],
        'telefono' => [
            'patron' => '/^\d{7,15}$/',
            'mensaje' => 'Teléfono: solo números (7-15 dígitos)'
        ],
        'numero' => [
            'patron' => '/^\d+\.?\d*$/',
            'mensaje' => 'Solo números positivos'
        ],
        'precio' => [
            'patron' => '/^\d+(\.\d{1,2})?$/',
            'mensaje' => 'Precio: solo números positivos con máximo 2 decimales'
        ],
        'stock' => [
            'patron' => '/^\d+$/',
            'mensaje' => 'Stock: solo números enteros positivos'
        ],
        'documento' => [
            'patron' => '/^\d{6,20}$/',
            'mensaje' => 'Documento: solo números (6-20 dígitos)'
        ],
        'titulo' => [
            'patron' => '/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-.,()]{3,100}$/',
            'mensaje' => 'Título: 3-100 caracteres'
        ],
        'descripcion' => [
            'patron' => '/^[\s\S]{10,5000}$/',
            'mensaje' => 'Descripción: 10-5000 caracteres'
        ],
        'porcentaje' => [
            'patron' => '/^([0-9]|[1-9][0-9]|100)$/',
            'mensaje' => 'Porcentaje: 0-100'
        ]
    ];

    /**
     * Validar un campo
     */
    public static function validar($valor, $tipo) {
        $valor = self::limpiar($valor);
        
        if (!isset(self::$reglas[$tipo])) {
            return true; // Si no existe regla, es válido
        }

        $regla = self::$reglas[$tipo];
        $patron = $regla['patron'];

        // Validaciones específicas
        switch($tipo) {
            case 'numero':
            case 'precio':
            case 'stock':
            case 'porcentaje':
                // Verificar números negativos
                if (floatval($valor) < 0) {
                    return [
                        'valido' => false,
                        'mensaje' => '❌ No permitidos números negativos'
                    ];
                }
                break;
        }

        // Validar con patrón
        if (!preg_match($patron, $valor)) {
            return [
                'valido' => false,
                'mensaje' => $regla['mensaje']
            ];
        }

        return true;
    }

    /**
     * Validar coincidencia
     */
    public static function validarCoincidencia($valor1, $valor2, $nombre1 = 'Campo', $nombre2 = 'Confirmación') {
        if ($valor1 !== $valor2) {
            return [
                'valido' => false,
                'mensaje' => "❌ {$nombre1} y {$nombre2} no coinciden"
            ];
        }
        return true;
    }

    /**
     * Validar longitud
     */
    public static function validarLongitud($valor, $minimo, $maximo = null, $nombre = 'Campo') {
        $valor = self::limpiar($valor);
        $longitud = strlen($valor);

        if ($longitud < $minimo) {
            return [
                'valido' => false,
                'mensaje' => "❌ {$nombre} debe tener mínimo {$minimo} caracteres (actual: {$longitud})"
            ];
        }

        if ($maximo && $longitud > $maximo) {
            return [
                'valido' => false,
                'mensaje' => "❌ {$nombre} no puede exceder {$maximo} caracteres (actual: {$longitud})"
            ];
        }

        return true;
    }

    /**
     * Validar requerido
     */
    public static function requerido($valor, $nombre = 'Campo') {
        $valor = self::limpiar($valor);
        
        if (empty($valor)) {
            return [
                'valido' => false,
                'mensaje' => "❌ {$nombre} es requerido"
            ];
        }

        return true;
    }

    /**
     * Limpiar valor (eliminar espacios, trimear, escapar)
     */
    public static function limpiar($valor) {
        if (!is_string($valor)) {
            return $valor;
        }

        // Trimear espacios al inicio y final
        $valor = trim($valor);
        
        // Remover espacios múltiples
        $valor = preg_replace('/\s+/', ' ', $valor);

        return $valor;
    }

    /**
     * Validar sin espacios
     */
    public static function sinEspacios($valor, $nombre = 'Campo') {
        $valor = self::limpiar($valor);

        if (strpos($valor, ' ') !== false) {
            return [
                'valido' => false,
                'mensaje' => "❌ {$nombre} no puede contener espacios"
            ];
        }

        return true;
    }

    /**
     * Validar arreglo de valores
     */
    public static function validarArreglo($campos, $reglas) {
        self::$errores = [];

        foreach ($reglas as $campo => $validaciones) {
            $valor = $campos[$campo] ?? null;

            foreach ($validaciones as $validacion) {
                $resultado = null;

                if ($validacion['tipo'] === 'requerido') {
                    $resultado = self::requerido($valor, $validacion['nombre'] ?? $campo);
                } elseif ($validacion['tipo'] === 'validar') {
                    $resultado = self::validar($valor, $validacion['patron']);
                } elseif ($validacion['tipo'] === 'longitud') {
                    $resultado = self::validarLongitud(
                        $valor,
                        $validacion['minimo'],
                        $validacion['maximo'] ?? null,
                        $validacion['nombre'] ?? $campo
                    );
                } elseif ($validacion['tipo'] === 'coincidencia') {
                    $valor2 = $campos[$validacion['campo2']] ?? null;
                    $resultado = self::validarCoincidencia($valor, $valor2, $validacion['nombre1'] ?? $campo, $validacion['nombre2']);
                } elseif ($validacion['tipo'] === 'sinEspacios') {
                    $resultado = self::sinEspacios($valor, $validacion['nombre'] ?? $campo);
                }

                if ($resultado !== true) {
                    self::$errores[] = $resultado['mensaje'];
                }
            }
        }

        return empty(self::$errores);
    }

    /**
     * Obtener errores
     */
    public static function obtenerErrores() {
        return self::$errores;
    }

    /**
     * Validar email único (base de datos)
     */
    public static function emailUnico($email, $db, $tabla = 'usuarios', $campoEmail = 'email') {
        $email = self::limpiar($email);

        $stmt = $db->prepare("SELECT id FROM {$tabla} WHERE {$campoEmail} = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return [
                'valido' => false,
                'mensaje' => "❌ Este email ya está registrado"
            ];
        }

        return true;
    }

    /**
     * Validar usuario único (base de datos)
     */
    public static function usuarioUnico($usuario, $db, $tabla = 'usuarios', $campoUsuario = 'usuario') {
        $usuario = self::limpiar($usuario);

        $stmt = $db->prepare("SELECT id FROM {$tabla} WHERE {$campoUsuario} = ?");
        $stmt->execute([$usuario]);

        if ($stmt->rowCount() > 0) {
            return [
                'valido' => false,
                'mensaje' => "❌ Este usuario ya está registrado"
            ];
        }

        return true;
    }

    /**
     * Sanitizar HTML (prevenir XSS)
     */
    public static function sanitizar($valor) {
        if (!is_string($valor)) {
            return $valor;
        }

        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapar para SQL (aunque usamos prepared statements)
     */
    public static function escaparSQL($valor) {
        if (!is_string($valor)) {
            return $valor;
        }

        return addslashes($valor);
    }
}

?>
