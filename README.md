# Money Manager

Sistema avanzado de gestión financiera personal desarrollado en PHP con SQLite, que incluye notificaciones automáticas, sistema de caché y arquitectura modular.

## 🚀 Características Principales

### Gestión Financiera
- ✅ Gestión de transacciones (ingresos y gastos)
- ✅ Categorización avanzada de transacciones
- ✅ Cuentas por pagar y por cobrar con seguimiento de vencimientos
- ✅ Gestión completa de tarjetas de crédito con estados dinámicos
  - Estados automáticos: Pagada, Pendiente, Vencida, Activa
  - Cálculo inteligente basado en fechas de corte y pagos
  - Visualización con badges de colores e iconos
  - Personalización de colores por tarjeta
- ✅ Deudas con interés compuesto y cálculos automáticos
- ✅ Cuentas bancarias con balances
- ✅ Reportes y estadísticas avanzadas
- ✅ Dashboard interactivo con gráficos

### Sistema de Usuarios
- ✅ Autenticación segura con roles
- ✅ Perfiles de usuario personalizables
- ✅ Control de acceso granular
- ✅ Configuraciones por usuario

### 🔔 Sistema de Notificaciones (NUEVO)
- ✅ Notificaciones automáticas de vencimientos
- ✅ Alertas de límites de gastos
- ✅ Notificaciones en tiempo real
- ✅ Sistema de badges y contadores
- ✅ Notificaciones toast no intrusivas
- ✅ Prevención de duplicados con verificación temporal
- ✅ Compatibilidad completa con SQLite

### ⚡ Sistema de Caché (NUEVO)
- ✅ Caché dual: archivos y memoria (APCu)
- ✅ Funciones globales: `cache()` y `cache_remember()`
- ✅ Gestión automática de expiración y limpieza
- ✅ Estadísticas detalladas y monitoreo
- ✅ Manejo robusto de errores y archivos corruptos
- ✅ Operaciones numéricas: increment/decrement
- ✅ Patrón Singleton para acceso global

### 🔧 Infraestructura Avanzada (NUEVO)
- ✅ Sistema de migraciones de base de datos
- ✅ Consola CLI para tareas administrativas
- ✅ Gestión de variables de entorno
- ✅ Sistema de logs estructurado
- ✅ Backups automáticos

## 📋 Requisitos

- PHP 7.4 o superior
- SQLite 3
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, SQLite, JSON, APCu (opcional)

## 🛠️ Instalación

### Instalación Básica
1. Clona o descarga el proyecto
2. Configura tu servidor web para apuntar al directorio del proyecto
3. Accede a `install.php` para configurar la base de datos
4. Crea tu primer usuario administrador

### Configuración Avanzada
1. Copia `.env.example` a `.env` y configura las variables
2. Ejecuta las migraciones: `php console.php migrate`
3. Configura las tareas programadas (opcional):
   - Windows: Ejecuta `cron/setup_cron.bat` como administrador
   - Linux/Mac: Configura crontab manualmente

## 🎯 Uso

### Uso Básico
1. Inicia sesión con tu usuario
2. Configura las categorías de transacciones
3. Comienza a registrar tus transacciones
4. Utiliza los módulos de cuentas por pagar/cobrar según necesites
5. Revisa los reportes para analizar tus finanzas

### Funcionalidades Avanzadas
- **Notificaciones**: Recibe alertas automáticas de vencimientos y límites
- **Consola CLI**: Usa `php console.php` para tareas administrativas
- **Sistema de Caché**: Optimización automática del rendimiento con múltiples opciones
- **Migraciones**: Actualiza la base de datos con `php console.php migrate`

#### 🚀 Uso del Sistema de Caché

**Funciones Globales Disponibles:**

```php
// Función cache() - Uso básico
cache('mi_clave', 'mi_valor', 3600); // Guardar por 1 hora
$valor = cache('mi_clave');          // Obtener valor
$cache = cache();                    // Obtener instancia de caché

// Función cache_remember() - Caché con callback
$datos = cache_remember('datos_costosos', function() {
    // Operación costosa que solo se ejecuta si no está en caché
    return obtenerDatosDeLaAPI();
}, 1800); // 30 minutos
```

**Uso Avanzado con la Clase Cache:**

```php
$cache = new Cache();

// Operaciones básicas
$cache->set('clave', 'valor', 3600);
$valor = $cache->get('clave', 'valor_por_defecto');
$existe = $cache->has('clave');
$cache->delete('clave');

// Operaciones numéricas
$cache->set('contador', 10);
$nuevo = $cache->increment('contador', 5); // 15
$nuevo = $cache->decrement('contador', 3); // 12

// Estadísticas y mantenimiento
$stats = $cache->getStats();
$archivos_limpiados = $cache->clearExpired();
$cache->clear(); // Limpiar todo el caché
```

**Configuración del Caché:**

- **Directorio**: `data/cache/` (configurable)
- **TTL por defecto**: 3600 segundos (1 hora)
- **APCu**: Se usa automáticamente si está disponible
- **Estructura**: Archivos organizados en subdirectorios por hash

📖 **[Ver documentación completa del sistema de caché](docs/CACHE.md)**

#### 💳 Sistema de Tarjetas de Crédito

**Estados Dinámicos Automáticos:**

El sistema calcula automáticamente el estado de cada tarjeta basándose en:
- Fechas de corte y pago configuradas
- Balance al momento del corte
- Pagos realizados después del corte

**Estados Disponibles:**
- 🟢 **Pagada**: El balance del corte ha sido pagado completamente
- 🟡 **Pendiente**: Hay balance pendiente pero aún no ha vencido el plazo de pago
- 🔴 **Vencida**: Se pasó la fecha de pago sin cubrir el balance del corte
- 🔵 **Activa**: Tarjeta en uso normal sin balance de corte pendiente

**Funcionalidades:**
- Gestión completa de transacciones (cargos y pagos)
- Cálculo automático de utilización de crédito
- Personalización de colores por tarjeta
- Eliminación segura de transacciones con actualización de balances
- Visualización clara del estado con badges de colores

**Mejoras Técnicas Recientes:**
- ✅ Solucionado error `deleteTransaction is not defined`
- ✅ Implementado manejo global de IDs con `window.currentCardId`
- ✅ Corregido problema de accesibilidad `aria-hidden` en modales
- ✅ Eliminada duplicación de funciones JavaScript
- ✅ Mejorada la experiencia de usuario en eliminación de transacciones

## 📁 Estructura del Proyecto

```
/
├── classes/              # Clases PHP del sistema
│   ├── Cache.php         # Sistema de caché (dual: archivos + APCu)
│   ├── Migration.php     # Sistema de migraciones
│   ├── Notification.php  # Sistema de notificaciones
│   └── ...
├── config/               # Archivos de configuración
│   ├── config.php        # Configuración principal
│   └── env_config.php    # Gestión de variables de entorno
├── migrations/           # Migraciones de base de datos
├── scripts/              # Scripts de mantenimiento
├── ajax/                 # Endpoints AJAX
├── js/                   # Scripts JavaScript
│   └── notifications.js  # Cliente de notificaciones
├── logs/                 # Archivos de log
├── cache/                # Archivos de caché
├── backups/              # Backups de base de datos
├── cron/                 # Scripts para tareas programadas
├── data/                 # Base de datos SQLite
├── includes/             # Archivos incluidos (header, footer)
├── admin/                # Panel de administración
├── console.php           # Consola CLI
├── .env                  # Variables de entorno
└── *.php                 # Páginas principales
```

## 🚀 Despliegue en Producción

### 📋 Requisitos del Servidor
- PHP 7.4+ (recomendado PHP 8.0+)
- Apache 2.4+ o Nginx 1.18+
- Certificado SSL/TLS válido
- Extensiones PHP: `pdo`, `pdo_sqlite`, `json`, `mbstring`, `openssl`
- Opcionales: `zip`, `curl`, `gd`, `fileinfo`, `opcache`

### 🔧 Proceso de Despliegue Automatizado

**1. Preparar el Servidor:**
```bash
# Crear directorio y configurar permisos
sudo mkdir -p /var/www/money-manager
sudo chown -R www-data:www-data /var/www/money-manager
```

**2. Subir Archivos:**
- Subir todos los archivos del proyecto (excepto `.git` y `test_*.php`)
- Configurar permisos:
```bash
sudo chmod -R 755 /var/www/money-manager
sudo chmod -R 777 /var/www/money-manager/{data,logs,uploads,backups}
```

**3. Ejecutar Script de Despliegue:**
```bash
# Desde línea de comandos
cd /var/www/money-manager
php deploy.php

# O desde navegador (solo una vez)
https://tu-dominio.com/deploy.php?admin_deploy=1
```

**El script automáticamente:**
- ✅ Crea directorios necesarios
- ✅ Configura permisos correctos
- ✅ Inicializa base de datos y migraciones
- ✅ Configura entorno de producción
- ✅ Implementa medidas de seguridad
- ✅ Limpia archivos de desarrollo

**4. Configurar Backup y Monitoreo:**
```bash
# Agregar a crontab para backup diario
0 2 * * * /usr/bin/php /var/www/money-manager/backup.php auto clean

# Monitoreo cada 5 minutos
*/5 * * * * curl -s "https://tu-dominio.com/monitor.php?key=mm2024monitor" > /dev/null
```

### 🔐 Características de Producción
- **Backup Automático:** Sistema completo con limpieza de archivos antiguos
- **Monitoreo:** Verificación automática del sistema y base de datos
- **Seguridad Avanzada:** Protección de archivos, cabeceras HTTP seguras
- **SSL/HTTPS:** Redirección automática y configuración segura
- **Cache Optimizado:** Rendimiento mejorado para producción
- **Logs Estructurados:** Monitoreo y debugging avanzado

### 📖 Documentación Completa
- **Guía de Producción:** [README_PRODUCTION.md](README_PRODUCTION.md)
- **Checklist de Despliegue:** [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)
- **Configuración del Servidor:** [server-config.md](server-config.md)
- **Documentación Técnica:** `/admin/backend-documentation.php`
- **Resolución de Problemas:** [docs/RESOLUCION_PROBLEMAS.md](docs/RESOLUCION_PROBLEMAS.md)

## 🔒 Seguridad

- Autenticación robusta de usuarios
- Protección CSRF en todos los formularios
- Validación y sanitización exhaustiva de datos
- Control de acceso basado en roles
- Headers de seguridad configurados
- Protección contra inyección SQL
- Configuración SSL/TLS para producción
- Archivos sensibles protegidos con .htaccess
- Backup automático con encriptación
- Escape de HTML para prevenir XSS

## 🚀 Comandos de Consola

### 🔄 Migraciones de Base de Datos

El sistema incluye un sistema de migraciones para actualizar la estructura de la base de datos:

```bash
# Ejecutar todas las migraciones pendientes
php console.php migrate

# Ejecutar una migración específica
php migrations/nombre_migracion.php

# Revertir la última migración
php console.php migrate:rollback
```

**Migraciones Disponibles:**
- `2025_01_15_140000_add_status_to_credit_cards.php` - Agrega campo status a tarjetas de crédito
- `2025_01_16_120000_add_card_color_to_credit_cards.php` - Agrega personalización de colores
- `2025_07_27_030000_add_color_to_credit_cards.php` - Migración legacy de colores

**Para nuevas instalaciones:** Los campos se crean automáticamente con el esquema actualizado.
**Para instalaciones existentes:** Ejecutar las migraciones correspondientes.

```bash
# Migraciones
php console.php migrate              # Ejecutar migraciones pendientes
php console.php migrate:rollback     # Revertir última migración
php console.php migrate:status       # Ver estado de migraciones
php console.php migrate:refresh      # Refrescar base de datos

# Caché
php console.php cache:clear          # Limpiar todo el caché (archivos + APCu)
php console.php cache:stats          # Ver estadísticas detalladas de caché
php console.php cache:expired        # Limpiar solo archivos expirados

# Notificaciones
php console.php notifications:generate  # Generar notificaciones
php console.php notifications:clean     # Limpiar notificaciones expiradas

# Base de datos
php console.php db:backup            # Crear backup
php console.php db:seed              # Ejecutar seeders

# Sistema
php console.php system:status        # Ver estado del sistema
```

## 📊 Monitoreo y Logs

- **Logs de aplicación**: `logs/app.log`
- **Logs de notificaciones**: `logs/notifications.log`
- **Logs de errores**: `logs/error.log`
- **Estadísticas de caché**: Disponibles en la consola

## 🔄 Tareas Programadas

Para un funcionamiento óptimo, configura estas tareas:

- **Notificaciones**: Cada hora
- **Limpieza de caché**: Diariamente
- **Backup de BD**: Diariamente
- **Limpieza de logs**: Semanalmente

## 🔧 Solución de Problemas Comunes

### Mensajes de WhatsApp no llegan

**Problema:** Las notificaciones se muestran en la interfaz pero los mensajes de WhatsApp no llegan al teléfono.

**Causa:** WhatsApp estaba deshabilitado en la configuración y faltaban las credenciales de la API.

**Solución aplicada:**
- ✅ Habilitado WhatsApp en `.env.local` (`WHATSAPP_ENABLED=true`)
- ✅ Creada guía completa en `WHATSAPP_SETUP.md` para configurar credenciales
- ✅ Documentado proceso de obtención de API Key de CallMeBot
- ✅ Panel de administración disponible en `whatsapp_admin.php`

**Configuración requerida:** Obtener API Key de CallMeBot y configurar número de teléfono (ver `WHATSAPP_SETUP.md`)

### Notificaciones que no desaparecen al marcarlas como leídas

**Problema:** Las notificaciones marcadas como leídas no desaparecían de la lista.

**Causa:** El frontend cargaba todas las notificaciones por defecto (leídas y no leídas).

**Solución aplicada:**
- ✅ Modificado comportamiento por defecto para mostrar solo notificaciones no leídas
- ✅ Agregado botón "Ver todas las notificaciones" para acceso completo
- ✅ Mejorada experiencia de usuario con actualización inmediata

### Notificaciones Duplicadas

Si experimentas notificaciones duplicadas, esto puede deberse a:

1. **Problemas de compatibilidad SQLite**: Se corrigieron las consultas de verificación de duplicados
2. **Elementos HTML duplicados**: Se eliminó la duplicación entre `header.php` y `notifications.js`
3. **Consultas de fecha incorrectas**: Se actualizaron todas las funciones de fecha para usar sintaxis SQLite correcta

**Solución automática aplicada:**
- ✅ Corregidas consultas `date()` por `datetime()` en verificaciones de duplicados
- ✅ Eliminada duplicación de botones "Marcar todas como leídas"
- ✅ Mejorada lógica de prevención de duplicados en base de datos
- ✅ Agregado script de limpieza: `scripts/clean_duplicate_notifications.php`

**Para limpiar duplicados existentes:**
```bash
php scripts/clean_duplicate_notifications.php
```

## 🆕 Changelog

### v2.1.0 (2025-01-16)
- ✅ **Sistema de Estados Dinámicos para Tarjetas de Crédito**
  - Implementado cálculo automático de estados (Pagada, Pendiente, Vencida, Activa)
  - Agregado método `getDynamicCardStatus()` en clase CreditCard
  - Visualización mejorada con badges de colores e iconos
  - Integración completa en la interfaz de tarjetas de crédito
- ✅ **Correcciones de JavaScript**
  - Solucionado error `deleteTransaction is not defined`
  - Corregido problema de accesibilidad `aria-hidden` en modales
  - Implementado `window.currentCardId` para manejo global de IDs
  - Eliminada duplicación de funciones entre archivos
- ✅ **Mejoras en la Experiencia de Usuario**
  - Función de eliminación de transacciones más robusta
  - Actualización automática de la interfaz tras eliminar transacciones
  - Mejor manejo de modales y estados de visualización

### v2.0.0 (Actual)
- ✅ Sistema de notificaciones en tiempo real
- ✅ Sistema de caché avanzado
- ✅ Migraciones de base de datos
- ✅ Consola CLI completa
- ✅ Gestión de variables de entorno
- ✅ Mejoras de seguridad
- ✅ Optimización de rendimiento

### v1.0.0
- ✅ Sistema básico de gestión financiera
- ✅ CRUD completo de transacciones
- ✅ Sistema de usuarios y roles
- ✅ Dashboard básico

## 📄 Licencia

Este proyecto es de uso libre para fines educativos y personales.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## 📞 Soporte

Para soporte técnico o consultas, contacta al equipo de desarrollo.