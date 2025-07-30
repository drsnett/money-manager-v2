<?php
require_once 'config/config.php';
require_once 'classes/Category.php';

requireLogin();

$title = 'Categorías';
$userId = getCurrentUserId();

// Inicializar clase
$category = new Category();

// Obtener categorías
$categories = $category->getCategoryUsage($userId);

// Manejo de formularios
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitize($_POST['name']);
        $type = $_POST['type'];
        $color = sanitize($_POST['color']);
        
        if ($name && $type && $color) {
            try {
                $category->create($userId, $name, $type, $color);
                $success = 'Categoría agregada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al agregar la categoría: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos.';
        }
    } elseif ($action === 'edit') {
        $categoryId = (int)$_POST['category_id'];
        $name = sanitize($_POST['name']);
        $type = $_POST['type'];
        $color = sanitize($_POST['color']);
        
        if ($categoryId && $name && $type && $color) {
            try {
                $category->update($categoryId, $name, $type, $color);
                $success = 'Categoría actualizada exitosamente.';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al actualizar la categoría: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos.';
        }
    } elseif ($action === 'delete') {
        $categoryId = (int)$_POST['category_id'];
        
        if ($categoryId) {
            try {
                $result = $category->delete($categoryId);
                if ($result) {
                    $success = 'Categoría eliminada exitosamente.';
                } else {
                    $error = 'No se puede eliminar la categoría porque tiene transacciones asociadas.';
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $error = 'Error al eliminar la categoría: ' . $e->getMessage();
            }
        }
    }
}

// Separar categorías por tipo
$incomeCategories = array_filter($categories, function($cat) { return $cat['type'] === 'income'; });
$expenseCategories = array_filter($categories, function($cat) { return $cat['type'] === 'expense'; });

include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-tags"></i> Categorías</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus"></i> Nueva Categoría
        </button>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Categorías de Ingresos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="bi bi-arrow-up-circle"></i> Categorías de Ingresos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($incomeCategories)): ?>
                    <p class="text-muted text-center">No hay categorías de ingresos.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Transacciones</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incomeCategories as $cat): ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo $cat['color']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $cat['transaction_count']; ?></td>
                                        <td class="text-success"><?php echo formatCurrency($cat['total_amount']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($cat['transaction_count'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Categorías de Gastos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-arrow-down-circle"></i> Categorías de Gastos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($expenseCategories)): ?>
                    <p class="text-muted text-center">No hay categorías de gastos.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Transacciones</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expenseCategories as $cat): ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: <?php echo $cat['color']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $cat['transaction_count']; ?></td>
                                        <td class="text-danger"><?php echo formatCurrency($cat['total_amount']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($cat['transaction_count'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de categorías -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart"></i> Distribución de Ingresos</h5>
            </div>
            <div class="card-body">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart"></i> Distribución de Gastos</h5>
            </div>
            <div class="card-body">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Seleccionar...</option>
                            <option value="income">Ingreso</option>
                            <option value="expense">Gasto</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="color" name="color" value="#007bff" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar categoría -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Tipo</label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="">Seleccionar...</option>
                            <option value="income">Ingreso</option>
                            <option value="expense">Gasto</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="edit_color" name="color" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form para eliminar -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="category_id" id="delete_category_id">
</form>

<script>
// Función para editar categoría
function editCategory(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_type').value = category.type;
    document.getElementById('edit_color').value = category.color;
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

// Función para eliminar categoría
function deleteCategory(id) {
    if (confirmDelete('¿Está seguro de que desea eliminar esta categoría?')) {
        document.getElementById('delete_category_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Datos para gráficos
const incomeData = {
    labels: <?php echo json_encode(array_column($incomeCategories, 'name')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($incomeCategories, 'total_amount')); ?>,
        backgroundColor: <?php echo json_encode(array_column($incomeCategories, 'color')); ?>,
        borderWidth: 2
    }]
};

const expenseData = {
    labels: <?php echo json_encode(array_column($expenseCategories, 'name')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($expenseCategories, 'total_amount')); ?>,
        backgroundColor: <?php echo json_encode(array_column($expenseCategories, 'color')); ?>,
        borderWidth: 2
    }]
};

// Configuración común para los gráficos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

// Crear gráficos
const incomeCtx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(incomeCtx, {
    type: 'doughnut',
    data: incomeData,
    options: chartOptions
});

const expenseCtx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(expenseCtx, {
    type: 'doughnut',
    data: expenseData,
    options: chartOptions
});
</script>

<?php include 'includes/footer.php'; ?>
