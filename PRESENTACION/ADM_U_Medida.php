<?php
require_once '../NEGOCIO/N_U_Medida.php';
$medidaService = new N_U_Medida();

// Inicializar variable para edición
$medida = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_medida'])) {
    $id_medida = filter_input(INPUT_GET, 'id_medida', FILTER_VALIDATE_INT);
    if ($id_medida) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $medidaService->eliminar($id_medida);
            header('Location: ADM_U_Medida.php');
            exit();
        } else {
            $medida = $medidaService->buscarPorId($id_medida);
            if (!$medida) {
                echo "No se encontró la unidad de medida.";
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
        $u_medida = trim(strip_tags($_POST['u_medida'] ?? ''));
        $u_descripcion = trim(strip_tags($_POST['u_descripcion'] ?? ''));
        if ($u_medida && $u_descripcion!== false) {
            $medidaService->adicionar($u_medida, $u_descripcion);
            header('Location: ADM_U_Medida.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_medida = filter_input(INPUT_POST, 'id_medida', FILTER_VALIDATE_INT);
        $u_medida = trim(strip_tags($_POST['u_medida'] ?? ''));
        $u_descripcion = trim(strip_tags($_POST['u_descripcion'] ?? ''));
        if ($id_medida && $u_medida && $u_descripcion !== false) {
            $existing = $medidaService->buscarPorId($id_medida);
            if ($existing) {
                $medidaService->modificar($id_medida, $u_medida, $u_descripcion);
                header('Location: ADM_U_Medida.php');
                exit();
            } else {
                echo "Error: No existe la unidad de medida con ID $id_medida.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Listado y búsqueda
$medidas = $medidaService->buscarTodo();
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $medidas = $medidaService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Unidades de Medida</title>
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
                    <h4 class="card-title">UNIDADES DE MEDIDA</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="medidaModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Crear o Editar Unidad de Medida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMedida" action="ADM_U_Medida.php" method="post">
                        <input type="hidden" name="id_medida" id="id_medida" value="<?php echo isset($medida) ? $medida['id_medida'] : ''; ?>">

                        <div class="form-group">
                            <label for="u_medida">Unidad de Medida</label>
                            <input type="text" class="form-control" id="u_medida" name="u_medida" value="<?php echo isset($medida) ? htmlspecialchars($medida['u_medida']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="u_descripcion">Descripción</label>
                            <textarea class="form-control" id="u_descripcion" name="u_descripcion" required><?php echo isset($medida) ? htmlspecialchars($medida['u_descripcion']) : ''; ?></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Unidad de medida</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($medida) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar las Unidades de Medida</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_U_Medida.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearmedida" data-bs-toggle="modal" data-bs-target="#medidaModal">
            Registrar Unidad de Medida
        </button>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Unidad de Medida</th>
                <th>Descripción</th>
                <th>Cantidad de Materiales</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medidas as $u_medida): ?>
            <tr>
                <td><?php echo htmlspecialchars($u_medida['id_medida']); ?></td>
                <td><?php echo htmlspecialchars($u_medida['u_medida']); ?></td>
                <td><?php echo htmlspecialchars($u_medida['u_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($u_medida['u_materiales']); ?></td>
                <td>
                    <a href="ADM_U_Medida.php?id_medida=<?php echo $u_medida['id_medida']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_U_Medida.php?id_medida=<?php echo $u_medida['id_medida']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta medida?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php if (isset($medida)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('medidaModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearmedida").addEventListener("click", function () {
    const form = document.getElementById("formMedida");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_medida");
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
