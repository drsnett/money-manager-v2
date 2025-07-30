# Guía de Despliegue en Servidor

## Money Manager v2.0 - Instrucciones para Servidor

### ⚠️ Problemas Comunes y Soluciones

#### Error: "SQLSTATE[HY000] [14] unable to open database file"

**Causa:** Rutas hardcodeadas y permisos incorrectos.

**Solución aplicada:**
- ✅ Corregidas rutas hardcodeadas en `check_migrations.php`
- ✅ Configuración de permisos automática en `deploy.php`

### 📋 Pasos para Despliegue

#### 1. Preparación del Servidor

**Requisitos mínimos:**
```bash
# Verificar versión PHP
php -v  # Debe ser 7.4 o superior

# Verificar extensiones requeridas
php -m | grep -E "pdo|sqlite|mbstring|openssl|json"
```

**Extensiones PHP requeridas:**
- pdo
- pdo_sqlite
- mbstring
- openssl
- json

#### 2. Subir Archivos al Servidor

**Archivos a subir:**
- Todo el contenido del proyecto
- **IMPORTANTE:** No subir archivos `.env*` desde desarrollo

**Archivos a excluir:**
- `.git/`
- `node_modules/`
- `composer.json` y `composer.lock` (opcional)
- Archivos de prueba (`test_*.php`, `debug.php`, etc.)

#### 3. Configuración de Permisos

```bash
# Permisos de directorios
chmod 755 data/
chmod 755 logs/
chmod 755 uploads/
chmod 755 backups/
chmod 755 data/cache/

# Permisos de archivos
chmod 644 data/money_manager.db
chmod 644 .env.local
```

#### 4. Configuración de Entorno

```bash
# Copiar configuración de producción
cp .env.production .env.local

# Editar variables específicas del servidor
nano .env.local
```

**Variables críticas a configurar:**
```env
# Base de datos
DB_PATH=./data/money_manager.db

# URLs del sistema
SYSTEM_URL=https://tu-dominio.com
BASE_URL=https://tu-dominio.com

# Email (si aplica)
SMTP_HOST=tu-servidor-smtp
SMTP_USERNAME=tu-email
SMTP_PASSWORD=tu-password

# WhatsApp (si aplica)
WHATSAPP_API_KEY=tu-api-key
WHATSAPP_PHONE=tu-numero
```

#### 5. Ejecutar Script de Despliegue

```bash
# Desde línea de comandos
php deploy.php

# O desde navegador (solo con permisos admin)
https://tu-dominio.com/deploy.php?admin_deploy=1
```

**El script automáticamente:**
- ✅ Crea directorios necesarios
- ✅ Configura permisos
- ✅ Copia configuración de producción
- ✅ Verifica extensiones PHP
- ✅ Inicializa base de datos
- ✅ Ejecuta migraciones
- ✅ Configura protecciones de seguridad

#### 6. Verificación Post-Despliegue

**Verificar funcionamiento:**
```bash
# Verificar base de datos
php -r "require 'config/database.php'; echo 'DB OK';"

# Verificar permisos
ls -la data/ logs/ uploads/

# Probar aplicación
curl https://tu-dominio.com/
```

**Accesos importantes:**
- Sistema: `https://tu-dominio.com/`
- Documentación: `https://tu-dominio.com/documentation.php`
- Admin: `https://tu-dominio.com/admin/`

### 🔧 Configuraciones Adicionales

#### Tareas Programadas (Cron)

**⚠️ IMPORTANTE:** El archivo `cron/setup_cron.bat` contiene rutas hardcodeadas de XAMPP.

**Para Linux/Unix, crear crontab:**
```bash
# Editar crontab
crontab -e

# Agregar tareas (ajustar rutas según tu servidor)
0 * * * * /usr/bin/php /ruta/completa/al/proyecto/scripts/generate_notifications.php
0 2 * * * /usr/bin/php /ruta/completa/al/proyecto/console.php cache:clear
0 3 * * * /usr/bin/php /ruta/completa/al/proyecto/console.php db:backup
```

#### SSL/HTTPS

**Configurar redirección en .htaccess:**
```apache
# Forzar HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Backup Automático

```bash
# Configurar backup diario
echo "0 4 * * * /usr/bin/php /ruta/al/proyecto/console.php db:backup" | crontab -
```

### 🚨 Solución de Problemas

#### Error de Base de Datos
```bash
# Verificar permisos
ls -la data/money_manager.db

# Recrear base de datos si es necesario
rm data/money_manager.db
php config/database.php
```

#### Error de Permisos
```bash
# Corregir permisos recursivamente
chown -R www-data:www-data .
chmod -R 755 data/ logs/ uploads/
```

#### Error de Configuración
```bash
# Verificar configuración
php -r "require 'config/env_config.php'; print_r($_ENV);"
```

### 📞 Soporte

Para problemas específicos, consultar:
- [docs/RESOLUCION_PROBLEMAS.md](docs/RESOLUCION_PROBLEMAS.md)
- [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)
- [README_PRODUCTION.md](README_PRODUCTION.md)

---

**Fecha de creación:** 30 de Julio de 2025  
**Versión:** 2.0  
**Estado:** Probado y funcional ✅