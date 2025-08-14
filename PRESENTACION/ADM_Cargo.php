<?php
require_once '../NEGOCIO/N_Cargo.php';
$cargoService = new N_Cargo();



/// Verifica si se pasa un ID en la URL para editar o eliminar
$cargo = null;

if (isset($_GET['id_cargo'])) {
    $id_cargo = filter_input(INPUT_GET, 'id_cargo', FILTER_VALIDATE_INT);

    if ($id_cargo) {
        // Crear una instancia del servicio de negocio
        $cargoService = new N_Cargo();
        
        // Verifica si se ha solicitado eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar al cargo
            $cargoService->eliminar($id_cargo);
            // Redirigir al listado después de eliminar
            header('Location: ADM_Cargo.php');
            exit();
        } else {
            // Llamada al método de negocio para obtener los datos de los cargos
            $cargo = $cargoService->buscarPorId($id_cargo);
            if (!$cargo) {
                echo "No se encontró el cargo.";
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
        $nombre_c = trim(strip_tags($_POST['nombre_c'] ?? ''));
        $descripcion_c = trim(strip_tags($_POST['descripcion_c'] ?? ''));
        if ($nombre_c && $descripcion_c !== false) {
            $cargoService->adicionar($nombre_c, $descripcion_c);
            header('Location: ADM_Cargo.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_cargo = filter_input(INPUT_POST, 'id_cargo', FILTER_VALIDATE_INT);
        $nombre_c = trim(strip_tags($_POST['nombre_c'] ?? ''));
        $descripcion_c = trim(strip_tags($_POST['descripcion_c'] ?? ''));
        if ($id_cargo && $nombre_c && $p_direccion !== false) {
            $existing = $cargoService->buscarPorId($id_cargo);
            if ($existing) {
                $cargoService->modificar($id_cargo, $nombre_c, $descripcion_c);
                header('Location: ADM_Cargo.php');
                exit();
            } else {
                echo "Error: No existe el cargo con ID $id_cargo.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Obtener la lista de cargos
$cargos = $cargoService->buscarTodo();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $cargos = $cargoService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Cargos</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <div class="card mb-4" style="max-width: 540px; margin-left: 60vh">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/cargo.jpeg" class="img-fluid rounded-start">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title">CARGOS</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="cargoModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Crear o Editar Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formCargo" action="ADM_Cargo.php" method="post">
                        <input type="hidden" name="id_cargo" id="id_cargo" value="<?php echo isset($cargo) ? $cargo['id_cargo'] : ''; ?>">

                        <div class="form-group">
                            <label for="nombre_c">Nombre</label>
                            <input type="text" class="form-control" id="nombre_c" name="nombre_c" value="<?php echo isset($cargo) ? htmlspecialchars($cargo['nombre_c']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="descripcion_c">Descripción</label>
                            <textarea class="form-control" id="descripcion_c" name="descripcion_c" required><?php echo isset($cargo) ? htmlspecialchars($cargo['descripcion_c']) : ''; ?></textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Cargo</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($cargo) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Cargos</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Cargo.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearCargo" data-bs-toggle="modal" data-bs-target="#cargoModal">
            Registrar Cargo
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
            <?php foreach ($cargos as $are): ?>
            <tr>
                <td><?php echo htmlspecialchars($are['id_cargo']); ?></td>
                <td><?php echo htmlspecialchars($are['nombre_c']); ?></td>
                <td><?php echo htmlspecialchars($are['descripcion_c']); ?></td>
                <td><?php echo htmlspecialchars($are['funcionarios_c']); ?></td>
                <td>
                    <a href="ADM_Cargo.php?id_cargo=<?php echo $are['id_cargo']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_Cargo.php?id_cargo=<?php echo $are['id_cargo']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta cargo?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php if (isset($cargo)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('cargoModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearCargo").addEventListener("click", function () {
    const form = document.getElementById("formCargo");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_cargo");
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
