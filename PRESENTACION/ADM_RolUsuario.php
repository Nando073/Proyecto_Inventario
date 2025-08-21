<?php
require_once '../NEGOCIO/N_RolUsuario.php';
$rolUsuarioService = new N_RolUsuario();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$rolUsuario = null;

if (isset($_GET['id_RolUsuario'])) {
    $id_RolUsuario = filter_input(INPUT_GET, 'id_RolUsuario', FILTER_VALIDATE_INT);

    if ($id_RolUsuario) {
        // Registrar una instancia del servicio de negocio
        $rolUsuarioService = new N_RolUsuario();
        
        // Verifica si se ha solicitado eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar al rolUsuario
            $rolUsuarioService->eliminar($id_RolUsuario);
            // Redirigir al listado después de eliminar
            header('Location: ADM_RolUsuario.php');
            exit();
        } else {
            // Llamada al método de negocio para obtener los datos de RolUsuario
            $rolUsuario = $rolUsuarioService->buscarPorId($id_RolUsuario);
            if (!$rolUsuario) {
                echo "No se encontró el rolUsuario.";
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
        $id_rol = trim(strip_tags($_POST['id_rol'] ?? ''));
        $id_usuario = trim(strip_tags($_POST['id_usuario'] ?? ''));
        if ( $id_rol && $id_usuario ) {    
            $rolUsuarioService->adicionar($id_rol, $id_usuario);
            header('Location: ADM_RolUsuario.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_RolUsuario = filter_input(INPUT_POST, 'id_RolUsuario', FILTER_VALIDATE_INT);
        $id_rol = trim(strip_tags($_POST['id_rol'] ?? ''));
        $id_usuario = trim(strip_tags($_POST['id_usuario'] ?? ''));
        if ($id_RolUsuario &&  $id_rol && $id_usuario !== false) {
            $existing = $rolUsuarioService->buscarPorId($id_RolUsuario);
            if ($existing) {
                $rolUsuarioService->modificar($id_RolUsuario, $id_rol, $id_usuario);
                echo "";
                header('Location: ADM_RolUsuario.php');
                exit();
            } else {
                echo "Error: No existe la categoría con ID $id_RolUsuario.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Obtener la lista de los rolUsuario
$rol_usuario = $rolUsuarioService->buscarTodo();
//obtener la lista de usuarios y roles
$roles = $rolUsuarioService->obtenerRol();
$usuarios = $rolUsuarioService->obtenerUsuario();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $rol_usuario = $rolUsuarioService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Rol_Usuario</title>
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
                    <h4 class="card-title">ROL_USUARIO</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="RolUModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Registrar o Editar Rol_Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formRol_U" action="ADM_RolUsuario.php" method="post">
                        <input type="hidden" name="id_RolUsuario" id="id_RolUsuario" value="<?php echo isset($rolUsuario) ? $rolUsuario['id_rol_usuario'] : ''; ?>">
                        
                        <div class="form-group">
                            <label for="id_rol">ID Rol</label>
                            <select name="id_rol" id="id_rol" class="form-control" required>
                                <option value="">Seleccione un rol</option>
                                <?php
                                    // Llenar el select con los roles obtenidos
                                    foreach ($roles as $rol) {
                                        // Si estamos editando un rolUsuario, seleccionamos el rol previamente asignado
                                        $selected = (isset($rolUsuario) && $rolUsuario['id_rol'] == $rol['id_rol']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($rol['id_rol']) . "' $selected>" . htmlspecialchars($rol['r_nombre']) . "</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_usuario">ID Usuario</label>
                            <select name="id_usuario" id="id_usuario" class="form-control" required>
                                <option value="">Seleccione una usuario</option>
                                <?php
                                    // Llenar el select con los usuarios obtenidos
                                    foreach ($usuarios as $usuario) {
                                        // Si estamos editando un rolUsuario, seleccionamos el usuario previamente asignado
                                        $selected = (isset($rolUsuario) && $rolUsuario['id_usuario'] == $usuario['id_usuario']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($usuario['id_usuario']) . "' $selected>" . htmlspecialchars($usuario['nombre']) . "</option>";
                                    }
                                ?>
                            </select>                       
                        </div>

                        
                        <div class="mt-3">
                            <button type="submit" name="accion" value="Registrar" class="btn btn-primary">Registrar Rol_Usuario</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($rolUsuario) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Rol_Usuario</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_RolUsuario.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnRegistrarRol" data-bs-toggle="modal" data-bs-target="#RolUModal">
            Registrar Rol_Usuario
        </button>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Rol</th>
                <th>Usuario</th>
                <th>Fecha de registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rol_usuario as $ROL_U): ?>
            <tr>
                <td><?php echo htmlspecialchars($ROL_U['id_rol_usuario']); ?></td>
                <td><?php echo htmlspecialchars($ROL_U['r_nombre']); ?></td>
                <td><?php echo htmlspecialchars($ROL_U['f_nombre'] . ' ' . $ROL_U['f_apellido']); ?></td>
                <td><?php echo htmlspecialchars($ROL_U['fecha_registro']); ?></td>
                <td>
                    <a href="ADM_RolUsuario.php?id_RolUsuario=<?php echo $ROL_U['id_rol_usuario']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_RolUsuario.php?id_RolUsuario=<?php echo $ROL_U['id_rol_usuario']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta rolUsuario?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php if (isset($rolUsuario)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('RolUModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnRegistrarRol").addEventListener("click", function () {
    const form = document.getElementById("formRol_U");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_RolUsuario");
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
