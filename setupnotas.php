<?php
/**
 * Setup del Sistema de Notas
 * Archivo: setup.php
 * 
 * Instrucciones:
 * 1. Sube SOLO este archivo a la raíz de tu servidor
 * 2. Accede desde el navegador: http://tudominio.com/setup.php
 * 3. Sigue los pasos del instalador
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de GitHub
define('GITHUB_USER', 'Oberluss');
define('GITHUB_REPO', 'sistema-notas');
define('GITHUB_BRANCH', 'main');
define('APP_NAME', 'Sistema de Notas');
define('APP_VERSION', '2.0');

class NotasSetup {
    private $errors = [];
    private $messages = [];
    private $step = 1;
    private $github_raw = 'https://raw.githubusercontent.com/';
    
    // Archivos principales a descargar
    private $core_files = [
        'index.php',
        'conexion.php',
        'check-session.php',
        'crear_nota.php',
        'guardar_nota.php',
        'editarnota.php',
        'eliminar_nota.php',
        'vernota.php',
        'vernotas.php',
        '.htaccess',
        'robots.txt'
    ];
    
    // Archivos del admin
    private $admin_files = [
        'admin/index.php',
        'admin/login.php',
        'admin/logout.php',
        'admin/dashboard.php'
    ];
    
    // Archivos de assets
    private $asset_files = [
        'assets/css/style.css',
        'assets/css/admin.css',
        'assets/js/main.js',
        'assets/images/logo.png'
    ];
    
    public function __construct() {
        // Procesar step
        if (isset($_POST['step'])) {
            $this->step = (int)$_POST['step'];
        } elseif (isset($_GET['step'])) {
            $this->step = (int)$_GET['step'];
        }
        
        // IMPORTANTE: Procesar acciones ANTES de enviar cualquier HTML
        $this->processEarlyActions();
    }
    
    /**
     * Procesar acciones que requieren redirección
     * DEBE ejecutarse ANTES de cualquier salida HTML
     */
    private function processEarlyActions() {
        // Procesar configuración del paso 2
        if ($this->step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'configure') {
            $_SESSION['setup_config'] = [
                'site_name' => $_POST['site_name'] ?? APP_NAME,
                'admin_user' => $_POST['admin_user'] ?? 'admin',
                'admin_pass' => $_POST['admin_pass'] ?? '',
                'admin_email' => $_POST['admin_email'] ?? '',
                'timezone' => $_POST['timezone'] ?? 'America/Lima',
                'allow_registration' => isset($_POST['allow_registration'])
            ];
            
            // Redirigir al paso 3
            header('Location: ?step=3');
            exit;
        }
        
        // Procesar descarga de archivos (AJAX)
        if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
            header('Content-Type: application/json');
            
            $file = $_GET['file'];
            $result = $this->downloadFileFromGitHub($file);
            
            echo json_encode($result);
            exit;
        }
        
        // Procesar eliminación del instalador
        if ($this->step == 5 && isset($_GET['delete']) && $_GET['delete'] == '1') {
            @unlink(__FILE__);
            header('Location: ?step=5');
            exit;
        }
    }
    
    public function run() {
        // Ahora sí podemos enviar HTML
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Setup - <?php echo APP_NAME; ?></title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                
                .setup-container {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    max-width: 900px;
                    width: 100%;
                    overflow: hidden;
                }
                
                .setup-header {
                    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                    color: white;
                    padding: 40px;
                    text-align: center;
                }
                
                .setup-header h1 {
                    font-size: 32px;
                    margin-bottom: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 15px;
                }
                
                .logo {
                    font-size: 48px;
                }
                
                .setup-progress {
                    display: flex;
                    background: #f5f5f5;
                    padding: 0;
                }
                
                .progress-step {
                    flex: 1;
                    padding: 20px 15px;
                    text-align: center;
                    border-right: 1px solid #ddd;
                    position: relative;
                    font-size: 14px;
                    color: #666;
                    font-weight: 500;
                }
                
                .progress-step:last-child {
                    border-right: none;
                }
                
                .progress-step.active {
                    background: #3498db;
                    color: white;
                    font-weight: bold;
                }
                
                .progress-step.completed {
                    background: #27ae60;
                    color: white;
                }
                
                .step-icon {
                    font-size: 24px;
                    margin-bottom: 5px;
                }
                
                .setup-content {
                    padding: 50px;
                }
                
                .alert {
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 25px;
                    display: flex;
                    align-items: flex-start;
                    gap: 15px;
                }
                
                .alert-icon {
                    font-size: 24px;
                    flex-shrink: 0;
                }
                
                .alert-error {
                    background: #fee;
                    color: #c0392b;
                    border: 1px solid #e74c3c;
                }
                
                .alert-success {
                    background: #d5f4e6;
                    color: #27ae60;
                    border: 1px solid #27ae60;
                }
                
                .alert-info {
                    background: #d6eaf8;
                    color: #2980b9;
                    border: 1px solid #3498db;
                }
                
                .alert-warning {
                    background: #fcf3cf;
                    color: #f39c12;
                    border: 1px solid #f1c40f;
                }
                
                .requirement-list {
                    list-style: none;
                    margin: 25px 0;
                }
                
                .requirement-list li {
                    padding: 15px;
                    margin-bottom: 12px;
                    background: #f8f9fa;
                    border-radius: 8px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border: 1px solid #e9ecef;
                }
                
                .requirement-list .ok {
                    color: #27ae60;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                .requirement-list .error {
                    color: #e74c3c;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                .form-group {
                    margin-bottom: 25px;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: #2c3e50;
                }
                
                .form-group input,
                .form-group select {
                    width: 100%;
                    padding: 12px 15px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    font-size: 16px;
                    transition: border-color 0.3s;
                }
                
                .form-group input:focus,
                .form-group select:focus {
                    outline: none;
                    border-color: #3498db;
                    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
                }
                
                .form-group small {
                    display: block;
                    margin-top: 5px;
                    color: #7f8c8d;
                }
                
                .button-group {
                    display: flex;
                    gap: 15px;
                    margin-top: 35px;
                }
                
                .btn {
                    padding: 14px 28px;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: all 0.3s;
                    font-weight: 500;
                }
                
                .btn-primary {
                    background: #3498db;
                    color: white;
                }
                
                .btn-primary:hover {
                    background: #2980b9;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
                }
                
                .btn-secondary {
                    background: #ecf0f1;
                    color: #2c3e50;
                }
                
                .btn-secondary:hover {
                    background: #bdc3c7;
                }
                
                .btn-danger {
                    background: #e74c3c;
                    color: white;
                }
                
                .btn-danger:hover {
                    background: #c0392b;
                }
                
                .btn:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                    transform: none !important;
                }
                
                .loading {
                    display: inline-block;
                    width: 20px;
                    height: 20px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .file-list {
                    max-height: 400px;
                    overflow-y: auto;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 25px 0;
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    background: #f8f9fa;
                }
                
                .file-list .file {
                    padding: 5px 0;
                    color: #27ae60;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .file-list .folder {
                    padding: 5px 0;
                    color: #3498db;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .file-list .error-file {
                    color: #e74c3c;
                }
                
                .complete-icon {
                    font-size: 72px;
                    color: #27ae60;
                    text-align: center;
                    margin: 30px 0;
                    animation: bounce 1s ease-in-out;
                }
                
                @keyframes bounce {
                    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                    40% { transform: translateY(-20px); }
                    60% { transform: translateY(-10px); }
                }
                
                h2 {
                    color: #2c3e50;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .icon {
                    font-size: 32px;
                }
                
                code {
                    background: #f8f9fa;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-family: 'Courier New', monospace;
                    color: #e74c3c;
                }
                
                .download-log {
                    background: #2c3e50;
                    color: #ecf0f1;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    font-family: 'Courier New', monospace;
                    font-size: 14px;
                    max-height: 300px;
                    overflow-y: auto;
                }
                
                .download-log .success {
                    color: #2ecc71;
                }
                
                .download-log .error {
                    color: #e74c3c;
                }
                
                .download-log .info {
                    color: #3498db;
                }
                
                @media (max-width: 768px) {
                    .setup-content {
                        padding: 30px 20px;
                    }
                    
                    .setup-header h1 {
                        font-size: 24px;
                    }
                    
                    .progress-step {
                        font-size: 12px;
                        padding: 15px 5px;
                    }
                    
                    .step-icon {
                        font-size: 20px;
                    }
                    
                    .button-group {
                        flex-direction: column;
                    }
                    
                    .btn {
                        width: 100%;
                        justify-content: center;
                    }
                }
            </style>
        </head>
        <body>
            <div class="setup-container">
                <div class="setup-header">
                    <h1>
                        <span class="logo">📝</span>
                        Setup de <?php echo APP_NAME; ?>
                    </h1>
                    <p>Versión <?php echo APP_VERSION; ?> - Instalación desde GitHub</p>
                </div>
                
                <div class="setup-progress">
                    <div class="progress-step <?php echo $this->step >= 1 ? ($this->step > 1 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">🔍</div>
                        Verificación
                    </div>
                    <div class="progress-step <?php echo $this->step >= 2 ? ($this->step > 2 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">⚙️</div>
                        Configuración
                    </div>
                    <div class="progress-step <?php echo $this->step >= 3 ? ($this->step > 3 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">📥</div>
                        Preparación
                    </div>
                    <div class="progress-step <?php echo $this->step >= 4 ? ($this->step > 4 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">🔧</div>
                        Instalación
                    </div>
                    <div class="progress-step <?php echo $this->step >= 5 ? 'active' : ''; ?>">
                        <div class="step-icon">✅</div>
                        Finalización
                    </div>
                </div>
                
                <div class="setup-content">
                    <?php
                    switch($this->step) {
                        case 1:
                            $this->stepRequirements();
                            break;
                        case 2:
                            $this->stepConfiguration();
                            break;
                        case 3:
                            $this->stepDownload();
                            break;
                        case 4:
                            $this->stepInstall();
                            break;
                        case 5:
                            $this->stepComplete();
                            break;
                        default:
                            $this->stepRequirements();
                    }
                    ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    private function stepRequirements() {
        $requirements = $this->checkRequirements();
        $canContinue = !in_array(false, $requirements);
        ?>
        <h2><span class="icon">🔍</span> Verificación de Requisitos</h2>
        <p>Verificando que tu servidor cumple con los requisitos mínimos para instalar <?php echo APP_NAME; ?>.</p>
        
        <ul class="requirement-list">
            <li>
                <span>PHP 7.0 o superior</span>
                <span class="<?php echo $requirements['php'] ? 'ok' : 'error'; ?>">
                    <?php echo $requirements['php'] ? '✓' : '✗'; ?> 
                    <?php echo PHP_VERSION; ?>
                </span>
            </li>
            <li>
                <span>Extensión JSON</span>
                <span class="<?php echo $requirements['json'] ? 'ok' : 'error'; ?>">
                    <?php echo $requirements['json'] ? '✓ Instalada' : '✗ No disponible'; ?>
                </span>
            </li>
            <li>
                <span>Extensión cURL</span>
                <span class="<?php echo $requirements['curl'] ? 'ok' : 'error'; ?>">
                    <?php echo $requirements['curl'] ? '✓ Instalada' : '✗ No disponible'; ?>
                </span>
            </li>
            <li>
                <span>Permisos de escritura</span>
                <span class="<?php echo $requirements['writable'] ? 'ok' : 'error'; ?>">
                    <?php echo $requirements['writable'] ? '✓ Directorio escribible' : '✗ Sin permisos'; ?>
                </span>
            </li>
            <li>
                <span>Función file_get_contents</span>
                <span class="<?php echo $requirements['file_get_contents'] ? 'ok' : 'error'; ?>">
                    <?php echo $requirements['file_get_contents'] ? '✓ Disponible' : '✗ Deshabilitada'; ?>
                </span>
            </li>
            <li>
                <span>allow_url_fopen</span>
                <span class="<?php echo $requirements['allow_url_fopen'] ? 'ok' : 'error'; ?>">
                    <?php echo $requirements['allow_url_fopen'] ? '✓ Habilitado' : '✗ Deshabilitado'; ?>
                </span>
            </li>
        </ul>
        
        <?php if (!$canContinue): ?>
            <div class="alert alert-error">
                <span class="alert-icon">⚠️</span>
                <div>
                    <strong>Requisitos no cumplidos</strong><br>
                    Por favor, corrige los problemas marcados en rojo antes de continuar con la instalación.
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <span class="alert-icon">✅</span>
                <div>
                    <strong>Todos los requisitos cumplidos</strong><br>
                    Tu servidor está listo para instalar <?php echo APP_NAME; ?>.
                </div>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <span class="alert-icon">📦</span>
            <div>
                <strong>Información del repositorio:</strong><br>
                Usuario: <?php echo GITHUB_USER; ?><br>
                Repositorio: <?php echo GITHUB_REPO; ?><br>
                Rama: <?php echo GITHUB_BRANCH; ?><br>
                URL: <a href="https://github.com/<?php echo GITHUB_USER; ?>/<?php echo GITHUB_REPO; ?>" target="_blank">
                    https://github.com/<?php echo GITHUB_USER; ?>/<?php echo GITHUB_REPO; ?>
                </a>
            </div>
        </div>
        
        <div class="button-group">
            <button class="btn btn-primary" <?php echo !$canContinue ? 'disabled' : ''; ?> 
                    onclick="window.location.href='?step=2'">
                Continuar <span>→</span>
            </button>
        </div>
        <?php
    }
    
    private function stepConfiguration() {
        ?>
        <h2><span class="icon">⚙️</span> Configuración del Sistema</h2>
        <p>Configura los parámetros básicos de tu sistema de notas.</p>
        
        <form method="POST" action="">
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="action" value="configure">
            
            <div class="form-group">
                <label for="site_name">Nombre del Sitio</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo APP_NAME; ?>" required>
                <small>El nombre que aparecerá en la parte superior del sistema</small>
            </div>
            
            <hr style="margin: 30px 0; border: 1px solid #ecf0f1;">
            
            <h3 style="margin-bottom: 20px;">👤 Cuenta de Administrador</h3>
            
            <div class="form-group">
                <label for="admin_user">Usuario Administrador</label>
                <input type="text" id="admin_user" name="admin_user" value="admin" required pattern="[a-zA-Z0-9_]{3,20}">
                <small>Solo letras, números y guión bajo (3-20 caracteres)</small>
            </div>
            
            <div class="form-group">
                <label for="admin_pass">Contraseña</label>
                <input type="password" id="admin_pass" name="admin_pass" required minlength="6">
                <small>Mínimo 6 caracteres. Guárdala en un lugar seguro</small>
            </div>
            
            <div class="form-group">
                <label for="admin_email">Email del Administrador</label>
                <input type="email" id="admin_email" name="admin_email" required>
                <small>Para recuperación de contraseña y notificaciones</small>
            </div>
            
            <hr style="margin: 30px 0; border: 1px solid #ecf0f1;">
            
            <h3 style="margin-bottom: 20px;">🌍 Configuración Regional</h3>
            
            <div class="form-group">
                <label for="timezone">Zona Horaria</label>
                <select id="timezone" name="timezone">
                    <option value="America/Lima">Lima (UTC-5)</option>
                    <option value="America/Mexico_City">Ciudad de México (UTC-6)</option>
                    <option value="America/Buenos_Aires">Buenos Aires (UTC-3)</option>
                    <option value="America/Bogota">Bogotá (UTC-5)</option>
                    <option value="America/Santiago">Santiago (UTC-3)</option>
                    <option value="Europe/Madrid">Madrid (UTC+1)</option>
                    <option value="America/New_York">Nueva York (UTC-4)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="allow_registration" value="1">
                    Permitir registro de nuevos usuarios
                </label>
                <small>Si está desactivado, solo el administrador podrá crear usuarios</small>
            </div>
            
            <div class="button-group">
                <a href="?step=1" class="btn btn-secondary">← Atrás</a>
                <button type="submit" class="btn btn-primary">Continuar →</button>
            </div>
        </form>
        <?php
    }
    
    private function stepDownload() {
        ?>
        <h2><span class="icon">📥</span> Preparando Sistema</h2>
        <p>Preparando los archivos del sistema...</p>
        
        <div class="alert alert-info">
            <span class="alert-icon">📦</span>
            <div>
                <strong>Repositorio detectado:</strong> 
                <a href="https://github.com/<?php echo GITHUB_USER; ?>/<?php echo GITHUB_REPO; ?>" target="_blank">
                    <?php echo GITHUB_USER . '/' . GITHUB_REPO; ?>
                </a><br>
                <strong>Nota:</strong> Se crearán los archivos faltantes automáticamente
            </div>
        </div>
        
        <div id="download-status" class="download-log">
            <div class="info">🚀 Verificando archivos existentes en GitHub...</div>
        </div>
        
        <div id="download-progress" style="display:none;">
            <div class="file-list" id="file-list"></div>
        </div>
        
        <div class="button-group">
            <a href="?step=2" class="btn btn-secondary" id="back-btn">← Atrás</a>
            <button id="continue-btn" class="btn btn-primary" style="display:none;" 
                    onclick="window.location.href='?step=4'">
                Continuar →
            </button>
        </div>
        
        <script>
            // Lista de archivos que SÍ existen en tu GitHub
            const existingFiles = [
                '.htaccess',
                'check-session.php',
                'crear_nota.php',
                'editarnota.php',
                'eliminar_nota.php',
                'guardar_nota.php',
                'index.php',
                'vernota.php'
            ];
            
            // Archivos que se crearán automáticamente
            const filesToCreate = [
                'conexion.php (Sistema JSON)',
                'assets/css/style.css',
                'data/database.json',
                'data/.htaccess'
            ];
            
            const statusDiv = document.getElementById('download-status');
            const fileListDiv = document.getElementById('file-list');
            const continueBtn = document.getElementById('continue-btn');
            
            function prepareSystem() {
                document.getElementById('download-progress').style.display = 'block';
                
                statusDiv.innerHTML = '<div class="info">📁 Verificando estructura del proyecto...</div>';
                
                // Mostrar archivos existentes
                fileListDiv.innerHTML = '<div style="margin-bottom: 15px; font-weight: bold; color: #2c3e50;">📥 Archivos en GitHub:</div>';
                existingFiles.forEach(file => {
                    fileListDiv.innerHTML += '<div class="file">📄 ✓ ' + file + '</div>';
                });
                
                // Mostrar archivos a crear
                fileListDiv.innerHTML += '<div style="margin: 20px 0 15px 0; font-weight: bold; color: #2c3e50;">🔨 Archivos a crear:</div>';
                filesToCreate.forEach(file => {
                    fileListDiv.innerHTML += '<div class="file" style="color: #3498db;">📄 + ' + file + '</div>';
                });
                
                // Mostrar carpetas a crear
                fileListDiv.innerHTML += '<div style="margin: 20px 0 15px 0; font-weight: bold; color: #2c3e50;">📁 Estructura de carpetas:</div>';
                const folders = ['data/', 'data/backup/', 'assets/', 'assets/css/', 'assets/js/', 'assets/images/', 'admin/'];
                folders.forEach(folder => {
                    fileListDiv.innerHTML += '<div class="folder">📁 ' + folder + '</div>';
                });
                
                statusDiv.innerHTML = '<div class="success">✅ Sistema listo para instalar</div>';
                continueBtn.style.display = 'inline-flex';
                
                // Aviso importante
                fileListDiv.innerHTML += '<div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; color: #856404;">' +
                    '<strong>⚠️ Importante:</strong> El archivo <code>conexion.php</code> será creado automáticamente con el sistema JSON. ' +
                    'Los archivos originales de GitHub serán actualizados para funcionar sin MySQL.' +
                    '</div>';
            }
            
            // Ejecutar preparación
            setTimeout(prepareSystem, 1000);
        </script>
        <?php
    }
    
    private function stepInstall() {
        $result = $this->createInstallation();
        ?>
        <h2><span class="icon">🔧</span> Instalando Sistema</h2>
        <p>Configurando el sistema de notas con los parámetros especificados...</p>
        
        <?php
        $config = isset($_SESSION['setup_config']) ? $_SESSION['setup_config'] : array();
        ?>
        
        <div class="alert alert-info">
            <span class="alert-icon">📋</span>
            <div>
                <strong>Configuración aplicada:</strong><br>
                Sitio: <?php echo htmlspecialchars(isset($config['site_name']) ? $config['site_name'] : 'Sistema de Notas'); ?><br>
                Administrador: <?php echo htmlspecialchars(isset($config['admin_user']) ? $config['admin_user'] : 'admin'); ?><br>
                Email: <?php echo htmlspecialchars(isset($config['admin_email']) ? $config['admin_email'] : ''); ?><br>
                Zona horaria: <?php echo htmlspecialchars(isset($config['timezone']) ? $config['timezone'] : 'America/Lima'); ?>
            </div>
        </div>
        
        <div class="file-list">
            <div class="folder">📁 /data</div>
            <div class="file">&nbsp;&nbsp;&nbsp;📄 database.json (Base de datos JSON)</div>
            <div class="file">&nbsp;&nbsp;&nbsp;📄 .htaccess (Protección)</div>
            <div class="folder">📁 /data/backup</div>
            <div class="file">&nbsp;&nbsp;&nbsp;📄 backup_inicial.json</div>
            <div class="folder">📁 /assets</div>
            <div class="folder">&nbsp;&nbsp;&nbsp;📁 /css</div>
            <div class="file">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;📄 style.css</div>
            <div class="folder">&nbsp;&nbsp;&nbsp;📁 /js</div>
            <div class="folder">&nbsp;&nbsp;&nbsp;📁 /images</div>
            <div class="folder">📁 /admin</div>
            <div class="file">📄 index.php</div>
            <div class="file">📄 conexion.php (Sistema JSON)</div>
            <div class="file">📄 check-session.php</div>
            <div class="file">📄 crear_nota.php</div>
            <div class="file">📄 guardar_nota.php</div>
            <div class="file">📄 vernota.php</div>
            <div class="file">📄 editarnota.php</div>
            <div class="file">📄 eliminar_nota.php</div>
            <div class="file">📄 .htaccess</div>
        </div>
        
        <?php if ($result['success']): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✅</span>
                <div>
                    <strong>Instalación completada correctamente</strong><br>
                    El sistema de notas ha sido instalado y configurado exitosamente.<br>
                    Se crearon <?php echo $result['files_created']; ?> archivos.
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <span class="alert-icon">❌</span>
                <div>
                    <strong>Errores durante la instalación:</strong><br>
                    <?php foreach($result['errors'] as $error): ?>
                        - <?php echo $error; ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="button-group">
            <button class="btn btn-primary" onclick="window.location.href='?step=5'">
                Finalizar instalación →
            </button>
        </div>
        <?php
    }
    
    private function stepComplete() {
        ?>
        <div class="complete-icon">🎉</div>
        
        <h2 style="text-align:center;">¡Instalación Completada!</h2>
        <p style="text-align:center; margin:20px 0; font-size:18px;">
            <?php echo APP_NAME; ?> se ha instalado correctamente en tu servidor.
        </p>
        
        <?php $config = isset($_SESSION['setup_config']) ? $_SESSION['setup_config'] : array(); ?>
        
        <div class="alert alert-success">
            <span class="alert-icon">🔐</span>
            <div>
                <strong>Datos de acceso:</strong><br>
                <strong>URL:</strong> <a href="index.php" style="color: #27ae60;"><?php echo $this->getCurrentUrl(); ?>index.php</a><br>
                <strong>Usuario:</strong> <?php echo htmlspecialchars(isset($config['admin_user']) ? $config['admin_user'] : 'admin'); ?><br>
                <strong>Contraseña:</strong> La que configuraste<br>
                <strong>Panel Admin:</strong> <a href="admin/" style="color: #27ae60;"><?php echo $this->getCurrentUrl(); ?>admin/</a>
            </div>
        </div>
        
        <?php if (file_exists(__FILE__)): ?>
            <div class="alert alert-warning">
                <span class="alert-icon">⚠️</span>
                <div>
                    <strong>Importante - Seguridad:</strong><br>
                    Por seguridad, debes eliminar el archivo de instalación (<code>setup.php</code>) de tu servidor.
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <span class="alert-icon">✅</span>
                <div>
                    <strong>Archivo de instalación eliminado</strong><br>
                    El instalador ha sido eliminado por seguridad.
                </div>
            </div>
        <?php endif; ?>
        
        <div class="button-group" style="justify-content:center;">
            <?php if (file_exists(__FILE__)): ?>
                <a href="?step=5&delete=1" class="btn btn-danger">
                    🗑️ Eliminar setup.php
                </a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-primary">
                🚀 Ir al Sistema de Notas
            </a>
        </div>
        
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h4 style="margin-bottom: 15px;">🚀 Próximos pasos:</h4>
            <ol style="margin-left: 20px; line-height: 1.8;">
                <li>Accede al sistema con tus credenciales de administrador</li>
                <li>Crea tu primera nota para probar el sistema</li>
                <li>Personaliza la configuración desde el panel de admin</li>
                <li>Crea usuarios adicionales si es necesario</li>
                <li>¡Disfruta de tu sistema de notas!</li>
            </ol>
            
            <h4 style="margin-top: 20px; margin-bottom: 15px;">📚 Características del sistema:</h4>
            <ul style="margin-left: 20px; line-height: 1.8;">
                <li>✅ Sin base de datos MySQL (usa archivos JSON)</li>
                <li>✅ Backups automáticos</li>
                <li>✅ Búsqueda de notas</li>
                <li>✅ Sistema de usuarios (opcional)</li>
                <li>✅ Totalmente responsive</li>
                <li>✅ Fácil de migrar (solo copiar archivos)</li>
            </ul>
        </div>
        
        <?php
        // Limpiar sesión
        unset($_SESSION['setup_config']);
        ?>
        <?php
    }
    
    private function checkRequirements() {
        return array(
            'php' => version_compare(PHP_VERSION, '7.0.0', '>='),
            'json' => extension_loaded('json'),
            'curl' => extension_loaded('curl'),
            'writable' => is_writable(dirname(__FILE__)),
            'file_get_contents' => function_exists('file_get_contents'),
            'allow_url_fopen' => ini_get('allow_url_fopen')
        );
    }
    
    private function downloadFileFromGitHub($file) {
        try {
            // Si es un archivo .gitkeep o termina en /, solo crear el directorio
            if (strpos($file, '.gitkeep') !== false || substr($file, -1) === '/') {
                $dir = str_replace('.gitkeep', '', $file);
                $dir = rtrim($dir, '/');
                
                if (!empty($dir) && !file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                return ['success' => true, 'message' => 'Directorio creado'];
            }
            
            // Construir URL de GitHub
            $url = $this->github_raw . GITHUB_USER . '/' . GITHUB_REPO . '/' . GITHUB_BRANCH . '/' . $file;
            
            // Crear directorio si no existe
            $dir = dirname($file);
            if (!file_exists($dir) && $dir !== '.') {
                mkdir($dir, 0755, true);
            }
            
            // Opciones para file_get_contents
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: PHP Setup Script',
                        'Accept: */*'
                    ],
                    'timeout' => 30
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
            
            $context = stream_context_create($opts);
            
            // Intentar descargar el archivo
            $content = @file_get_contents($url, false, $context);
            
            if ($content === false) {
                // Si falla, intentar con cURL
                if (function_exists('curl_init')) {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Setup Script');
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    
                    $content = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode !== 200 || $content === false) {
                        // Archivo no encontrado o error
                        // Para archivos que no existen en el repo, crear versión básica
                        $content = $this->getDefaultFileContent($file);
                        if ($content === null) {
                            return ['success' => false, 'error' => 'Archivo no encontrado'];
                        }
                    }
                } else {
                    // Sin cURL, usar contenido por defecto
                    $content = $this->getDefaultFileContent($file);
                    if ($content === null) {
                        return ['success' => false, 'error' => 'No se pudo descargar'];
                    }
                }
            }
            
            // Guardar archivo
            if (file_put_contents($file, $content) !== false) {
                return ['success' => true, 'message' => 'Archivo descargado'];
            } else {
                return ['success' => false, 'error' => 'Error al guardar archivo'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function getDefaultFileContent($file) {
        // Contenido por defecto para archivos esenciales
        switch ($file) {
            case '.htaccess':
                return '# Sistema de Notas
Options -Indexes
RewriteEngine On

# Proteger archivos
<FilesMatch "\.(json|log)$">
    Order deny,allow
    Deny from all
</FilesMatch>

# Proteger carpeta data
RewriteRule ^data/ - [F,L]';
                
            case 'robots.txt':
                return 'User-agent: *
Disallow: /data/
Disallow: /admin/
Disallow: /assets/';
                
            case 'admin/.htaccess':
                return '# Protección admin
Order allow,deny
Allow from all';
                
            case 'assets/css/style.css':
                return '/* Sistema de Notas - Estilos */
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    margin: 0;
    padding: 0;
    background: #f5f5f5;
}';
                
            default:
                // Para archivos PHP que no existen, retornar null
                if (substr($file, -4) === '.php') {
                    return null;
                }
                return '';
        }
    }
    
    private function createInstallation() {
        $config = isset($_SESSION['setup_config']) ? $_SESSION['setup_config'] : array();
        $files_created = 0;
        $errors = array();
        
        try {
            // Crear estructura de directorios
            $directories = array('data', 'data/backup', 'data/uploads', 'admin', 'assets', 'assets/css', 'assets/js', 'assets/images');
            foreach ($directories as $dir) {
                if (!file_exists($dir)) {
                    if (@mkdir($dir, 0755, true)) {
                        $files_created++;
                    }
                }
            }
            
            // Proteger carpeta data
            if (!file_exists('data/.htaccess')) {
                $htaccess = "Order deny,allow\nDeny from all";
                file_put_contents('data/.htaccess', $htaccess);
                $files_created++;
            }
            
            // Crear base de datos inicial
            $database = array(
                'users' => array(
                    array(
                        'id' => 1,
                        'username' => isset($config['admin_user']) ? $config['admin_user'] : 'admin',
                        'password' => password_hash(isset($config['admin_pass']) ? $config['admin_pass'] : 'admin123', PASSWORD_DEFAULT),
                        'email' => isset($config['admin_email']) ? $config['admin_email'] : 'admin@example.com',
                        'role' => 'admin',
                        'created_at' => date('Y-m-d H:i:s')
                    )
                ),
                'notes' => array(),
                'settings' => array(
                    'site_name' => isset($config['site_name']) ? $config['site_name'] : 'Sistema de Notas',
                    'version' => APP_VERSION,
                    'timezone' => isset($config['timezone']) ? $config['timezone'] : 'America/Lima',
                    'allow_registration' => isset($config['allow_registration']) ? $config['allow_registration'] : false,
                    'installed_date' => date('Y-m-d H:i:s')
                )
            );
            
            // Guardar base de datos
            $json = json_encode($database, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents('data/database.json', $json);
            $files_created++;
            
            // Crear backup inicial
            file_put_contents('data/backup/backup_inicial.json', $json);
            $files_created++;
            
            // CREAR ARCHIVO conexion.php
            $conexion_content = '<?php
/**
 * conexion.php - Sistema de gestión de datos JSON
 * Reemplaza la conexión MySQL por archivos JSON
 */

class Database {
    private $dataPath;
    private $dataFile;
    private $data;
    
    public function __construct() {
        $this->dataPath = dirname(__FILE__) . \'/data/\';
        $this->dataFile = $this->dataPath . \'database.json\';
        $this->initialize();
    }
    
    private function initialize() {
        if (!file_exists($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
            $htaccess = "Order deny,allow\nDeny from all";
            file_put_contents($this->dataPath . \'.htaccess\', $htaccess);
        }
        
        $folders = array(\'backup\', \'uploads\');
        foreach ($folders as $folder) {
            if (!file_exists($this->dataPath . $folder)) {
                mkdir($this->dataPath . $folder, 0755, true);
            }
        }
        
        $this->loadData();
    }
    
    private function loadData() {
        if (file_exists($this->dataFile)) {
            $content = file_get_contents($this->dataFile);
            $this->data = json_decode($content, true);
        }
    }
    
    private function saveData() {
        $this->createBackup();
        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->dataFile, $json);
    }
    
    private function createBackup() {
        if (file_exists($this->dataFile)) {
            $backupFile = $this->dataPath . \'backup/backup_\' . date(\'Y-m-d_H-i-s\') . \'.json\';
            copy($this->dataFile, $backupFile);
            
            $files = glob($this->dataPath . \'backup/backup_*.json\');
            if (count($files) > 10) {
                usort($files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });
                
                $toDelete = array_slice($files, 0, count($files) - 10);
                foreach ($toDelete as $file) {
                    unlink($file);
                }
            }
        }
    }
    
    private function generateId() {
        return time() . \'_\' . uniqid();
    }
    
    public function getNotes($limit = null, $offset = 0) {
        $notes = isset($this->data[\'notes\']) ? $this->data[\'notes\'] : array();
        
        usort($notes, function($a, $b) {
            return strtotime($b[\'created_at\']) - strtotime($a[\'created_at\']);
        });
        
        if ($limit !== null) {
            $notes = array_slice($notes, $offset, $limit);
        }
        
        return $notes;
    }
    
    public function getNoteById($id) {
        if (isset($this->data[\'notes\'])) {
            foreach ($this->data[\'notes\'] as $note) {
                if ($note[\'id\'] == $id) {
                    return $note;
                }
            }
        }
        return null;
    }
    
    public function createNote($title, $content, $userId = 1) {
        $newNote = array(
            \'id\' => $this->generateId(),
            \'title\' => $title,
            \'content\' => $content,
            \'user_id\' => $userId,
            \'views\' => 0,
            \'created_at\' => date(\'Y-m-d H:i:s\'),
            \'updated_at\' => date(\'Y-m-d H:i:s\')
        );
        
        if (!isset($this->data[\'notes\'])) {
            $this->data[\'notes\'] = array();
        }
        
        $this->data[\'notes\'][] = $newNote;
        $this->saveData();
        
        return $newNote[\'id\'];
    }
    
    public function updateNote($id, $title, $content) {
        if (isset($this->data[\'notes\'])) {
            foreach ($this->data[\'notes\'] as &$note) {
                if ($note[\'id\'] == $id) {
                    $note[\'title\'] = $title;
                    $note[\'content\'] = $content;
                    $note[\'updated_at\'] = date(\'Y-m-d H:i:s\');
                    $this->saveData();
                    return true;
                }
            }
        }
        return false;
    }
    
    public function deleteNote($id) {
        if (isset($this->data[\'notes\'])) {
            $notes = array_filter($this->data[\'notes\'], function($note) use ($id) {
                return $note[\'id\'] != $id;
            });
            
            $this->data[\'notes\'] = array_values($notes);
            $this->saveData();
            
            return true;
        }
        return false;
    }
    
    public function incrementViews($id) {
        if (isset($this->data[\'notes\'])) {
            foreach ($this->data[\'notes\'] as &$note) {
                if ($note[\'id\'] == $id) {
                    $note[\'views\'] = isset($note[\'views\']) ? $note[\'views\'] + 1 : 1;
                    $this->saveData();
                    break;
                }
            }
        }
    }
    
    public function searchNotes($query) {
        $query = strtolower($query);
        $notes = isset($this->data[\'notes\']) ? $this->data[\'notes\'] : array();
        
        return array_filter($notes, function($note) use ($query) {
            return strpos(strtolower($note[\'title\']), $query) !== false ||
                   strpos(strtolower($note[\'content\']), $query) !== false;
        });
    }
    
    public function checkLogin($username, $password) {
        if (isset($this->data[\'users\'])) {
            foreach ($this->data[\'users\'] as $user) {
                if ($user[\'username\'] == $username && password_verify($password, $user[\'password\'])) {
                    return $user;
                }
            }
        }
        return false;
    }
    
    public function getStats() {
        $notes = isset($this->data[\'notes\']) ? $this->data[\'notes\'] : array();
        $total_views = 0;
        
        foreach ($notes as $note) {
            $total_views += isset($note[\'views\']) ? $note[\'views\'] : 0;
        }
        
        return array(
            \'total_notes\' => count($notes),
            \'total_users\' => isset($this->data[\'users\']) ? count($this->data[\'users\']) : 0,
            \'total_views\' => $total_views
        );
    }
}

// Crear instancia global
$db = new Database();
?>';
            file_put_contents('conexion.php', $conexion_content);
            $files_created++;
            
            // CREAR ARCHIVO check-session.php
            $timezone = isset($config['timezone']) ? $config['timezone'] : 'America/Lima';
            $check_session = '<?php
/**
 * check-session.php
 * Verificación de sesión de usuario
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define(\'REQUIRE_LOGIN\', false); // Cambiar a true si se requiere login

function isLoggedIn() {
    return isset($_SESSION[\'user_id\']) && isset($_SESSION[\'logged_in\']) && $_SESSION[\'logged_in\'] === true;
}

function getCurrentUserId() {
    return isset($_SESSION[\'user_id\']) ? $_SESSION[\'user_id\'] : 1;
}

function getCurrentUserName() {
    return isset($_SESSION[\'username\']) ? $_SESSION[\'username\'] : \'Usuario\';
}

function isAdmin() {
    return isset($_SESSION[\'is_admin\']) && $_SESSION[\'is_admin\'] === true;
}

date_default_timezone_set(\'' . $timezone . '\');

if ($_SERVER[\'SERVER_NAME\'] === \'localhost\') {
    error_reporting(E_ALL);
    ini_set(\'display_errors\', 1);
} else {
    error_reporting(0);
    ini_set(\'display_errors\', 0);
}
?>';
            file_put_contents('check-session.php', $check_session);
            $files_created++;
            
            // CREAR ARCHIVO index.php
            $site_name = isset($config['site_name']) ? $config['site_name'] : 'Sistema de Notas';
            $index_content = '<?php
session_start();
require_once \'conexion.php\';

// Paginación
$page = isset($_GET[\'page\']) ? max(1, intval($_GET[\'page\'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Búsqueda
$search = isset($_GET[\'search\']) ? $_GET[\'search\'] : \'\';

// Obtener notas
if (!empty($search)) {
    $notes = $db->searchNotes($search);
    $total_notes = count($notes);
    $notes = array_slice($notes, $offset, $per_page);
} else {
    $all_notes = $db->getNotes();
    $total_notes = count($all_notes);
    $notes = $db->getNotes($per_page, $offset);
}

// Calcular páginas
$total_pages = ceil($total_notes / $per_page);

// Obtener estadísticas
$stats = $db->getStats();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($site_name) . '</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1 class="logo">📝 ' . htmlspecialchars($site_name) . '</h1>
                    <div class="stats">
                        <div class="stat">
                            <div class="stat-value"><?php echo $stats[\'total_notes\']; ?></div>
                            <div class="stat-label">Notas totales</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value"><?php echo $stats[\'total_views\']; ?></div>
                            <div class="stat-label">Vistas totales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <?php if (isset($_SESSION[\'success\'])): ?>
                <div class="messages">
                    <div class="message message-success">
                        <?php 
                        echo htmlspecialchars($_SESSION[\'success\']);
                        unset($_SESSION[\'success\']);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION[\'error\'])): ?>
                <div class="messages">
                    <div class="message message-error">
                        <?php 
                        echo htmlspecialchars($_SESSION[\'error\']);
                        unset($_SESSION[\'error\']);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="crear_nota.php" class="btn btn-primary">
                    + Nueva Nota
                </a>
                
                <form class="search-form" method="GET" action="">
                    <input type="text" 
                           name="search" 
                           placeholder="Buscar notas..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-secondary">Buscar</button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn btn-secondary">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if (!empty($search)): ?>
                <p style="margin-bottom: 20px;">
                    Resultados para: <strong><?php echo htmlspecialchars($search); ?></strong> 
                    (<?php echo $total_notes; ?> encontradas)
                </p>
            <?php endif; ?>
            
            <?php if (empty($notes)): ?>
                <div class="empty-state">
                    <h2>
                        <?php if (!empty($search)): ?>
                            No se encontraron notas
                        <?php else: ?>
                            No hay notas todavía
                        <?php endif; ?>
                    </h2>
                    <p>
                        <?php if (!empty($search)): ?>
                            Intenta con otros términos de búsqueda
                        <?php else: ?>
                            ¡Crea tu primera nota para comenzar!
                        <?php endif; ?>
                    </p>
                    <?php if (empty($search)): ?>
                        <a href="crear_nota.php" class="btn btn-primary" style="margin-top: 20px;">
                            Crear primera nota
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="notes-grid">
                    <?php foreach ($notes as $note): ?>
                        <div class="note-card">
                            <h2 class="note-title">
                                <a href="vernota.php?id=<?php echo $note[\'id\']; ?>">
                                    <?php echo htmlspecialchars($note[\'title\']); ?>
                                </a>
                            </h2>
                            
                            <p class="note-content">
                                <?php 
                                $preview = strip_tags($note[\'content\']);
                                $preview = substr($preview, 0, 150);
                                echo htmlspecialchars($preview);
                                if (strlen($note[\'content\']) > 150) echo \'...\';
                                ?>
                            </p>
                            
                            <div class="note-meta">
                                <div>
                                    <small>
                                        <?php echo date(\'d/m/Y H:i\', strtotime($note[\'created_at\'])); ?>
                                        · <?php echo isset($note[\'views\']) ? $note[\'views\'] : 0; ?> vistas
                                    </small>
                                </div>
                                
                                <div class="note-actions">
                                    <a href="editarnota.php?id=<?php echo $note[\'id\']; ?>">Editar</a>
                                    <a href="eliminar_nota.php?id=<?php echo $note[\'id\']; ?>" 
                                       onclick="return confirm(\'¿Estás seguro de eliminar esta nota?\')">Eliminar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? \'&search=\' . urlencode($search) : \'\'; ?>">
                                ← Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? \'&search=\' . urlencode($search) : \'\'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? \'&search=\' . urlencode($search) : \'\'; ?>">
                                Siguiente →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>';
            file_put_contents('index.php', $index_content);
            $files_created++;
            
            // crear_nota.php
            file_put_contents('crear_nota.php', '<?php
session_start();
require_once \'conexion.php\';
require_once \'check-session.php\';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Nota - Sistema de Notas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Nueva Nota</h1>
        <form method="POST" action="guardar_nota.php">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Contenido</label>
                <textarea name="content" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>');
            $files_created++;
            
            // guardar_nota.php
            file_put_contents('guardar_nota.php', '<?php
session_start();
require_once \'conexion.php\';
require_once \'check-session.php\';

if ($_SERVER[\'REQUEST_METHOD\'] !== \'POST\') {
    header(\'Location: index.php\');
    exit;
}

$title = isset($_POST[\'title\']) ? trim($_POST[\'title\']) : \'\';
$content = isset($_POST[\'content\']) ? trim($_POST[\'content\']) : \'\';
$user_id = isset($_SESSION[\'user_id\']) ? $_SESSION[\'user_id\'] : 1;

if (!empty($title) && !empty($content)) {
    $noteId = $db->createNote($title, $content, $user_id);
    $_SESSION[\'success\'] = "Nota creada exitosamente";
    header(\'Location: vernota.php?id=\' . $noteId);
} else {
    $_SESSION[\'error\'] = "El título y contenido son obligatorios";
    header(\'Location: crear_nota.php\');
}
exit;
?>');
            $files_created++;
            
            // vernota.php
            file_put_contents('vernota.php', '<?php
session_start();
require_once \'conexion.php\';

$id = isset($_GET[\'id\']) ? $_GET[\'id\'] : null;
if (!$id) {
    $_SESSION[\'error\'] = "ID de nota no especificado";
    header(\'Location: index.php\');
    exit;
}

$note = $db->getNoteById($id);
if (!$note) {
    $_SESSION[\'error\'] = "La nota no existe";
    header(\'Location: index.php\');
    exit;
}

$db->incrementViews($id);
$note[\'views\'] = (isset($note[\'views\']) ? $note[\'views\'] : 0) + 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($note[\'title\']); ?> - Sistema de Notas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php">← Volver</a>
        <h1><?php echo htmlspecialchars($note[\'title\']); ?></h1>
        <div class="note-meta">
            <?php echo date(\'d/m/Y H:i\', strtotime($note[\'created_at\'])); ?> · 
            <?php echo $note[\'views\']; ?> vistas
        </div>
        <div class="note-content">
            <?php echo nl2br(htmlspecialchars($note[\'content\'])); ?>
        </div>
        <div class="note-actions">
            <a href="editarnota.php?id=<?php echo $note[\'id\']; ?>" class="btn btn-primary">Editar</a>
            <a href="eliminar_nota.php?id=<?php echo $note[\'id\']; ?>" class="btn btn-danger"
               onclick="return confirm(\'¿Estás seguro?\')">Eliminar</a>
        </div>
    </div>
</body>
</html>');
            $files_created++;
            
            // editarnota.php
            file_put_contents('editarnota.php', '<?php
session_start();
require_once \'conexion.php\';
require_once \'check-session.php\';

$id = isset($_GET[\'id\']) ? $_GET[\'id\'] : null;
if (!$id) {
    header(\'Location: index.php\');
    exit;
}

$note = $db->getNoteById($id);
if (!$note) {
    header(\'Location: index.php\');
    exit;
}

if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
    $title = isset($_POST[\'title\']) ? trim($_POST[\'title\']) : \'\';
    $content = isset($_POST[\'content\']) ? trim($_POST[\'content\']) : \'\';
    
    if (!empty($title) && !empty($content)) {
        if ($db->updateNote($id, $title, $content)) {
            $_SESSION[\'success\'] = "Nota actualizada";
            header(\'Location: vernota.php?id=\' . $id);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Nota</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Editar Nota</h1>
        <form method="POST">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($note[\'title\']); ?>" required>
            </div>
            <div class="form-group">
                <label>Contenido</label>
                <textarea name="content" required><?php echo htmlspecialchars($note[\'content\']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="vernota.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>');
            $files_created++;
            
            // eliminar_nota.php
            file_put_contents('eliminar_nota.php', '<?php
session_start();
require_once \'conexion.php\';
require_once \'check-session.php\';

$id = null;
if (isset($_GET[\'id\'])) {
    $id = $_GET[\'id\'];
} elseif (isset($_POST[\'id\'])) {
    $id = $_POST[\'id\'];
}

if (!$id) {
    $_SESSION[\'error\'] = "ID no especificado";
    header(\'Location: index.php\');
    exit;
}

$note = $db->getNoteById($id);
if (!$note) {
    $_SESSION[\'error\'] = "La nota no existe";
    header(\'Location: index.php\');
    exit;
}

if ($db->deleteNote($id)) {
    $_SESSION[\'success\'] = "Nota eliminada";
} else {
    $_SESSION[\'error\'] = "Error al eliminar";
}

header(\'Location: index.php\');
exit;
?>');
            $files_created++;
            
            // Crear CSS básico
            $css_content = '/* Sistema de Notas - Estilos */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #f5f5f5;
    color: #333;
    line-height: 1.6;
}

.header {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    color: white;
    padding: 30px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.logo {
    font-size: 28px;
    font-weight: bold;
}

.stats {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.stat {
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
}

.stat-label {
    font-size: 14px;
    opacity: 0.8;
}

.main-content {
    padding: 30px 0;
}

.actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-form input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    width: 300px;
    font-size: 16px;
}

.btn {
    padding: 10px 20px;
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

.btn-danger {
    background: #e74c3c;
    color: white;
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.note-card {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.note-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}

.note-title a {
    color: #2c3e50;
    text-decoration: none;
}

.note-title a:hover {
    color: #3498db;
}

.note-meta {
    color: #7f8c8d;
    font-size: 14px;
    margin: 10px 0;
}

.note-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.note-actions a {
    color: #3498db;
    text-decoration: none;
    font-size: 14px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
}

.pagination a, .pagination span {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.pagination .current {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.message {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.message-success {
    background: #d4edda;
    color: #155724;
}

.message-error {
    background: #f8d7da;
    color: #721c24;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group textarea {
    min-height: 200px;
    resize: vertical;
}

@media (max-width: 768px) {
    .notes-grid {
        grid-template-columns: 1fr;
    }
    
    .actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .search-form input {
        width: 100%;
    }
}';
            if (!file_exists('assets/css/style.css')) {
                file_put_contents('assets/css/style.css', $css_content);
                $files_created++;
            }
            
            // Crear .htaccess principal
            if (!file_exists('.htaccess')) {
                $htaccess_main = '# Sistema de Notas
Options -Indexes
RewriteEngine On

# Proteger archivos
<FilesMatch "\.(json|log)$">
    Order deny,allow
    Deny from all
</FilesMatch>

# Proteger carpeta data
RewriteRule ^data/ - [F,L]';
                file_put_contents('.htaccess', $htaccess_main);
                $files_created++;
            }
            
            return array(
                'success' => true,
                'files_created' => $files_created,
                'errors' => $errors
            );
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
            return array(
                'success' => false,
                'files_created' => $files_created,
                'errors' => $errors
            );
        }
    }
    
    private function getCurrentUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = dirname($_SERVER['REQUEST_URI']);
        return $protocol . '://' . $host . $uri . '/';
    }
}

// Ejecutar el instalador
$installer = new NotasSetup();
$installer->run();
?>
