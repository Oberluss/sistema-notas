<?php
session_start();
require_once 'conexion.php';
require_once 'check-session.php';

// Obtener ID de la nota
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = "ID de nota no especificado";
    header('Location: index.php');
    exit;
}

// Obtener la nota
$note = $db->getNoteById($id);

if (!$note) {
    $_SESSION['error'] = "La nota no existe";
    header('Location: index.php');
    exit;
}

// Verificar permisos
$user_id = $_SESSION['user_id'] ?? 0;
$is_admin = $_SESSION['is_admin'] ?? false;

if ($note['user_id'] != $user_id && !$is_admin) {
    $_SESSION['error'] = "No tienes permisos para editar esta nota";
    header('Location: vernota.php?id=' . $id);
    exit;
}

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Validar
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
    
    if (empty($errors)) {
        try {
            if ($db->updateNote($id, $title, $content)) {
                $_SESSION['success'] = "Nota actualizada exitosamente";
                header('Location: vernota.php?id=' . $id);
                exit;
            } else {
                $errors[] = "Error al actualizar la nota";
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

// Usar datos del POST si hay errores, sino usar datos de la nota
$title = $_POST['title'] ?? $note['title'];
$content = $_POST['content'] ?? $note['content'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Nota - Sistema de Notas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            margin: -20px -20px 30px;
        }
        
        .header h1 {
            margin: 0;
            padding: 0 20px;
            font-size: 24px;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        textarea {
            min-height: 300px;
            resize: vertical;
            font-family: inherit;
        }
        
        .char-count {
            text-align: right;
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .error-list {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
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
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .meta-info {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Editar Nota</h1>
        </div>
        
        <a href="vernota.php?id=<?php echo $id; ?>" class="back-link">← Volver a la nota</a>
        
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="meta-info">
                <strong>Información:</strong><br>
                Creada: <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?><br>
                <?php if ($note['updated_at'] && $note['updated_at'] != $note['created_at']): ?>
                    Última modificación: <?php echo date('d/m/Y H:i', strtotime($note['updated_at'])); ?><br>
                <?php endif; ?>
                Vistas: <?php echo $note['views'] ?? 0; ?>
            </div>
            
            <form method="POST" action="editarnota.php?id=<?php echo $id; ?>">
                <div class="form-group">
                    <label for="title">Título</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo htmlspecialchars($title); ?>" 
                           maxlength="200" 
                           required
                           autofocus>
                    <div class="char-count">
                        <span id="titleCount">0</span>/200 caracteres
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="content">Contenido</label>
                    <textarea id="content" 
                              name="content" 
                              required><?php echo htmlspecialchars($content); ?></textarea>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>
                    <a href="vernota.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Contador de caracteres
        const titleInput = document.getElementById('title');
        const titleCount = document.getElementById('titleCount');
        
        function updateCount() {
            titleCount.textContent = titleInput.value.length;
        }
        
        titleInput.addEventListener('input', updateCount);
        updateCount();
        
        // Auto-guardar borrador (opcional)
        let saveTimeout;
        const form = document.querySelector('form');
        
        form.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                // Aquí podrías implementar auto-guardado
                console.log('Auto-guardado...');
            }, 2000);
        });
    </script>
</body>
</html>
