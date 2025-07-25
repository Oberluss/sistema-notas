<?php
/**
 * Archivo de conexión a la base de datos MySQL
 * Usuario: oberlus
 */

// Datos de conexión
$host = '192.168.10.136';  // IP del servidor MySQL
$usuario = 'oberlus';      // Usuario de la base de datos
$password = 'Admin2018';   // Contraseña del usuario
$base_datos = 'oberlus_db'; // Nombre de la base de datos

// Intentar establecer la conexión con manejo de errores
try {
    // Establecer la conexión
    $conexion = mysqli_connect($host, $usuario, $password, $base_datos);
    
    // Verificar si hay errores en la conexión
    if (!$conexion) {
        throw new Exception('Error de conexión: ' . mysqli_connect_error());
    }
    
    // Establecer el juego de caracteres a utf8mb4
    mysqli_set_charset($conexion, 'utf8mb4');
    
    // Configurar la zona horaria (opcional)
    date_default_timezone_set('Europe/Madrid');
    
} catch (Exception $e) {
    // Guardar el error en un log para diagnóstico
    error_log('Error de conexión a la base de datos: ' . $e->getMessage(), 0);
    
    // Si estamos en desarrollo, mostrar el error
    if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1') {
        die('Error de conexión: ' . $e->getMessage());
    } else {
        // En producción, mostrar un mensaje genérico
        die('No se pudo conectar a la base de datos. Por favor, contacte al administrador.');
    }
}

/**
 * Función para sanitizar entradas
 * @param string $dato Dato a sanitizar
 * @return string Dato sanitizado
 */
function sanitizar($dato) {
    global $conexion;
    return mysqli_real_escape_string($conexion, trim($dato));
}

/**
 * Función para cerrar la conexión
 */
function cerrar_conexion() {
    global $conexion;
    if (isset($conexion) && $conexion) {
        mysqli_close($conexion);
    }
}
?>
