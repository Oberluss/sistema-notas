<?php
/**
 * check-session.php
 * Verificación de sesión de usuario
 * 
 * Para sistemas simples sin login, este archivo puede estar vacío
 * o contener validaciones básicas
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración básica
define('REQUIRE_LOGIN', false); // Cambiar a true si se requiere login

// Si se requiere login
if (REQUIRE_LOGIN) {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        // Guardar la URL actual para redirigir después del login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirigir al login
        header('Location: admin/login.php');
        exit;
    }
    
    // Verificar timeout de sesión (30 minutos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Destruir sesión por inactividad
        session_unset();
        session_destroy();
        header('Location: admin/login.php?timeout=1');
        exit;
    }
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
}

// Funciones auxiliares de sesión

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Obtener ID del usuario actual
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 1; // Por defecto ID 1 si no hay login
}

/**
 * Obtener nombre del usuario actual
 */
function getCurrentUserName() {
    return $_SESSION['username'] ?? 'Usuario';
}

/**
 * Verificar si el usuario es administrador
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Establecer mensaje flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtener y limpiar mensajes flash
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Proteger contra CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('Error de seguridad: Token CSRF inválido');
    }
}

// Configurar zona horaria
date_default_timezone_set('America/Lima'); // Cambiar según tu zona

// Headers de seguridad
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Para desarrollo - mostrar errores (desactivar en producción)
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>
