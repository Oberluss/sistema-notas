# 📝 Sistema de Notas

Un sistema web simple y eficiente para gestionar notas personales, sin necesidad de base de datos MySQL. Utiliza archivos JSON para almacenar toda la información.

## 🌟 Características Principales

- **Sin Base de Datos MySQL**: Utiliza archivos JSON para almacenar datos
- **Instalación Automática**: Setup en 5 pasos simples
- **Interfaz Moderna**: Diseño responsive y atractivo
- **Búsqueda Integrada**: Encuentra tus notas rápidamente
- **Backups Automáticos**: Crea copias de seguridad automáticamente
- **Sistema de Usuarios**: Gestión de usuarios con roles (opcional)
- **Estadísticas**: Visualiza el total de notas y vistas
- **Totalmente en Español**: Interfaz completamente en español

## 📋 Requisitos del Sistema

- **PHP 7.0** o superior
- **Extensión JSON** habilitada
- **Extensión cURL** habilitada (opcional pero recomendada)
- **Permisos de escritura** en el directorio de instalación
- **allow_url_fopen** habilitado

## 🚀 Instalación Rápida

### Opción 1: Instalación Automática (Recomendada)

1. Descarga el archivo `setup.php` del repositorio
2. Súbelo a tu servidor web
3. Accede desde tu navegador: `http://tudominio.com/setup.php`
4. Sigue los 5 pasos del instalador:
   - **Paso 1**: Verificación de requisitos
   - **Paso 2**: Configuración del sistema
   - **Paso 3**: Preparación de archivos
   - **Paso 4**: Instalación
   - **Paso 5**: Finalización
5. ¡Listo! Tu sistema de notas está instalado

### Opción 2: Instalación Manual

1. Clona o descarga el repositorio:
   ```bash
   git clone https://github.com/Oberluss/sistema-notas.git
   ```

2. Sube los archivos a tu servidor

3. Crea las siguientes carpetas con permisos 755:
   ```
   /data/
   /data/backup/
   /assets/css/
   /assets/js/
   /assets/images/
   /admin/
   ```

4. Crea el archivo `data/database.json` con la estructura inicial:
   ```json
   {
     "users": [{
       "id": 1,
       "username": "admin",
       "password": "$2y$10$...",
       "email": "admin@example.com",
       "role": "admin",
       "created_at": "2024-01-01 00:00:00"
     }],
     "notes": [],
     "settings": {
       "site_name": "Sistema de Notas",
       "version": "2.0",
       "timezone": "America/Lima"
     }
   }
   ```

## 📖 Uso del Sistema

### Acceso Principal
- URL principal: `http://tudominio.com/`
- Panel de administración: `http://tudominio.com/admin/`

### Funciones Básicas

1. **Crear una nota**:
   - Haz clic en "+ Nueva Nota"
   - Ingresa título y contenido
   - Guarda la nota

2. **Buscar notas**:
   - Usa la barra de búsqueda
   - Busca por título o contenido

3. **Editar/Eliminar**:
   - Cada nota tiene opciones para editar o eliminar
   - Confirma antes de eliminar

### Gestión de Usuarios (Opcional)

Si habilitaste el sistema de usuarios durante la instalación:
- Accede al panel admin con tus credenciales
- Crea nuevos usuarios
- Gestiona permisos

## 📁 Estructura de Archivos

```
sistema-notas/
├── index.php              # Página principal
├── conexion.php           # Sistema de gestión JSON
├── check-session.php      # Control de sesiones
├── crear_nota.php         # Formulario nueva nota
├── guardar_nota.php       # Procesa guardado
├── vernota.php           # Vista individual
├── editarnota.php        # Editar nota
├── eliminar_nota.php     # Eliminar nota
├── .htaccess             # Configuración Apache
├── data/                 # Datos del sistema
│   ├── database.json     # Base de datos principal
│   ├── backup/           # Copias de seguridad
│   └── .htaccess         # Protección
├── assets/               # Recursos
│   ├── css/              # Estilos
│   ├── js/               # JavaScript
│   └── images/           # Imágenes
└── admin/                # Panel administración
```

## 🎨 Personalización

### Cambiar el Nombre del Sitio

1. Edita `data/database.json`
2. Busca `"site_name": "Sistema de Notas"`
3. Cambia el valor por el nombre deseado

### Modificar Estilos

Los estilos están en `assets/css/style.css`. Puedes modificar:
- Colores principales
- Tamaños de fuente
- Espaciados
- Diseño responsive

### Zona Horaria

Para cambiar la zona horaria, edita `check-session.php`:
```php
date_default_timezone_set('America/Lima');
```

## 🔧 Configuración Avanzada

### Habilitar Login Obligatorio

En `check-session.php`, cambia:
```php
define('REQUIRE_LOGIN', true);
```

### Límite de Notas por Página

En `index.php`, modifica:
```php
$per_page = 10; // Cambia este valor
```

### Backups Automáticos

El sistema mantiene las últimas 10 copias de seguridad. Para cambiar este límite, edita `conexion.php`:
```php
if (count($files) > 10) { // Cambia el 10
```

## 🛠️ Solución de Problemas

### Error: "Requisitos no cumplidos"
- Verifica que tu servidor tenga PHP 7.0+
- Asegúrate que las extensiones JSON y cURL estén habilitadas
- Contacta a tu proveedor de hosting

### Error: "Sin permisos de escritura"
```bash
chmod 755 /ruta/a/tu/sitio
chmod -R 755 data/
```

### No puedo eliminar setup.php
- Elimínalo manualmente vía FTP
- O usa el administrador de archivos de tu hosting

### Las notas no se guardan
- Verifica permisos en la carpeta `data/`
- Asegúrate que `data/database.json` existe
- Revisa que no esté corrupto el archivo JSON

## 🔒 Seguridad

- **Elimina setup.php** después de la instalación
- La carpeta `data/` está protegida con `.htaccess`
- Las contraseñas se almacenan encriptadas
- No se permite acceso directo a archivos JSON

## 📱 Compatibilidad

- ✅ Chrome, Firefox, Safari, Edge
- ✅ Diseño responsive para móviles
- ✅ Tablets y escritorio
- ✅ PHP 7.0, 7.4, 8.0, 8.1, 8.2

## 🤝 Contribuir

Las contribuciones son bienvenidas:

1. Fork el proyecto
2. Crea tu rama de características (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Changelog

### Versión 2.0 (Actual)
- Sistema completo sin MySQL
- Instalador automático
- Backups automáticos
- Búsqueda mejorada
- Diseño responsive moderno

### Versión 1.0
- Sistema básico con MySQL
- Funciones CRUD básicas

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

- **GitHub**: [@Oberluss](https://github.com/Oberluss)
- **Proyecto**: [sistema-notas](https://github.com/Oberluss/sistema-notas)

## 🙏 Agradecimientos

- A todos los que han probado y mejorado el sistema
- A la comunidad de PHP por su excelente documentación
- A los usuarios que reportan bugs y sugieren mejoras

---

**¿Necesitas ayuda?** Abre un issue en GitHub o contacta al autor.
