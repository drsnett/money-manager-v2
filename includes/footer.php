</main>
            </div>
        </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-cash-coin"></i> Sistema de gestión financiera</h5>
                    <p class="text-muted">dev</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted">
                        &copy; <?php echo date('Y'); ?> Dev Network Solutions. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script>
        // Sidebar toggle para móvil
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
        
        // Inicializar sistema de notificaciones si el usuario está logueado
        <?php if (isLoggedIn()): ?>
        // Incluir el script de notificaciones
        const notificationManager = new NotificationManager();
        <?php endif; ?>
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
            
            // Cerrar sidebar al hacer clic en un enlace (móvil)
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            });
        });

        // Funciones utilitarias
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(amount);
        }

        function formatDate(date) {
            return new Intl.DateTimeFormat('es-MX').format(new Date(date));
        }

        // Función para mostrar alertas
        function showAlert(message, type = 'info', duration = 5000) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('main');
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-dismiss
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        }

        // Función para confirmar eliminación
        function confirmDelete(message = '¿Estás seguro de que deseas eliminar este elemento?') {
            return confirm(message);
        }

        // Función para mostrar loading
        function showLoading(element) {
            element.innerHTML = '<div class="loading"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        }

        // Función para ocultar loading
        function hideLoading(element, originalContent) {
            element.innerHTML = originalContent;
        }

        // Validaciones del formulario
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }

        // AJAX helper
        function makeRequest(url, method = 'GET', data = null) {
            return fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: data ? JSON.stringify(data) : null
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            });
        }

        // Función para exportar datos
        function exportData(type, url) {
            // Abrir en nueva ventana/pestaña para descargar
            window.open(url, '_blank');
        }

        // Función para imprimir
        function printContent(contentId) {
            const content = document.getElementById(contentId);
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Imprimir</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .no-print { display: none !important; }
                        </style>
                    </head>
                    <body>
                        ${content.innerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Función para copiar al portapapeles
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert('Copiado al portapapeles', 'success', 2000);
            }).catch(err => {
                showAlert('Error al copiar', 'danger', 3000);
            });
        }

        // Configurar tooltips y popovers
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        // Función para activar el enlace del menú actual
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    
    <?php if (isLoggedIn()): ?>
    <!-- Definir token CSRF para JavaScript -->
    <script>
        window.csrfToken = '<?php echo generateCSRFToken(); ?>';
    </script>
    
    <!-- Script de notificaciones -->
    <script src="js/notifications.js"></script>
    
    <!-- Contenedor para notificaciones toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        $(function() {
            setTimeout(console.log.bind(console, "%cPowered by:", "color: red; font-size: 20px; font-weight: bold;"), 1000);
            setTimeout(console.log.bind(console, "%cDev Network Solutions", "color: #00d09e; font-size: 40px; font-weight: bold; text-shadow: 1px 1px 5px #00d09e; filter: dropshadow(color=#00d09e, offx=1, offy=1);"), 1000);
            setTimeout(console.log.bind(console, "%chttp://panel.drsnet.ovh", "color: black; font-size: 15px; font-weight: bold;"), 1000);

            $(document).on("click", ".NotifySkipButton", function (e) {
                $.ajax({
                    type: 'POST',
                    async: true,
                    data: {
                        type: this.getAttribute("NotifyType"),
                        id: this.getAttribute("NotifyID"),
                        rndm: Math.floor(Math.random() * 99999)
                    },
                    url: '/functions/notificationskip.php',
                    success: function (data) {
                        NotificationsRemoveAll();
                        ShowNotifications(true);
                    }
                });
            });
        });
    </script>
</body>
</html>
