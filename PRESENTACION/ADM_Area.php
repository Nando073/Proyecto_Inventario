<?php
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Area.php';
$areaService = new N_Area();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$area = null;

if (isset($_GET['id_area'])) {
    $id_area = filter_input(INPUT_GET, 'id_area', FILTER_VALIDATE_INT);

    if ($id_area) {
        // Crear una instancia del servicio de negocio
        $areaService = new N_Area();
        
        // Verifica si se ha solicitado eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar al area
            $areaService->eliminar($id_area);
            // Redirigir al listado después de eliminar
            header('Location: ADM_Area.php');
            exit();
        } else {
            // Llamada al método de negocio para obtener los datos de los areas
            $area = $areaService->buscarPorId($id_area);
            if (!$area) {
                echo "No se encontró el area.";
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
        $a_nombre = trim(strip_tags($_POST['a_nombre'] ?? ''));
        $a_descripcion = trim(strip_tags($_POST['a_descripcion'] ?? ''));
        //$a_funcionarios = filter_input(INPUT_POST, 'a_funcionarios', FILTER_VALIDATE_INT);
        if ($a_nombre && $a_descripcion !== false) {
            $areaService->adicionar($a_nombre, $a_descripcion);
            header('Location: ADM_Area.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_area = filter_input(INPUT_POST, 'id_area', FILTER_VALIDATE_INT);
        $a_nombre = trim(strip_tags($_POST['a_nombre'] ?? ''));
        $a_descripcion = trim(strip_tags($_POST['a_descripcion'] ?? ''));
        //$a_funcionarios = filter_input(INPUT_POST, 'a_funcionarios', FILTER_VALIDATE_INT);
        if ($id_area && $a_nombre && $p_direccion !== false) {
            $existing = $areaService->buscarPorId($id_area);
            if ($existing) {
                $areaService->modificar($id_area, $a_nombre, $a_descripcion);
                echo "";
                header('Location: ADM_Area.php');
                exit();
            } else {
                echo "Error: No existe la categoría con ID $id_area.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Obtener la lista de areas
$areas = $areaService->buscarTodo();
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
    <div class="card mb-4" style="max-width: 540px; margin-left: 60vh">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/area.jpeg" class="img-fluid rounded-start">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title">AREAS</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Crear o Editar Area</h5>
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
                            <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Area</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($area) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Areas</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Area.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearArea" data-bs-toggle="modal" data-bs-target="#areaModal">
            Registrar Area
        </button>
    </form>

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
                    <a href="ADM_Area.php?id_area=<?php echo $are['id_area']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_Area.php?id_area=<?php echo $are['id_area']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta area?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../Acceso.php"><button type="button" class="btn btn-info">Acceso</button></a>
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
    if (btnGuardar) btnGuardar.disabled = true;

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.disabled = false;
});
</script>

</body>
</html>
