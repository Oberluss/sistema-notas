<?php
session_start();
require_once 'conexion.php';

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

// Incrementar vistas
$db->incrementViews($id);
$note['views'] = ($note['views'] ?? 0) + 1;

// Formatear contenido (convertir saltos de línea)
$content = nl2br(htmlspecialchars($note['content']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($note['title']); ?> - Sistema de Notas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            opacity: 0.9;
            transition: opacity 0.3s;
        }
        
        .back-link:hover {
            opacity: 1;
        }
        
        .main-content {
            padding: 30px 0;
        }
        
        .note-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .note-header {
            padding: 30px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .note-title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .note-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .note-content {
            padding: 30px;
            font-size: 18px;
            line-height: 1.8;
            color: #2c3e50;
        }
        
        .note-actions {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #ecf0f1;
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
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
        
        .related-notes {
            margin-top: 40px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .related-notes h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .related-list {
            list-style: none;
        }
        
        .related-list li {
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .related-list li:last-child {
            border-bottom: none;
        }
        
        .related-list a {
            color: #3498db;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .related-list a:hover {
            color: #2980b9;
        }
        
        .share-section {
            margin-top: 30px;
            padding: 20px;
            background: #ecf0f1;
            border-radius: 6px;
            text-align: center;
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .share-btn {
            padding: 8px 16px;
            background: #34495e;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .share-btn:hover {
            background: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .note-title {
                font-size: 24px;
            }
            
            .note-content {
                font-size: 16px;
                padding: 20px;
            }
            
            .note-actions {
                flex-wrap: wrap;
            }
            
            .btn {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
        
        @media print {
            .header,
            .note-actions,
            .related-notes,
            .share-section {
                display: none;
            }
            
            .note-container {
                box-shadow: none;
            }
            
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="header-nav">
                <a href="index.php" class="back-link">
                    ← Volver al listado
                </a>
                <div>
                    Sistema de Notas
                </div>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <article class="note-container">
                <div class="note-header">
                    <h1 class="note-title"><?php echo htmlspecialchars($note['title']); ?></h1>
                    
                    <div class="note-meta">
                        <div class="meta-item">
                            📅 <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?>
                        </div>
                        
                        <?php if ($note['updated_at'] && $note['updated_at'] != $note['created_at']): ?>
                            <div class="meta-item">
                                ✏️ Actualizado: <?php echo date('d/m/Y H:i', strtotime($note['updated_at'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            👁️ <?php echo $note['views']; ?> vistas
                        </div>
                    </div>
                </div>
                
                <div class="note-content">
                    <?php echo $content; ?>
                </div>
                
                <div class="note-actions">
                    <a href="editarnota.php?id=<?php echo $note['id']; ?>" class="btn btn-primary">
                        ✏️ Editar
                    </a>
                    
                    <a href="eliminar_nota.php?id=<?php echo $note['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('¿Estás seguro de eliminar esta nota?')">
                        🗑️ Eliminar
                    </a>
                    
                    <button onclick="window.print()" class="btn btn-secondary">
                        🖨️ Imprimir
                    </button>
                    
                    <button onclick="copyLink()" class="btn btn-secondary">
                        🔗 Copiar enlace
                    </button>
                </div>
            </article>
            
            <div class="share-section">
                <h3>Compartir esta nota</h3>
                <div class="share-buttons">
                    <a href="https://wa.me/?text=<?php echo urlencode($note['title'] . ' - ' . $url); ?>" 
                       target="_blank" 
                       class="share-btn"
                       style="background: #25D366;">
                        WhatsApp
                    </a>
                    
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($note['title']); ?>&url=<?php echo urlencode($url ?? ''); ?>" 
                       target="_blank" 
                       class="share-btn"
                       style="background: #1DA1F2;">
                        Twitter
                    </a>
                    
                    <a href="mailto:?subject=<?php echo urlencode($note['title']); ?>&body=<?php echo urlencode('Te comparto esta nota: ' . ($url ?? '')); ?>" 
                       class="share-btn"
                       style="background: #EA4335;">
                        Email
                    </a>
                </div>
            </div>
            
            <?php
            // Obtener notas relacionadas (últimas 5 notas, excluyendo la actual)
            $all_notes = $db->getNotes(6);
            $related_notes = array_filter($all_notes, function($n) use ($id) {
                return $n['id'] != $id;
            });
            $related_notes = array_slice($related_notes, 0, 5);
            ?>
            
            <?php if (!empty($related_notes)): ?>
                <div class="related-notes">
                    <h3>Otras notas</h3>
                    <ul class="related-list">
                        <?php foreach ($related_notes as $related): ?>
                            <li>
                                <a href="vernota.php?id=<?php echo $related['id']; ?>">
                                    <span><?php echo htmlspecialchars($related['title']); ?></span>
                                    <small><?php echo date('d/m/Y', strtotime($related['created_at'])); ?></small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function copyLink() {
            const url = window.location.href;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Enlace copiado al portapapeles');
                }, function() {
                    fallbackCopyLink(url);
                });
            } else {
                fallbackCopyLink(url);
            }
        }
        
        function fallbackCopyLink(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                alert('Enlace copiado al portapapeles');
            } catch (err) {
                alert('No se pudo copiar el enlace');
            }
            
            document.body.removeChild(textArea);
        }
        
        // Resaltar si viene de una búsqueda
        const urlParams = new URLSearchParams(window.location.search);
        const highlight = urlParams.get('highlight');
        
        if (highlight) {
            const content = document.querySelector('.note-content');
            const regex = new RegExp('(' + highlight + ')', 'gi');
            content.innerHTML = content.innerHTML.replace(regex, '<mark>$1</mark>');
        }
    </script>
</body>
</html>
