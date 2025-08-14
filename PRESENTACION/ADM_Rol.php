<?php
require_once '../NEGOCIO/N_Rol.php';
$rolService = new N_Rol();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$rol = null;

if (isset($_GET['id_rol'])) {
    $id_rol = filter_input(INPUT_GET, 'id_rol', FILTER_VALIDATE_INT);

    if ($id_rol) {
        // Registrar una instancia del servicio de negocio
        $rolService = new N_Rol();
        
        // Verifica si se ha solicitado eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar al rol
            $rolService->eliminar($id_rol);
            // Redirigir al listado después de eliminar
            header('Location: ADM_Rol.php');
            exit();
        } else {
            // Llamada al método de negocio para obtener los datos de los areas
            $rol = $rolService->buscarPorId($id_rol);
            if (!$rol) {
                echo "No se encontró el rol.";
            }
        }
    } else {
        echo "ID inválido.";
    }
}

// Manejo de creación/actualización vía POST
$accion = $_POST['accion'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'Registrar') {
        $r_nombre = trim(strip_tags($_POST['r_nombre'] ?? ''));
        $r_descripcion = trim(strip_tags($_POST['r_descripcion'] ?? ''));
        //$a_funcionarios = filter_input(INPUT_POST, 'a_funcionarios', FILTER_VALIDATE_INT);
        if ($r_nombre && $r_descripcion !== false) {
            $rolService->adicionar($r_nombre, $r_descripcion);
            header('Location: ADM_Rol.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_rol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
        $r_nombre = trim(strip_tags($_POST['r_nombre'] ?? ''));
        $r_descripcion = trim(strip_tags($_POST['r_descripcion'] ?? ''));
        if ($id_rol && $r_nombre && $r_descripcion !== false) {
            $existing = $rolService->buscarPorId($id_rol);
            if ($existing) {
                $rolService->modificar($id_rol, $r_nombre, $r_descripcion);
                echo "";
                header('Location: ADM_Rol.php');
                exit();
            } else {
                echo "Error: No existe la categoría con ID $id_rol.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Obtener la lista de areas
$areas = $rolService->buscarTodo();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $areas = $rolService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Roles</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <div class="card mb-4" style="max-width: 540px; margin-left: 60vh">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/img.png" class="img-fluid rounded-start">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title">ROLES</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="RolModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Registrar o Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formRol" action="ADM_Rol.php" method="post">
                        <input type="hidden" name="id_rol" id="id_rol" value="<?php echo isset($rol) ? $rol['id_rol'] : ''; ?>">

                        <div class="form-group">
                            <label for="r_nombre">Nombre</label>
                            <input type="text" class="form-control" id="r_nombre" name="r_nombre" value="<?php echo isset($rol) ? htmlspecialchars($rol['r_nombre']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="r_descripcion">Descripción</label>
                            <textarea class="form-control" id="r_descripcion" name="r_descripcion" required><?php echo isset($rol) ? htmlspecialchars($rol['r_descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="Registrar" class="btn btn-primary">Registrar Rol</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($rol) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Roles</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Rol.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnRegistrarRol" data-bs-toggle="modal" data-bs-target="#RolModal">
            Registrar Rol
        </button>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($areas as $are): ?>
            <tr>
                <td><?php echo htmlspecialchars($are['id_rol']); ?></td>
                <td><?php echo htmlspecialchars($are['r_nombre']); ?></td>
                <td><?php echo htmlspecialchars($are['r_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($are['r_fecha']); ?></td>
                <td>
                    <a href="ADM_Rol.php?id_rol=<?php echo $are['id_rol']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_Rol.php?id_rol=<?php echo $are['id_rol']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta rol?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../Acceso.php"><button type="button" class="btn btn-info">Acceso</button></a>
</main>

<?php if (isset($rol)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('RolModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnRegistrarRol").addEventListener("click", function () {
    const form = document.getElementById("formRol");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_rol");
    if (idInput) idInput.remove();

    // Desactiva el botón de guardar
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    if (btnGuardar) btnGuardar.disabled = true;

    // Activa el botón de Registrar
    const btnRegistrar = form.querySelector('button[name="accion"][value="Registrar"]');
    if (btnRegistrar) btnRegistrar.disabled = false;
});
</script>

</body>
</html>
