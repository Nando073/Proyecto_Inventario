<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Proveedor.php';
$proveedorService = new N_Proveedor();

// Filtro por estado (activo/inactivo)
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'activo'; // por defecto activos

/// Verifica si se pasa un ID en la URL para editar, eliminar o activar
$proveedor = null;

if (isset($_GET['id_proveedor'])) {
    $id_proveedor = filter_input(INPUT_GET, 'id_proveedor', FILTER_VALIDATE_INT);

    if ($id_proveedor) {
        // Verifica si se ha solicitado eliminar (desactivar)
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar/desactivar al proveedor
            $proveedorService->eliminar($id_proveedor);
            // Redirigir al listado después de eliminar
            header('Location: ADM_Proveedor.php');
            exit();
            
        } elseif (isset($_GET['action']) && $_GET['action'] === 'activar') {
            // Activar proveedor y capturar resultado
            $resultado = $proveedorService->activarProveedor($id_proveedor);

            if ($resultado) {
                $mensaje = "Proveedor activado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "El proveedor no pudo ser activado.";
                $tipo_mensaje = "danger";
            }

            // Guardar mensaje en sesión para mostrar después del redirect
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = $tipo_mensaje;

            header('Location: ADM_Proveedor.php?estado=activo'); 
            exit();
            
        } else {
            // Llamada al método de negocio para obtener los datos del proveedor
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
        $nit = trim(strip_tags($_POST['nit'] ?? ''));
        $departamento = trim(strip_tags($_POST['departamento'] ?? ''));
        $p_direccion = trim(strip_tags($_POST['p_direccion'] ?? ''));
        $p_celular = trim(strip_tags($_POST['p_celular'] ?? ''));
        if ($p_nombre && $nit && $departamento && $p_direccion && $p_celular !== false) {
            $proveedorService->adicionar($p_nombre, $nit, $departamento, $p_direccion, $p_celular);
            header('Location: ADM_Proveedor.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
        $p_nombre = trim(strip_tags($_POST['p_nombre'] ?? ''));
        $nit = trim(strip_tags($_POST['nit'] ?? ''));
        $departamento = trim(strip_tags($_POST['departamento'] ?? ''));
        $p_direccion = trim(strip_tags($_POST['p_direccion'] ?? ''));
        $p_celular = trim(strip_tags($_POST['p_celular'] ?? ''));
        if ($id_proveedor && $p_nombre && $nit && $departamento && $p_direccion && $p_celular !== false) {
            $existing = $proveedorService->buscarPorId($id_proveedor);
            if ($existing) {
                $proveedorService->modificar($id_proveedor, $p_nombre, $nit, $departamento, $p_direccion, $p_celular);
                header('Location: ADM_Proveedor.php');
                exit();
            } else {
                echo "Error: No existe el proveedor con ID $id_proveedor.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Obtener la lista de proveedores
$proveedores = $proveedorService->obtenerProveedores();

// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $proveedores = $proveedorService->buscarPorSimilitud($searchTerm);
}

// Filtrar en PHP según estado
if ($estadoFiltro === 'activo') {
    $proveedores = array_filter($proveedores, function($p) {
        return $p['p_estado'] == 1; // Ajusta según el nombre del campo en tu base de datos
    });
} elseif ($estadoFiltro === 'inactivo') {
    $proveedores = array_filter($proveedores, function($p) {
        return $p['p_estado'] == 0; // Ajusta según el nombre del campo en tu base de datos
    });
}

// Opcional: para mostrar en la vista como "Activo" o "Inactivo"
foreach ($proveedores as &$proveedorItem) {
    $proveedorItem['estado_texto'] = $proveedorItem['p_estado'] == 1 ? 'Activo' : 'Inactivo';
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
    <div class="card mb-4 mx-auto" style="max-width: 540px;">
        <div class="row g-0">
            <div class="col-5 col-md-5">
                <img src="../IMG/medida.jpeg" class="img-fluid rounded-start w-100 h-auto">
            </div>
            <div class="col-7 col-md-7">
                <div class="card-body">
                    <h4 class="card-title h5 h4-md">PROVEEDORES</h4>
                    <h3 class="card-text h6 h3-md"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="proveedorModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
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
                            <label for="nit">NIT</label>
                            <input type="number" class="form-control" id="nit" name="nit" value="<?php echo isset($proveedor) ? htmlspecialchars($proveedor['nit']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="departamento">Departamento</label>
                            <select class="form-control" id="departamento" name="departamento" required>
                                <option value="">Seleccione un departamento</option>
                                <option value="La Paz"      <?php echo (isset($proveedor) && $proveedor['departamento'] == 'La Paz') ? 'selected' : ''; ?>>La Paz</option>
                                <option value="Cochabamba"  <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Cochabamba') ? 'selected' : ''; ?>>Cochabamba</option>
                                <option value="Santa Cruz"  <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Santa Cruz') ? 'selected' : ''; ?>>Santa Cruz</option>
                                <option value="Oruro"       <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Oruro') ? 'selected' : ''; ?>>Oruro</option>
                                <option value="Potosí"      <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Potosí') ? 'selected' : ''; ?>>Potosí</option>
                                <option value="Chuquisaca"  <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Chuquisaca') ? 'selected' : ''; ?>>Chuquisaca</option>
                                <option value="Tarija"      <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Tarija') ? 'selected' : ''; ?>>Tarija</option>
                                <option value="Beni"        <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Beni') ? 'selected' : ''; ?>>Beni</option>
                                <option value="Pando"       <?php echo (isset($proveedor) && $proveedor['departamento'] == 'Pando') ? 'selected' : ''; ?>>Pando</option>
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="p_direccion">Dirección</label>
                            <textarea class="form-control" id="p_direccion" name="p_direccion" required><?php echo isset($proveedor) ? htmlspecialchars($proveedor['p_direccion']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="p_celular">Celular</label>
                            <input type="number" class="form-control" id="p_celular" name="p_celular" value="<?php echo isset($proveedor) ? htmlspecialchars($proveedor['p_celular']) : ''; ?>" required>
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
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Proveedor.php" method="get">
        <!-- Búsqueda -->
        <div class="d-flex flex-grow-1 me-md-2 mb-2 mb-md-0">
            <input type="text" name="search" placeholder="Buscar proveedor" 
                value="<?php echo htmlspecialchars($searchTerm); ?>" 
                class="form-control me-2">
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        
        <!-- Botones de administración -->
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Todos
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item <?php echo $estadoFiltro === 'activo' ? 'active' : ''; ?>" 
                        href="ADM_Proveedor.php?estado=activo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                            Activos
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item <?php echo $estadoFiltro === 'inactivo' ? 'active' : ''; ?>" 
                        href="ADM_Proveedor.php?estado=inactivo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                            Inactivos
                        </a>
                    </li>
                </ul>
            </div>
            
            <button type="button" class="btn btn-success" id="btnCrearProveedor" data-bs-toggle="modal" data-bs-target="#proveedorModal">
                Registrar
            </button>
        </div>
    </form>

    <!-- Mensaje -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> mt-3">
            <?= htmlspecialchars($_SESSION['mensaje']); ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

<div class="table-responsive">
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>NIT</th>
                <th>Departamento</th>
                <th>Dirección</th>
                <th>Celular</th>
                <th>Fecha de Registro</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($proveedores)): ?>
                <?php foreach ($proveedores as $Proveedor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($Proveedor['p_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($Proveedor['nit']); ?></td>
                        <td><?php echo htmlspecialchars($Proveedor['departamento']); ?></td>
                        <td><?php echo htmlspecialchars($Proveedor['p_direccion']); ?></td>
                        <td><?php echo htmlspecialchars($Proveedor['p_celular']); ?></td>
                        <td><?php echo htmlspecialchars($Proveedor['p_fecha']); ?></td>
                        <td>
                            <?php if ($Proveedor['p_estado'] == 1): ?>
                                <span style="color: green; font-weight: bold;">Activo</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <?php if ($Proveedor['p_estado'] == 1): ?>
                                    <a href="ADM_Proveedor.php?id_proveedor=<?php echo $Proveedor['id_proveedor']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="ADM_Proveedor.php?id_proveedor=<?php echo $Proveedor['id_proveedor']; ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');">Eliminar</a>
                                <?php else: ?>
                                    <a href="ADM_Proveedor.php?id_proveedor=<?php echo $Proveedor['id_proveedor']; ?>&action=activar" class="btn btn-primary btn-sm">Activar</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                 <tr>
                     <td colspan="10" class="text-center">No hay proveedores para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
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
