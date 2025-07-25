# Sistema de Notas con JSON

## 🚀 Conversión de MySQL a JSON

Este sistema ha sido convertido para usar archivos JSON en lugar de base de datos MySQL. Todos los datos se almacenan en la carpeta `/data` de forma segura.

## 📁 Estructura de archivos

```
sistema-notas/
├── data/                    # Carpeta de datos (se crea automáticamente)
│   ├── .htaccess           # Protección de la carpeta
│   ├── database.json       # Base de datos principal
│   ├── backup/             # Copias de seguridad automáticas
│   └── uploads/            # Archivos subidos
├── admin/                  # Panel de administración (existente)
├── assets/                 # Recursos (CSS, JS, imágenes)
├── conexion.php           # ✅ MODIFICADO - Gestor de datos JSON
├── check-session.php      # ✅ MODIFICADO - Verificación de sesión
├── crear_nota.php         # ✅ MODIFICADO - Formulario de creación
├── guardar_nota.php       # ✅ MODIFICADO - Guardar notas
├── editarnota.php         # ✅ MODIFICADO - Editar notas
├── eliminar_nota.php      # ✅ MODIFICADO - Eliminar notas
├── vernota.php            # ✅ MODIFICADO - Ver nota individual
├── index.php              # ✅ MODIFICADO - Listado principal
└── README.md              # Este archivo
```

## 🔧 Instalación

### 1. Requisitos
- PHP 7.0 o superior
- Servidor web (Apache/Nginx)
- Permisos de escritura en el servidor

### 2. Pasos de instalación

1. **Subir los archivos** al servidor
2. **Dar permisos de escritura** a la carpeta raíz:
   ```bash
   chmod 755 .
   chmod -R 777 data/  # Se creará automáticamente
   ```

3. **Acceder al sistema** desde el navegador:
   ```
   http://tudominio.com/
   ```

4. **¡Listo!** El sistema creará automáticamente:
   - La carpeta `/data`
   - El archivo `database.json` con datos iniciales
   - Las carpetas de backup y uploads
   - El archivo `.htaccess` de protección

## 📝 Uso del sistema

### Crear una nota
1. Click en "Nueva Nota" desde la página principal
2. Escribir título y contenido
3. Click en "Guardar Nota"

### Editar una nota
1. Click en una nota para verla
2. Click en "Editar"
3. Modificar y guardar

### Eliminar una nota
1. Click en "Eliminar" desde el listado o vista de nota
2. Confirmar la eliminación

### Buscar notas
- Usar el buscador en la página principal
- Busca en títulos y contenido

## 🔐 Seguridad

### Protección de datos
- La carpeta `/data` está protegida con `.htaccess`
- Los archivos JSON no son accesibles desde el navegador
- Se crean backups automáticos antes de cada modificación

### Sin necesidad de MySQL
- ✅ No requiere configuración de base de datos
- ✅ No hay riesgos de inyección SQL
- ✅ Portabilidad total (solo copiar archivos)

## 💾 Estructura de datos

### database.json
```json
{
    "users": [
        {
            "id": 1,
            "username": "admin",
            "password": "$2y$10$...",
            "email": "admin@example.com"
        }
    ],
    "notes": [
        {
            "id": "1234567890_abc123",
            "title": "Mi primera nota",
            "content": "Contenido de la nota...",
            "user_id": 1,
            "views": 42,
            "created_at": "2024-01-15 10:30:00",
            "updated_at": "2024-01-15 14:45:00"
        }
    ],
    "settings": {
        "site_name": "Sistema de Notas",
        "version": "2.0"
    }
}
```

## 🔄 Backups

### Automáticos
- Se crea un backup antes de cada modificación
- Se mantienen los últimos 10 backups
- Ubicación: `/data/backup/`

### Manuales
Para hacer un backup manual:
1. Copiar el archivo `/data/database.json`
2. Guardarlo en un lugar seguro

### Restaurar backup
1. Ir a la carpeta `/data/backup/`
2. Elegir el archivo de backup deseado
3. Copiarlo y renombrarlo como `database.json`
4. Reemplazar el archivo actual en `/data/`

## ⚙️ Configuración

### Cambiar configuración del sistema
Editar el archivo `conexion.php`:

```php
// Número de backups a mantener
$maxBackups = 10;

// Notas por página
$notesPerPage = 10;
```

### Habilitar/deshabilitar login
En `check-session.php`:

```php
define('REQUIRE_LOGIN', false); // true para requerir login
```

## 🚨 Solución de problemas

### "No se puede crear la carpeta data"
```bash
# Dar permisos al directorio
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

### "No se guardan las notas"
1. Verificar permisos de la carpeta `/data`
2. Verificar que PHP tenga permisos de escritura
3. Revisar logs del servidor

### "Perdí mis datos"
1. Revisar la carpeta `/data/backup/`
2. Buscar el backup más reciente
3. Restaurar según instrucciones anteriores

## 🎯 Ventajas del sistema JSON

1. **Sin base de datos**: No requiere MySQL/MariaDB
2. **Portabilidad**: Solo copiar archivos para migrar
3. **Simplicidad**: Fácil de entender y mantener
4. **Backups fáciles**: Solo copiar archivos JSON
5. **Sin configuración**: Funciona inmediatamente
6. **Seguridad**: Sin riesgos de inyección SQL

## 📱 Características

- ✅ Crear, editar y eliminar notas
- ✅ Búsqueda de notas
- ✅ Contador de vistas
- ✅ Paginación
- ✅ Backups automáticos
- ✅ Responsive (móviles y tablets)
- ✅ Sin necesidad de base de datos
- ✅ Instalación en 1 minuto

## 🤝 Soporte

Si necesitas ayuda:
1. Revisa este README
2. Verifica los permisos de archivos
3. Revisa los logs del servidor
4. Contacta al desarrollador

---

**Versión**: 2.0 (JSON)  
**Actualizado**: Enero 2024  
**Sin MySQL**: ✅ Completamente basado en archivos JSON
