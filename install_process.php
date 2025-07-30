<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Migration.php';

// Configurar respuesta JSON
header('Content-Type: application/json');

// Función para enviar respuesta JSON
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Obtener el paso de instalación
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;

try {
    switch ($step) {
        case 1:
            // Paso 1: Inicializar base de datos y ejecutar migraciones
            try {
                $database = new Database();
                $dbInfo = $database->getDatabaseInfo();
                $tableCount = count($dbInfo);
                
                if ($tableCount > 0) {
                    // Ejecutar migraciones pendientes
                    try {
                        $migration = new Migration();
                        
                        // Capturar la salida del método migrate
                        ob_start();
                        $migrationResult = $migration->migrate();
                        $migrationOutput = ob_get_clean();
                        
                        if ($migrationResult) {
                            // Contar las migraciones ejecutadas desde la salida
                            $migrationsCount = substr_count($migrationOutput, '✓ Migración');
                            if ($migrationsCount > 0) {
                                sendResponse(true, "Base de datos inicializada correctamente. Se crearon {$tableCount} tablas y se ejecutaron {$migrationsCount} migraciones.");
                            } else {
                                sendResponse(true, "Base de datos inicializada correctamente. Se crearon {$tableCount} tablas. No hay migraciones pendientes.");
                            }
                        } else {
                            sendResponse(false, "Error ejecutando migraciones: " . $migrationOutput);
                        }
                    } catch (Exception $migrationError) {
                        sendResponse(true, "Base de datos inicializada correctamente. Se crearon {$tableCount} tablas. Error en migraciones: " . $migrationError->getMessage());
                    }
                } else {
                    sendResponse(false, "Error: No se pudieron crear las tablas de la base de datos.");
                }
            } catch (DatabaseException $e) {
                error_log($e->getMessage());
                sendResponse(false, 'Error al inicializar la base de datos. Por favor revise el registro de errores.');
            } catch (Exception $e) {
                sendResponse(false, 'Error al inicializar la base de datos: ' . $e->getMessage());
            }
            break;
            
        case 2:
            // Paso 2: Crear usuario administrador
            try {
                require_once 'classes/User.php';
                $database = new Database();
                $pdo = $database->getConnection();
                
                // Verificar si ya existe un usuario administrador
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_admin = 1");
                $stmt->execute();
                $adminCount = $stmt->fetchColumn();
                
                if ($adminCount > 0) {
                    sendResponse(true, "Usuario administrador ya existe.");
                } else {
                    // Crear usuario administrador por defecto
                    $user = new User();
                    $userId = $user->register('admin', 'admin@localhost.com', 'admin123', 'Administrador del Sistema');
                    
                    if ($userId) {
                        // Establecer como administrador
                        $user->setAdminStatus($userId, true);
                        sendResponse(true, "Usuario administrador creado correctamente. Usuario: admin, Contraseña: admin123");
                    } else {
                        sendResponse(false, "Error: No se pudo crear el usuario administrador.");
                    }
                }
            } catch (DatabaseException $e) {
                error_log($e->getMessage());
                sendResponse(false, 'Error de base de datos al crear usuario administrador. Revise el registro de errores.');
            } catch (Exception $e) {
                sendResponse(false, 'Error al crear usuario administrador: ' . $e->getMessage());
            }
            break;
            
        case 3:
            // Paso 3: Crear categorías predefinidas
            try {
                $database = new Database();
                $pdo = $database->getConnection();
                
                // Verificar si ya existen categorías
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
                $stmt->execute();
                $categoryCount = $stmt->fetchColumn();
                
                if ($categoryCount > 0) {
                    sendResponse(true, "Categorías predefinidas ya existen. Total: {$categoryCount} categorías.");
                } else {
                    // Obtener el ID del usuario administrador para asignar las categorías
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
                    $stmt->execute();
                    $adminUserId = $stmt->fetchColumn();
                    
                    if (!$adminUserId) {
                        sendResponse(false, "Error: No se encontró usuario administrador para crear categorías.");
                        break;
                    }
                    
                    // Crear categorías por defecto (gastos)
                    $expenseCategories = [
                        ['Alimentación', 'expense', 'food', '#FF6B6B'],
                        ['Transporte', 'expense', 'transport', '#4ECDC4'],
                        ['Entretenimiento', 'expense', 'entertainment', '#45B7D1'],
                        ['Salud', 'expense', 'health', '#96CEB4'],
                        ['Educación', 'expense', 'education', '#FFEAA7'],
                        ['Servicios', 'expense', 'services', '#DDA0DD'],
                        ['Compras', 'expense', 'shopping', '#98D8C8'],
                        ['Hogar', 'expense', 'home', '#F7DC6F'],
                        ['Otros Gastos', 'expense', 'other', '#85C1E9']
                    ];
                    
                    // Crear categorías por defecto (ingresos)
                    $incomeCategories = [
                        ['Salario', 'income', 'work', '#28A745'],
                        ['Freelance', 'income', 'freelance', '#17A2B8'],
                        ['Inversiones', 'income', 'investment', '#6F42C1'],
                        ['Ventas', 'income', 'sales', '#FD7E14'],
                        ['Otros Ingresos', 'income', 'other', '#20C997']
                    ];
                    
                    $allCategories = array_merge($expenseCategories, $incomeCategories);
                    
                    $insertedCount = 0;
                    foreach ($allCategories as $category) {
                        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, icon, color) VALUES (?, ?, ?, ?, ?)");
                        if ($stmt->execute([$adminUserId, $category[0], $category[1], $category[2], $category[3]])) {
                            $insertedCount++;
                        }
                    }
                    
                    if ($insertedCount > 0) {
                        sendResponse(true, "Categorías predefinidas creadas correctamente. Total: {$insertedCount} categorías.");
                    } else {
                        sendResponse(false, "Error: No se pudieron crear las categorías predefinidas.");
                    }
                }
            } catch (DatabaseException $e) {
                error_log($e->getMessage());
                sendResponse(false, 'Error de base de datos al crear categorías. Revise el registro de errores.');
            } catch (Exception $e) {
                sendResponse(false, 'Error al crear categorías: ' . $e->getMessage());
            }
            break;
            
        case 4:
            // Paso 4: Crear usuario demo
            try {
                require_once 'classes/User.php';
                $database = new Database();
                $pdo = $database->getConnection();
                
                // Verificar si ya existe el usuario demo
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'demo'");
                $stmt->execute();
                $demoCount = $stmt->fetchColumn();
                
                if ($demoCount > 0) {
                    sendResponse(true, "Usuario demo ya existe.");
                } else {
                    // Crear usuario demo
                    $user = new User();
                    $userId = $user->register('demo', 'demo@localhost.com', 'demo123', 'Usuario Demo');
                    
                    if ($userId) {
                        sendResponse(true, "Usuario demo creado correctamente. Usuario: demo, Contraseña: demo123");
                    } else {
                        sendResponse(false, "Error: No se pudo crear el usuario demo.");
                    }
                }
            } catch (DatabaseException $e) {
                error_log($e->getMessage());
                sendResponse(false, 'Error de base de datos al crear usuario demo. Revise el registro de errores.');
            } catch (Exception $e) {
                sendResponse(false, 'Error al crear usuario demo: ' . $e->getMessage());
            }
            break;
            
        case 5:
            // Paso 5: Verificación final del sistema
            try {
                $database = new Database();
                $pdo = $database->getConnection();
                
                // Verificar integridad de la base de datos
                $integrity = $database->checkIntegrity();
                if (!$integrity) {
                    sendResponse(false, "Error: La base de datos falló la verificación de integridad.");
                }
                
                // Verificar que el campo status existe en credit_cards
                $stmt = $pdo->prepare("PRAGMA table_info(credit_cards)");
                $stmt->execute();
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $hasStatusField = false;
                foreach ($columns as $column) {
                    if ($column['name'] === 'status') {
                        $hasStatusField = true;
                        break;
                    }
                }
                
                // Verificar que existan las tablas de notificaciones y migraciones
                $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('notifications', 'migrations')");
                $stmt->execute();
                $systemTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $verificationResults = [];
                $verificationResults[] = "✓ Integridad de base de datos: OK";
                $verificationResults[] = $hasStatusField ? "✓ Campo 'status' en tarjetas de crédito: OK" : "⚠ Campo 'status' en tarjetas de crédito: FALTANTE";
                $verificationResults[] = in_array('notifications', $systemTables) ? "✓ Tabla de notificaciones: OK" : "⚠ Tabla de notificaciones: FALTANTE";
                $verificationResults[] = in_array('migrations', $systemTables) ? "✓ Tabla de migraciones: OK" : "⚠ Tabla de migraciones: FALTANTE";
                
                $message = "Verificación del sistema completada:\n" . implode("\n", $verificationResults);
                sendResponse(true, $message);
                
            } catch (DatabaseException $e) {
                error_log($e->getMessage());
                sendResponse(false, 'Error de base de datos en verificación final. Revise el registro de errores.');
            } catch (Exception $e) {
                sendResponse(false, 'Error en verificación final: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, "Paso de instalación no válido.");
            break;
    }
} catch (DatabaseException $e) {
    error_log($e->getMessage());
    sendResponse(false, 'Error general de base de datos. Revise el registro de errores.');
} catch (Exception $e) {
    sendResponse(false, 'Error general en la instalación: ' . $e->getMessage());
}
?>