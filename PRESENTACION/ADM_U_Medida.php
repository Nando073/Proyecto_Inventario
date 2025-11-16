<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_U_Medida.php';
$medidaService = new N_U_Medida();

// Inicializar variable para edición
$medida = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_medida'])) {
    $id_medida = filter_input(INPUT_GET, 'id_medida', FILTER_VALIDATE_INT);
    if ($id_medida) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                $resultado = $medidaService->eliminar($id_medida);

                if ($resultado['success']) {
                    $_SESSION['mensaje'] = "Unidad de medida eliminada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar la unidad de medida porque tiene materiales asociados.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                
                header('Location: ADM_U_Medida.php');
                exit();

            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar unidad de medida: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ADM_U_Medida.php');
                exit();
            }
        } else {
            // Modo edición
            $medida = $medidaService->buscarPorId($id_medida);
            if (!$medida) {
                $_SESSION['mensaje'] = "No se encontró la unidad de medida.";
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
    $id_medida = filter_input(INPUT_POST, 'id_medida', FILTER_VALIDATE_INT);
    $u_medida = trim(strip_tags($_POST['u_medida'] ?? ''));
    $u_descripcion = trim(strip_tags($_POST['u_descripcion'] ?? ''));

    if ($accion === 'crear') {
        if ($u_medida && $u_descripcion) {
            try {
            $resultado = $medidaService->adicionar($u_medida, $u_descripcion);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Medida registrado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo registrar la medida. ya registrada.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al registrar la medida: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            header('Location: ADM_U_Medida.php');
            exit();
        
        }
    } elseif ($accion === 'guardar') {
        if ($id_medida && $u_medida && $u_descripcion) {
            try {
                $resultado = $medidaService->modificar($id_medida, $u_medida, $u_descripcion);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Medida modificada correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo modificar la medida.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al modificar la medida: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            header('Location: ADM_U_Medida.php');
            exit();
        }
    }
}

// Listado y búsqueda
$medidas = $medidaService->obtenerMedidas();
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
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 mx-auto" style="max-width: 520px;">
  <div class="row g-0 align-items-center">
    <!-- Imagen -->
    <div class="col-5">
      <img src="../IMG/medida.jpg" alt="Unidades de Medida" class="img-fluid h-100 object-fit-cover">
    </div>

    <!-- Contenido -->
    <div class="col-7 bg-light">
      <div class="card-body d-flex flex-column justify-content-center h-100 text-center p-3">
        <h4 class="card-title fw-bold" style="color: #58D68D;">Unidades de Medida</h4>
        <p class="card-text text-secondary mb-0">Gestiona las unidades de medida para los materiales del sistema.</p>
      </div>
    </div>
  </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="medidaModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Formulario Unidad de Medida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMedida" action="ADM_U_Medida.php" method="post">
                        <input type="hidden" name="id_medida" id="id_medida" value="<?php echo isset($medida) ? $medida['id_medida'] : ''; ?>">

                        <div class="form-group">
                            <label for="u_medida">Unidad de Medida</label>
                            <input type="text" class="form-control" id="u_medida" name="u_medida" value="<?php echo isset($medida) ? htmlspecialchars($medida['u_medida']) : ''; ?>" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
                            onkeypress="return soloLetras(event)">
                        </div>

                        <div class="form-group">
                            <label for="u_descripcion">Descripción</label>
                            <textarea class="form-control" id="u_descripcion" name="u_descripcion" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
                            onkeypress="return soloLetras(event)"><?php echo isset($medida) ? htmlspecialchars($medida['u_descripcion']) : ''; ?></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($medida) ? 'display:none;' : ''; ?>">Crear Unidad de medida</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($medida) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar las Unidades de Medida</h3>
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_U_Medida.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2"/>
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        <button type="button" class="btn btn-success" id="btnCrearmedida" data-bs-toggle="modal" data-bs-target="#medidaModal">
            Registrar Unidad de Medida
        </button>
    </form>
    <!-- Mensajes de éxito o error -->
<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?>">
        <?= $_SESSION['mensaje']; ?>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
<?php endif; ?>

                                                <!--Tabla -->
 <div class="table-responsive">                                               
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Unidad de Medida</th>
                <th>Descripción</th>
                <th>Cantidad de Materiales</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medidas as $u_medida): ?>
            <tr>
                <td><?php echo htmlspecialchars($u_medida['u_medida']); ?></td>
                <td><?php echo htmlspecialchars($u_medida['u_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($u_medida['u_materiales']); ?></td>
                <td>
                    <div class="d-flex flex-column flex-md-row gap-1">
                        <a href="ADM_U_Medida.php?id_medida=<?php echo $u_medida['id_medida']; ?>" class="btn btn-warning">Editar</a>
                        <a href="ADM_U_Medida.php?id_medida=<?php echo $u_medida['id_medida']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta medida?');">Eliminar</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
    <!-- Fin de la tabla -->
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
    if (btnGuardar) btnGuardar.style.display = "none";

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.style.display = "inline-block";
});
</script>

</body>
</html>
