<?php
require_once __DIR__ . '/../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once __DIR__ . '/../NEGOCIO/N_Distrito.php';
$distritoService = new N_Distrito();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$distrito = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_distrito'])) {
    $id_distrito = filter_input(INPUT_GET, 'id_distrito', FILTER_VALIDATE_INT);
    if ($id_distrito) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                $resultado = $distritoService->eliminar($id_distrito);

                if ($resultado['success']) {
                    $_SESSION['mensaje'] = "Distrito eliminado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar el distrito porque tiene funcionarios asociados.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                
                header('Location: ADM_Distrito.php');
                exit();

            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar distrito: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ADM_Distrito.php');
                exit();
            }
        } else {
            // Modo edición
            $distrito = $distritoService->buscarPorId($id_distrito);
            if (!$distrito) {
                $_SESSION['mensaje'] = "No se encontró el distrito.";
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
        $d_nombre = trim(strip_tags($_POST['d_nombre'] ?? ''));
        $d_descripcion = trim(strip_tags($_POST['d_descripcion'] ?? ''));

        if ($d_nombre && $d_descripcion) {
            try {
                $resultado = $distritoService->adicionar($d_nombre, $d_descripcion);

                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Distrito registrada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se pudo registrar el distrito. ya existe.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al registrar el distrito: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: ADM_Distrito.php');
            exit();
        } 

    } elseif ($accion === 'guardar') {
        $id_distrito = filter_input(INPUT_POST, 'id_distrito', FILTER_VALIDATE_INT);
        $d_nombre = trim(strip_tags($_POST['d_nombre'] ?? ''));
        $d_descripcion = trim(strip_tags($_POST['d_descripcion'] ?? ''));

        if ($id_distrito && $d_nombre && $d_descripcion) {
            try {
                $resultado = $distritoService->modificar($id_distrito, $d_nombre, $d_descripcion);

                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Distrito modificada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se pudo modificar el distrito.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al modificar el distrito: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: ADM_Distrito.php');
            exit();
        }
    }
}


// Obtener la lista de distritos
$distritos = $distritoService->obtenerDistritos();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $distritos = $distritoService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Distritos</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
   <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 mx-auto" style="max-width: 520px;">
  <div class="row g-0 align-items-center">
    <!-- Imagen -->
    <div class="col-5">
      <img src="../IMG/distrito.png" alt="Distrito" class="img-fluid h-100 object-fit-cover">
    </div>

    <!-- Contenido -->
    <div class="col-7 bg-light">
      <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-3">
        <h4 class="card-title fw-bold mb-2" style="color: #6fbf73;">Control de Distritos</h4>
        <p class="card-text text-secondary mb-0" style="font-size: 0.95rem;">
          Administra los distritos y su información de forma eficiente.
        </p>
      </div>
    </div>
  </div>
</div>



    <!-- Modal -->
    <div class="modal fade" id="distritoModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Formulario Distrito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMaterial" action="ADM_Distrito.php" method="post">
                        <input type="hidden" name="id_distrito" id="id_distrito" value="<?php echo isset($distrito) ? $distrito['id_distrito'] : ''; ?>">

                        <div class="form-group">
                            <label for="d_nombre">Nombre</label>
                            <input type="text" class="form-control" id="d_nombre" name="d_nombre" value="<?php echo isset($distrito) ? htmlspecialchars($distrito['d_nombre']) : ''; ?>" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')" onkeypress="return soloLetras(event)">
                        </div>

                        <div class="form-group">
                            <label for="d_descripcion">Descripción</label>
                            <textarea class="form-control" id="d_descripcion" name="d_descripcion" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
        onkeypress="return soloLetras(event)"><?php echo isset($distrito) ? htmlspecialchars($distrito['d_descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($distrito) ? 'display:none;' : ''; ?>">Crear Area</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($distrito) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Distritos</h3>
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Distrito.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2"/>
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        <button type="button" class="btn btn-success" id="btnCrearDistrito" data-bs-toggle="modal" data-bs-target="#distritoModal">
            Registrar Distrito
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($distritos as $are): ?>
                <tr>
                    <td><?php echo htmlspecialchars($are['id_distrito']); ?></td>
                    <td><?php echo htmlspecialchars($are['d_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($are['d_descripcion']); ?></td>
                    <td>
                        <div class="d-flex flex-column flex-md-row gap-1">
                            <a href="ADM_Distrito.php?id_distrito=<?php echo $are['id_distrito']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="ADM_Distrito.php?id_distrito=<?php echo $are['id_distrito']; ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta distrito?');">Eliminar</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php if (isset($distrito)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('distritoModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearDistrito").addEventListener("click", function () {
    const form = document.getElementById("formMaterial");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_distrito");
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
