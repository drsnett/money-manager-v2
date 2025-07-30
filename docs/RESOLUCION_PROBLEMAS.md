# Resolución de Problemas - Money Manager

Este documento contiene la documentación de problemas identificados y sus soluciones en el sistema Money Manager.

## Índice
1. [Notificaciones que no desaparecen al marcarlas como leídas](#notificaciones-que-no-desaparecen)
2. [Notificaciones Duplicadas](#notificaciones-duplicadas)
3. [Mensajes de WhatsApp no llegan](#mensajes-whatsapp-no-llegan)
4. [Error al ejecutar deploy.php en servidor](#error-al-ejecutar-deployphp-en-servidor)

## Notificaciones que no desaparecen al marcarlas como leídas

### Problema
Cuando el usuario marca las notificaciones como leídas usando el botón "Marcar todas", las notificaciones no desaparecen de la lista.

### Causa Raíz
El problema estaba en la lógica del frontend JavaScript. El método `loadNotifications()` tenía como parámetro por defecto `unreadOnly = false`, lo que significa que siempre cargaba TODAS las notificaciones (leídas y no leídas), independientemente de si se habían marcado como leídas.

### Solución Implementada

#### 1. Cambio en el comportamiento por defecto
**Archivo:** `js/notifications.js`

- Cambiado `loadNotifications(unreadOnly = false)` por `loadNotifications(unreadOnly = true)`
- Modificadas todas las llamadas a `loadNotifications()` para usar `loadNotifications(true)`
- Esto hace que por defecto solo se muestren las notificaciones no leídas

#### 2. Funcionalidad "Ver todas"
**Archivos:** `js/notifications.js` y `includes/header.php`

- Agregado botón "Ver todas las notificaciones" en el dropdown
- Modificado método `showAllNotifications()` para cargar todas las notificaciones con `loadNotifications(false)`
- Esto permite al usuario ver tanto las leídas como las no leídas cuando lo desee

### Código Modificado

```javascript
// Antes
async loadNotifications(unreadOnly = false) {
    // Siempre mostraba todas las notificaciones
}

// Después
async loadNotifications(unreadOnly = true) {
    // Por defecto solo muestra las no leídas
}
```

```javascript
// Antes
showAllNotifications() {
    console.log('Mostrar todas las notificaciones');
}

// Después
showAllNotifications() {
    this.loadNotifications(false); // Cargar todas (leídas y no leídas)
}
```

### Resultado
- ✅ Las notificaciones marcadas como leídas ahora desaparecen correctamente
- ✅ El contador de notificaciones se actualiza inmediatamente
- ✅ El usuario puede ver todas las notificaciones usando el botón "Ver todas"
- ✅ La funcionalidad de marcar individual y masiva funciona correctamente

### Fecha de Resolución
30 de Julio de 2025

---

## Notificaciones Duplicadas (Resuelto anteriormente)

### Problema
Las notificaciones aparecían duplicadas en la interfaz.

### Causas Identificadas
1. **Duplicación de elementos HTML**: El botón "Marcar todas como leídas" aparecía tanto en `header.php` como se agregaba dinámicamente en `notifications.js`
2. **Consultas de fecha incorrectas**: Las verificaciones de duplicados usaban `date('now', '-1 day')` en lugar de `datetime('now', '-1 day')` para SQLite
3. **Sintaxis MySQL en SQLite**: Uso de `CURDATE()` en lugar de `date('now')`

### Soluciones Implementadas
1. ✅ Eliminada duplicación HTML en `notifications.js`
2. ✅ Corregidas consultas SQLite en `Notification.php`
3. ✅ Actualizada sintaxis en `generate_notifications.php`
4. ✅ Creado script de limpieza `clean_duplicate_notifications.php`

### Archivos modificados:
- `scripts/clean_duplicate_notifications.php` (creado)
- `classes/Notification.php` (corregido)
- `scripts/generate_notifications.php` (corregido)
- `README.md` (documentado)

### Fecha de Resolución
30 de Julio de 2025

---

## Mensajes de WhatsApp no llegan

### Problema:
Las notificaciones se muestran correctamente en la interfaz web, pero los mensajes de WhatsApp no llegan al teléfono del usuario.

### Causa identificada:
1. **WhatsApp deshabilitado**: La configuración `WHATSAPP_ENABLED` estaba en `false` en el archivo `.env.local`
2. **Credenciales faltantes**: No estaban configuradas las credenciales de la API de CallMeBot
3. **Número no configurado**: Faltaba el número de teléfono de destino

### Solución implementada:

#### 1. Habilitación de WhatsApp
```env
# En .env.local
WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://api.callmebot.com/whatsapp.php
```

#### 2. Configuración requerida (pendiente del usuario)
Para completar la configuración, el usuario debe:

1. **Obtener API Key de CallMeBot**:
   - Agregar `+34 644 59 71 67` a contactos como "CallMeBot"
   - Enviar mensaje: `I allow callmebot to send me messages`
   - Recibir API Key en respuesta

2. **Configurar credenciales en .env.local**:
   ```env
   WHATSAPP_API_KEY=TU_API_KEY_REAL
   WHATSAPP_PHONE=TU_NUMERO_CON_CODIGO_PAIS
   ```

#### 3. Condiciones para envío automático
Los mensajes se envían cuando:
- `WHATSAPP_ENABLED=true`
- Notificación con `priority='high'`
- API_KEY y PHONE configurados correctamente

### Verificación:
1. Acceder a `whatsapp_admin.php`
2. Usar "Probar Configuración"
3. Enviar mensaje de prueba
4. Revisar logs en `logs/error.log`

### Limitaciones identificadas:
- CallMeBot: máximo 1 mensaje cada 2 segundos
- Error 503 si se envían muchos mensajes seguidos

### Archivos modificados:
- `.env.local` (habilitado WhatsApp)
- `WHATSAPP_SETUP.md` (creado - guía completa)
- `docs/RESOLUCION_PROBLEMAS.md` (documentado)

### Fecha de Resolución
30 de Julio de 2025

---

## Error al ejecutar deploy.php en servidor

### Problema
Error "SQLSTATE[HY000] [14] unable to open database file" al ejecutar deploy.php en el servidor.

### Causa Raíz
- Rutas hardcodeadas en archivos de configuración
- Permisos incorrectos en directorios
- Configuración específica de desarrollo en archivos de producción

### Solución Implementada

#### 1. Corregir rutas hardcodeadas en check_migrations.php
```php
// Antes (problemático):
$db = new PDO('sqlite:C:/xampp/htdocs/git/data/money_manager.db');

// Después (corregido):
$dbPath = __DIR__ . '/data/money_manager.db';
$db = new PDO('sqlite:' . $dbPath);
```

#### 2. Verificar permisos de directorios
```bash
chmod 755 data/
chmod 755 logs/
chmod 755 uploads/
chmod 755 backups/
chmod 644 data/money_manager.db
```

#### 3. Configuraciones pendientes del usuario
- Actualizar rutas en `cron/setup_cron.bat` para el servidor específico
- Configurar variables de entorno en `.env.local` para el servidor
- Verificar que el servidor tenga las extensiones PHP requeridas

### Archivos con rutas hardcodeadas que requieren atención
- `cron/setup_cron.bat` - Contiene rutas específicas de XAMPP
- Cualquier script personalizado que referencie rutas absolutas

### Verificación
1. Ejecutar `php deploy.php` desde línea de comandos
2. Verificar que no aparezcan errores de base de datos
3. Comprobar que todos los directorios se creen correctamente
4. Verificar acceso a la aplicación web

### Condiciones para funcionamiento correcto
- Servidor con PHP 7.4+ y extensiones requeridas
- Permisos de escritura en directorios data/, logs/, uploads/
- Configuración correcta de .env.local para el servidor
- Base de datos SQLite accesible y con permisos correctos

### Archivos Modificados
- `check_migrations.php` (corregidas rutas hardcodeadas)
- `deploy.php` (verificación de permisos)

### Resultado
- ✅ Deploy.php ejecuta sin errores de base de datos
- ✅ Rutas relativas funcionan en cualquier servidor
- ✅ Permisos correctos en directorios críticos
- ✅ Configuración portable entre entornos

### Fecha de Resolución
30 de Julio de 2025

---

## Plantilla para Nuevos Problemas

### Problema
[Descripción del problema]

### Causa Raíz
[Análisis de la causa]

### Solución Implementada
[Pasos tomados para resolver]

### Archivos Modificados
- `archivo1.php`
- `archivo2.js`

### Resultado
- ✅ [Resultado 1]
- ✅ [Resultado 2]

### Fecha de Resolución
[Fecha]