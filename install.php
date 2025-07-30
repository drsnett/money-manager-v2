<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Dev Network Solutions Money Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .install-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .install-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .install-header {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .install-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .install-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .install-body {
            padding: 2rem;
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 15px;
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(-20px);
        }
        
        .step.active {
            opacity: 1;
            transform: translateX(0);
            background: #e3f2fd;
            border-left-color: var(--info-color);
        }
        
        .step.completed {
            opacity: 1;
            transform: translateX(0);
            background: #e8f5e8;
            border-left-color: var(--success-color);
        }
        
        .step.error {
            opacity: 1;
            transform: translateX(0);
            background: #ffeaea;
            border-left-color: var(--danger-color);
        }
        
        .step-icon {
            font-size: 2rem;
            margin-right: 1rem;
            width: 3rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .step-description {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .step-details {
            background: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            display: none;
        }
        
        .step.completed .step-details,
        .step.error .step-details {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .credentials {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .credential-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            backdrop-filter: blur(5px);
        }
        
        .credential-item:last-child {
            margin-bottom: 0;
        }
        
        .progress-bar {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin: 2rem 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
            width: 0%;
            transition: width 0.8s ease;
        }
        
        .spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-access {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-access:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .completion-section {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border-radius: 15px;
            margin-top: 2rem;
            display: none;
        }
        
        .completion-section.show {
            display: block;
            animation: slideUp 0.8s ease;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--success-color);
            margin-bottom: 1rem;
            animation: bounce 1s ease;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <h1><i class="fas fa-cogs"></i> Instalación</h1>
                <p>Dev Network Solutions Money Manager</p>
            </div>
            
            <div class="install-body">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                
                <div id="step1" class="step">
                    <div class="step-icon">
                        <i class="fas fa-database text-primary"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Inicializando Base de Datos</div>
                        <div class="step-description">Creando estructura de base de datos y tablas necesarias...</div>
                        <div class="step-details"></div>
                    </div>
                </div>
                
                <div id="step2" class="step">
                    <div class="step-icon">
                        <i class="fas fa-user-shield text-primary"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Creando Usuario Administrador</div>
                        <div class="step-description">Configurando cuenta de administrador del sistema...</div>
                        <div class="step-details"></div>
                    </div>
                </div>
                
                <div id="step3" class="step">
                    <div class="step-icon">
                        <i class="fas fa-tags text-primary"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Configurando Categorías</div>
                        <div class="step-description">Creando categorías predefinidas para ingresos y gastos...</div>
                        <div class="step-details"></div>
                    </div>
                </div>
                
                <div id="step4" class="step">
                    <div class="step-icon">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Creando Usuario Demo</div>
                        <div class="step-description">Configurando cuenta de demostración...</div>
                        <div class="step-details"></div>
                    </div>
                </div>
                
                <div id="step5" class="step">
                    <div class="step-icon">
                        <i class="fas fa-check-double text-primary"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Verificación Final</div>
                        <div class="step-description">Verificando integridad y características del sistema...</div>
                        <div class="step-details"></div>
                    </div>
                </div>
                
                <div class="completion-section" id="completionSection">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-success mb-3">¡Instalación Completada!</h3>
                    <p class="mb-4">El sistema ha sido configurado exitosamente y está listo para usar.</p>
                    
                    <div class="credentials">
                        <h5 class="mb-3"><i class="fas fa-key"></i> Credenciales de Acceso</h5>
                        
                        <div class="credential-item">
                            <h6><i class="fas fa-user-shield"></i> Administrador</h6>
                            <div><strong>Usuario:</strong> admin</div>
                            <div><strong>Email:</strong> admin@moneymanager.com</div>
                            <div><strong>Contraseña:</strong> admin123</div>
                        </div>
                        
                        <div class="credential-item">
                            <h6><i class="fas fa-user"></i> Usuario Demo</h6>
                            <div><strong>Usuario:</strong> demo</div>
                            <div><strong>Email:</strong> demo@moneymanager.com</div>
                            <div><strong>Contraseña:</strong> demo123</div>
                        </div>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Cambie las contraseñas después del primer inicio de sesión por seguridad.
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn-access">
                            <i class="fas fa-sign-in-alt"></i>
                            Acceder al Sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 0;
        const totalSteps = 5;
        
        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }
        
        function setStepStatus(stepId, status, details = '') {
            const step = document.getElementById(stepId);
            const icon = step.querySelector('.step-icon i');
            const detailsDiv = step.querySelector('.step-details');
            
            step.classList.remove('active', 'completed', 'error');
            step.classList.add(status);
            
            if (status === 'completed') {
                icon.className = 'fas fa-check-circle text-success';
                currentStep++;
            } else if (status === 'error') {
                icon.className = 'fas fa-times-circle text-danger';
            } else if (status === 'active') {
                icon.innerHTML = '<div class="spinner"></div>';
            }
            
            if (details) {
                detailsDiv.innerHTML = details;
            }
            
            updateProgress();
        }
        
        function showCompletion() {
            document.getElementById('completionSection').classList.add('show');
        }
        
        // Simular proceso de instalación
        async function runInstallation() {
            // Paso 1: Base de datos
            setStepStatus('step1', 'active');
            
            try {
                const response1 = await fetch('install_process.php?step=1');
                const result1 = await response1.json();
                
                if (result1.success) {
                    setStepStatus('step1', 'completed', result1.message);
                    
                    // Paso 2: Usuario admin
                    setTimeout(async () => {
                        setStepStatus('step2', 'active');
                        
                        const response2 = await fetch('install_process.php?step=2');
                        const result2 = await response2.json();
                        
                        if (result2.success) {
                            setStepStatus('step2', 'completed', result2.message);
                            
                            // Paso 3: Categorías
                            setTimeout(async () => {
                                setStepStatus('step3', 'active');
                                
                                const response3 = await fetch('install_process.php?step=3');
                                const result3 = await response3.json();
                                
                                if (result3.success) {
                                    setStepStatus('step3', 'completed', result3.message);
                                    
                                    // Paso 4: Usuario demo
                                    setTimeout(async () => {
                                        setStepStatus('step4', 'active');
                                        
                                        const response4 = await fetch('install_process.php?step=4');
                                        const result4 = await response4.json();
                                        
                                        if (result4.success) {
                                            setStepStatus('step4', 'completed', result4.message);
                                            
                                            // Paso 5: Verificación final
                                            setTimeout(async () => {
                                                setStepStatus('step5', 'active');
                                                
                                                const response5 = await fetch('install_process.php?step=5');
                                                const result5 = await response5.json();
                                                
                                                if (result5.success) {
                                                    setStepStatus('step5', 'completed', result5.message);
                                                    
                                                    setTimeout(() => {
                                                        showCompletion();
                                                    }, 1000);
                                                } else {
                                                    setStepStatus('step5', 'error', result5.message);
                                                }
                                            }, 1500);
                                        } else {
                                            setStepStatus('step4', 'error', result4.message);
                                        }
                                    }, 1500);
                                } else {
                                    setStepStatus('step3', 'error', result3.message);
                                }
                            }, 1500);
                        } else {
                            setStepStatus('step2', 'error', result2.message);
                        }
                    }, 1500);
                } else {
                    setStepStatus('step1', 'error', result1.message);
                }
            } catch (error) {
                setStepStatus('step1', 'error', 'Error de conexión: ' + error.message);
            }
        }
        
        // Iniciar instalación cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(runInstallation, 1000);
        });
    </script>
</body>
</html>
