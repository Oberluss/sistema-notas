<?php
session_start();
require_once 'conexion.php';
require_once 'check-session.php';

// Verificar si se recibió el ID
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "ID de nota no especificado";
    header('Location: index.php');
    exit;
}

// Verificar que la nota existe
$note = $db->getNoteById($id);

if (!$note) {
    $_SESSION['error'] = "La nota no existe";
    header('Location: index.php');
    exit;
}

// Verificar permisos (solo el autor o admin puede eliminar)
$user_id = $_SESSION['user_id'] ?? 0;
$is_admin = $_SESSION['is_admin'] ?? false;

if ($note['user_id'] != $user_id && !$is_admin) {
    $_SESSION['error'] = "No tienes permisos para eliminar esta nota";
    header('Location: index.php');
    exit;
}

// Si es una confirmación de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        if ($db->deleteNote($id)) {
            $_SESSION['success'] = "Nota eliminada exitosamente";
        } else {
            $_SESSION['error'] = "Error al eliminar la nota";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header('Location: index.php');
    exit;
}

// Si no es POST, mostrar confirmación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Nota - Sistema de Notas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .delete-confirm {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .delete-confirm h2 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .note-preview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: left;
        }
        
        .note-preview h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .note-preview p {
            color: #7f8c8d;
            margin: 0;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .warning-icon {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="delete-confirm">
            <div class="warning-icon">⚠️</div>
            <h2>¿Estás seguro de eliminar esta nota?</h2>
            
            <div class="note-preview">
                <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                <p><?php echo htmlspecialchars(substr($note['content'], 0, 200)) . '...'; ?></p>
                <small>Creada: <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?></small>
            </div>
            
            <p><strong>Esta acción no se puede deshacer.</strong></p>
            
            <form method="POST" action="eliminar_nota.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="confirm" value="1">
                
                <div class="button-group">
                    <button type="submit" class="btn btn-danger">
                        Sí, eliminar
                    </button>
                    <a href="vernota.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
