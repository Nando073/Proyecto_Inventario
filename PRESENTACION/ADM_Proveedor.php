<?php
require_once '../NEGOCIO/N_Proveedor.php';
$proveedorService = new N_Proveedor();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$proveedor = null;

if (isset($_GET['id_proveedor'])) {
    $id_proveedor = filter_input(INPUT_GET, 'id_proveedor', FILTER_VALIDATE_INT);

    if ($id_proveedor) {
        // Crear una instancia del servicio de negocio
        $proveedorService = new N_Proveedor();
        
        // Verifica si se ha solicitado eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar al proveedor
            $proveedorService->eliminar($id_proveedor);
            // Redirigir al listado después de eliminar
            header('Location: ADM_Proveedor.php');
            exit();
        } else {
            // Llamada al método de negocio para obtener los datos de los areas
            $proveedor = $proveedorService->buscarPorId($id_proveedor);
            if (!$proveedor) {
                echo "No se encontró el proveedor.";
            }
        }
    } else {
        echo "ID inválido.";
    }
}

// Manejo de creación/actualización vía POST
$accion = $_POST['accion'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'crear') {
        $p_nombre = trim(strip_tags($_POST['p_nombre'] ?? ''));
        $p_direccion = trim(strip_tags($_POST['p_direccion'] ?? ''));
        $p_celular = trim(strip_tags($_POST['p_celular'] ?? ''));
        if ($p_nombre && $p_direccion && $p_celular !== false) {
            $proveedorService->adicionar($p_nombre, $p_direccion, $p_celular);
            header('Location: ADM_Proveedor.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
        $p_nombre = trim(strip_tags($_POST['p_nombre'] ?? ''));
        $p_direccion = trim(strip_tags($_POST['p_direccion'] ?? ''));
        $p_celular = trim(strip_tags($_POST['p_celular'] ?? ''));
        if ($id_proveedor && $p_nombre && $p_direccion && $p_celular !== false) {
            $existing = $proveedorService->buscarPorId($id_proveedor);
            if ($existing) {
                $proveedorService->modificar($id_proveedor, $p_nombre, $p_direccion, $p_celular);
                header('Location: ADM_Proveedor.php');
                exit();
            } else {
                echo "Error: No existe la categoría con ID $id_proveedor.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Obtener la lista de areas
$areas = $proveedorService->buscarTodo();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $areas = $proveedorService->buscarPorSimilitud($searchTerm);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../DEMO/styles.css?v=<?php echo(rand()); ?>"> 
    <script src="../DEMO/contrarer.js" defer></script>
    <title>Administrar Proveedores</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <div class="card mb-4" style="max-width: 540px; margin-left: 60vh">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/medida.jpeg" class="img-fluid rounded-start">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title">PROVEEDORES</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="proveedorModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Crear o Editar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMaterial" action="ADM_Proveedor.php" method="post">
                        <input type="hidden" name="id_proveedor" id="id_proveedor" value="<?php echo isset($proveedor) ? $proveedor['id_proveedor'] : ''; ?>">

                        <div class="form-group">
                            <label for="p_nombre">Nombre</label>
                            <input type="text" class="form-control" id="p_nombre" name="p_nombre" value="<?php echo isset($proveedor) ? htmlspecialchars($proveedor['p_nombre']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="p_direccion">Dirección</label>
                            <textarea class="form-control" id="p_direccion" name="p_direccion" required><?php echo isset($proveedor) ? htmlspecialchars($proveedor['p_direccion']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="p_celular">Celular</label>
                            <input type="text" class="form-control" id="p_celular" name="p_celular" value="<?php echo isset($proveedor) ? htmlspecialchars($proveedor['p_celular']) : ''; ?>" required>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Proveedor</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($proveedor) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Proveedores</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Proveedor.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearProveedor" data-bs-toggle="modal" data-bs-target="#proveedorModal">
            Registrar Proveedor
        </button>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Celular</th>
                <th>Fecha de Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($areas as $are): ?>
            <tr>
                <td><?php echo htmlspecialchars($are['id_proveedor']); ?></td>
                <td><?php echo htmlspecialchars($are['p_nombre']); ?></td>
                <td><?php echo htmlspecialchars($are['p_direccion']); ?></td>
                <td><?php echo htmlspecialchars($are['p_celular']); ?></td>
                <td><?php echo htmlspecialchars($are['p_fecha']); ?></td>
                <td>
                    <a href="ADM_Proveedor.php?id_proveedor=<?php echo $are['id_proveedor']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_Proveedor.php?id_proveedor=<?php echo $are['id_proveedor']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta proveedor?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php if (isset($proveedor)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('proveedorModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearProveedor").addEventListener("click", function () {
    const form = document.getElementById("formMaterial");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_proveedor");
    if (idInput) idInput.remove();

    // Desactiva el botón de guardar
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    if (btnGuardar) btnGuardar.disabled = true;

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.disabled = false;
});
</script>

</body>
</html>
