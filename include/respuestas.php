<?php
/**
 * SISTEMA CENTRALIZADO DE RESPUESTAS
 * Respuestas JSON unificadas para AJAX
 */

class RespuestaAPI {
    
    private static $respuestas = [];

    /**
     * Respuesta exitosa
     */
    public static function exito($mensaje = '', $datos = [], $codigoHTTP = 200) {
        $respuesta = [
            'ok' => true,
            'tipo' => 'success',
            'titulo' => '✅ Éxito',
            'mensaje' => $mensaje,
            'datos' => $datos
        ];

        http_response_code($codigoHTTP);
        return $respuesta;
    }

    /**
     * Respuesta de error
     */
    public static function error($mensaje = '', $detalles = [], $codigoHTTP = 400) {
        $respuesta = [
            'ok' => false,
            'tipo' => 'error',
            'titulo' => '❌ Error',
            'mensaje' => $mensaje,
            'detalles' => $detalles
        ];

        http_response_code($codigoHTTP);
        return $respuesta;
    }

    /**
     * Respuesta de validación
     */
    public static function validacion($mensaje = '', $errores = []) {
        $respuesta = [
            'ok' => false,
            'tipo' => 'validacion',
            'titulo' => '🔒 Validación',
            'mensaje' => $mensaje ?: 'Errores en la validación',
            'errores' => $errores
        ];

        http_response_code(422);
        return $respuesta;
    }

    /**
     * Respuesta de advertencia
     */
    public static function advertencia($mensaje = '', $datos = []) {
        $respuesta = [
            'ok' => true,
            'tipo' => 'warning',
            'titulo' => '⚠️ Advertencia',
            'mensaje' => $mensaje,
            'datos' => $datos
        ];

        http_response_code(200);
        return $respuesta;
    }

    /**
     * Respuesta de información
     */
    public static function info($mensaje = '', $datos = []) {
        $respuesta = [
            'ok' => true,
            'tipo' => 'info',
            'titulo' => 'ℹ️ Información',
            'mensaje' => $mensaje,
            'datos' => $datos
        ];

        http_response_code(200);
        return $respuesta;
    }

    /**
     * Respuesta no autorizada
     */
    public static function noAutorizado($mensaje = 'No autorizado') {
        $respuesta = [
            'ok' => false,
            'tipo' => 'error',
            'titulo' => '🔒 No Autorizado',
            'mensaje' => $mensaje
        ];

        http_response_code(401);
        return $respuesta;
    }

    /**
     * Respuesta no encontrado
     */
    public static function noEncontrado($mensaje = 'Recurso no encontrado') {
        $respuesta = [
            'ok' => false,
            'tipo' => 'error',
            'titulo' => '❌ No Encontrado',
            'mensaje' => $mensaje
        ];

        http_response_code(404);
        return $respuesta;
    }

    /**
     * Respuesta conflicto
     */
    public static function conflicto($mensaje = 'Conflicto') {
        $respuesta = [
            'ok' => false,
            'tipo' => 'error',
            'titulo' => '⚠️ Conflicto',
            'mensaje' => $mensaje
        ];

        http_response_code(409);
        return $respuesta;
    }

    /**
     * Enviar respuesta JSON
     */
    public static function enviar($respuesta) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Enviar con alias de método
     */
    public static function send($respuesta) {
        self::enviar($respuesta);
    }

    /**
     * Verificar si es AJAX
     */
    public static function esAJAX() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Agregar mensaje a la cola de respuestas
     */
    public static function agregarMensaje($tipo, $titulo, $mensaje, $duracion = 3000) {
        self::$respuestas[] = [
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'duracion' => $duracion
        ];
    }

    /**
     * Obtener mensajes agregados
     */
    public static function obtenerMensajes() {
        return self::$respuestas;
    }

    /**
     * Limpiar mensajes
     */
    public static function limpiarMensajes() {
        self::$respuestas = [];
    }

    /**
     * Crear respuesta con mensajes múltiples
     */
    public static function conMensajes($ok = true, $mensaje = '', $datos = []) {
        $respuesta = [
            'ok' => $ok,
            'mensaje' => $mensaje,
            'datos' => $datos,
            'notificaciones' => self::$respuestas
        ];

        return $respuesta;
    }

    /**
     * Plantilla de respuesta para listar datos
     */
    public static function lista($datos, $total = 0, $mensaje = 'Datos obtenidos correctamente') {
        return self::exito($mensaje, [
            'items' => $datos,
            'total' => $total ?: count($datos)
        ]);
    }

    /**
     * Plantilla para paginación
     */
    public static function paginado($datos, $pagina = 1, $limite = 10, $total = 0) {
        return self::exito('Datos paginados obtenidos', [
            'items' => $datos,
            'pagina' => $pagina,
            'limite' => $limite,
            'total' => $total,
            'paginas' => ceil($total / $limite)
        ]);
    }
}

?>
