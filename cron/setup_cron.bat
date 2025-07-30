@echo off
REM Script para configurar tareas programadas en Windows
REM Este script debe ejecutarse como administrador

echo Configurando tareas programadas para Money Manager...

REM Crear tarea para generar notificaciones cada hora
schtasks /create /tn "MoneyManager_Notifications" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\git\scripts\generate_notifications.php" /sc hourly /mo 1 /f

REM Crear tarea para limpiar caché diariamente a las 2:00 AM
schtasks /create /tn "MoneyManager_CleanCache" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\git\console.php cache:clear" /sc daily /st 02:00 /f

REM Crear tarea para backup de base de datos diariamente a las 3:00 AM
schtasks /create /tn "MoneyManager_Backup" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\git\console.php db:backup" /sc daily /st 03:00 /f

echo.
echo Tareas programadas configuradas exitosamente:
echo - Notificaciones: cada hora
echo - Limpieza de caché: diariamente a las 2:00 AM
echo - Backup de BD: diariamente a las 3:00 AM
echo.
echo Para ver las tareas: schtasks /query /tn "MoneyManager*"
echo Para eliminar las tareas: schtasks /delete /tn "MoneyManager*" /f
echo.
pause