<?php
session_start();
require_once 'conexion.php';
require_once 'check-session.php';

// Verificar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Obtener datos del formulario
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$user_id = $_SESSION['user_id'] ?? 1;

// Validar datos
$errors = [];

if (empty($title)) {
    $errors[] = "El título es obligatorio";
}

if (empty($content)) {
    $errors[] = "El contenido es obligatorio";
}

if (strlen($title) > 200) {
    $errors[] = "El título no puede exceder 200 caracteres";
}

// Si hay errores, volver al formulario
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = [
        'title' => $title,
        'content' => $content
    ];
    header('Location: crear_nota.php');
    exit;
}

try {
    // Crear la nota
    $noteId = $db->createNote($title, $content, $user_id);
    
    if ($noteId) {
        $_SESSION['success'] = "Nota creada exitosamente";
        header('Location: vernota.php?id=' . $noteId);
    } else {
        $_SESSION['error'] = "Error al crear la nota";
        header('Location: crear_nota.php');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: crear_nota.php');
}

exit;
?>
