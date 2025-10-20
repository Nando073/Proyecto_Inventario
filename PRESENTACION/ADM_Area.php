<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador']);
require_once '../NEGOCIO/N_Area.php';
$areaService = new N_Area();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$area = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_area'])) {
    $id_area = filter_input(INPUT_GET, 'id_area', FILTER_VALIDATE_INT);
    if ($id_area) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                $resultado = $areaService->eliminar($id_area);

                if ($resultado['success']) {
                    $_SESSION['mensaje'] = "Área eliminada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar el área porque tiene funcionarios asociados.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                
                header('Location: ADM_Area.php');
                exit();

            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar área: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ADM_Area.php');
                exit();
            }
        } else {
            // Modo edición
            $area = $areaService->buscarPorId($id_area);
            if (!$area) {
                $_SESSION['mensaje'] = "No se encontró el área.";
                $_SESSION['tipo_mensaje'] = "warning";
            }
        }
    } else {
        $_SESSION['mensaje'] = "ID inválido.";
        $_SESSION['tipo_mensaje'] = "danger";
    }
}
// Manejo de creación/actualización vía POST
$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'crear') {
        $a_nombre = trim(strip_tags($_POST['a_nombre'] ?? ''));
        $a_descripcion = trim(strip_tags($_POST['a_descripcion'] ?? ''));

        if ($a_nombre && $a_descripcion) {
            try {
                $resultado = $areaService->adicionar($a_nombre, $a_descripcion);

                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Área registrada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se pudo registrar el área. ya existe.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al registrar el área: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: ADM_Area.php');
            exit();
        } 

    } elseif ($accion === 'guardar') {
        $id_area = filter_input(INPUT_POST, 'id_area', FILTER_VALIDATE_INT);
        $a_nombre = trim(strip_tags($_POST['a_nombre'] ?? ''));
        $a_descripcion = trim(strip_tags($_POST['a_descripcion'] ?? ''));

        if ($id_area && $a_nombre && $a_descripcion) {
            try {
                $resultado = $areaService->modificar($id_area, $a_nombre, $a_descripcion);

                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Área modificada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se pudo modificar el área.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al modificar el área: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: ADM_Area.php');
            exit();
        }
    }
}


// Obtener la lista de areas
$areas = $areaService->obtenerAreas();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $areas = $areaService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Areas</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <div class="card mb-4 mx-auto" style="max-width: 540px;">
        <div class="row g-0">
            <div class="col-5 col-md-5">
                <img src="../IMG/area.jpeg" class="img-fluid rounded-start w-100 h-auto">
            </div>
            <div class="col-7 col-md-7">
                <div class="card-body">
                    <h4 class="card-title h5 h4-md">AREAS</h4>
                    <h3 class="card-text h6 h3-md"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Formulario Área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMaterial" action="ADM_Area.php" method="post">
                        <input type="hidden" name="id_area" id="id_area" value="<?php echo isset($area) ? $area['id_area'] : ''; ?>">

                        <div class="form-group">
                            <label for="a_nombre">Nombre</label>
                            <input type="text" class="form-control" id="a_nombre" name="a_nombre" value="<?php echo isset($area) ? htmlspecialchars($area['a_nombre']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="a_descripcion">Descripción</label>
                            <textarea class="form-control" id="a_descripcion" name="a_descripcion" required><?php echo isset($area) ? htmlspecialchars($area['a_descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($area) ? 'display:none;' : ''; ?>">Crear Area</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($area) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Areas</h3>
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Area.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2"/>
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        <button type="button" class="btn btn-success" id="btnCrearArea" data-bs-toggle="modal" data-bs-target="#areaModal">
            Registrar Área
        </button>
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
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Cantidad de fucionarios</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($areas as $are): ?>
                <tr>
                    <td><?php echo htmlspecialchars($are['id_area']); ?></td>
                    <td><?php echo htmlspecialchars($are['a_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($are['a_descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($are['a_funcionarios']); ?></td>
                    <td>
                        <div class="d-flex flex-column flex-md-row gap-1">
                            <a href="ADM_Area.php?id_area=<?php echo $are['id_area']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="ADM_Area.php?id_area=<?php echo $are['id_area']; ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta area?');">Eliminar</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php if (isset($area)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('areaModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearArea").addEventListener("click", function () {
    const form = document.getElementById("formMaterial");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_area");
    if (idInput) idInput.remove();

    // Desactiva el botón de guardar
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    if (btnGuardar) btnGuardar.style.display = "none";

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.style.display = "inline-block";
});
</script>

</body>
</html>
