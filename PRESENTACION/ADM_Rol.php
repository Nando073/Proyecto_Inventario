<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador']);
require_once '../NEGOCIO/N_Rol.php';
$rolService = new N_Rol();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$rol = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_rol'])) {
    $id_rol = filter_input(INPUT_GET, 'id_rol', FILTER_VALIDATE_INT);
    if ($id_rol) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                $resultado = $rolService->eliminar($id_rol);

                if ($resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Rol eliminado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar el rol porque tiene " 
                                         . $resultado['cantidad_usuarios'] 
                                         . " usuario(s) asignado(s).";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                
                header('Location: ADM_Rol.php');
                exit();

            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar rol: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ADM_Rol.php');
                exit();
            }
        } else {
            // Modo edición
            $rol = $rolService->buscarPorId($id_rol);
            if (!$rol) {
                $_SESSION['mensaje'] = "No se encontró el rol.";
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
    $id_rol = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
    $r_nombre = trim(strip_tags($_POST['r_nombre'] ?? ''));
    $r_descripcion = trim(strip_tags($_POST['r_descripcion'] ?? ''));

    if ($accion === 'Registrar') {
        
        if ($r_nombre && $r_descripcion) {
            try {
                $resultado = $rolService->adicionar($r_nombre, $r_descripcion);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Rol registrado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo registrar el Rol. ya existe";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al registrar Rol: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            header('Location: ADM_Rol.php');
            exit();
        } 
    } elseif ($accion === 'guardar') {
        
        if ($id_rol && $r_nombre && $r_descripcion) {
            try {
                $resultado = $rolService->modificar($id_rol, $r_nombre, $r_descripcion);
                if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Rol modificado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo modificar el Rol.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al modificar el Rol: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            header('Location: ADM_Rol.php');
            exit();
        }
    }
}

// Obtener la lista de areas
$areas = $rolService->obtenerRoles();
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
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 mx-auto" style="max-width: 520px;">
  <div class="row g-0 align-items-center">
    <!-- Imagen -->
    <div class="col-5">
      <img src="../IMG/rol.jpg" alt="Roles" class="img-fluid h-100 object-fit-cover">
    </div>

    <!-- Contenido -->
    <div class="col-7 bg-light">
      <div class="card-body d-flex flex-column justify-content-center h-100 text-center p-3">
        <h4 class="card-title fw-bold" style="color: #8e44ad;">Gestión de Roles</h4>
        <p class="card-text text-secondary mb-0">Define y administra los roles del sistema para controlar los permisos de acceso.</p>
      </div>
    </div>
  </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="RolModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Formulario Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formRol" action="ADM_Rol.php" method="post">
                        <input type="hidden" name="id_rol" id="id_rol" value="<?php echo isset($rol) ? $rol['id_rol'] : ''; ?>">

                        <div class="form-group">
                            <label for="r_nombre">Nombre</label>
                            <input type="text" class="form-control" id="r_nombre" name="r_nombre" value="<?php echo isset($rol) ? htmlspecialchars($rol['r_nombre']) : ''; ?>" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
                            onkeypress="return soloLetras(event)">
                        </div>

                        <div class="form-group">
                            <label for="r_descripcion">Descripción</label>
                            <textarea class="form-control" id="r_descripcion" name="r_descripcion" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
                            onkeypress="return soloLetras(event)"><?php echo isset($rol) ? htmlspecialchars($rol['r_descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="Registrar" class="btn btn-primary" style="<?php echo isset($rol) ? 'display:none;' : ''; ?>">Registrar Rol</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($rol) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Roles</h3>
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Rol.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2"/>
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        <button type="button" class="btn btn-success" id="btnRegistrarRol" data-bs-toggle="modal" data-bs-target="#RolModal">
            Registrar Rol
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
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($areas as $are): ?>
            <tr>
                <td><?php echo htmlspecialchars($are['r_nombre']); ?></td>
                <td><?php echo htmlspecialchars($are['r_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($are['r_fecha']); ?></td>
                <td>
                    <div class="d-flex flex-column flex-md-row gap-1">
                        <a href="ADM_Rol.php?id_rol=<?php echo $are['id_rol']; ?>" class="btn btn-warning">Editar</a>
                        <a href="ADM_Rol.php?id_rol=<?php echo $are['id_rol']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta rol?');">Eliminar</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
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
    if (btnGuardar) btnGuardar.style.display = "none";

    // Activa el botón de Registrar
    const btnRegistrar = form.querySelector('button[name="accion"][value="Registrar"]');
    if (btnRegistrar) btnRegistrar.style.display = "inline-block";
});
</script>

</body>
</html>
