<?php
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Usuario.php';

$usuarioService = new N_Usuario();

$usuario = null;
$id_funcionario_actual = null;

// Filtro por estado (activo/inactivo)
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'activo'; // por defecto activos

// Verificar si llega un ID en la URL
if (isset($_GET['id_usuario'])) {
    $usuario_id = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT);
    if ($usuario_id) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $usuarioService->eliminar($usuario_id);
            header('Location: ADM_Usuario.php');
            exit();
        } elseif (isset($_GET['action']) && $_GET['action'] === 'activar') {
            $resultado = $usuarioService->activarUsuario($usuario_id);

            if ($resultado) {
                $mensaje = "Usuario activado correctamente";
                $tipo = "success";
            } else {
                $mensaje = "El Funcionario está inactivo";
                $tipo = "danger";
            }

            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = $tipo;

            header('Location: ADM_Usuario.php?estado=activo');
            exit();
        } else {
            // Cargar un usuario para edición
            $usuario = $usuarioService->buscarPorId($usuario_id);
            if (!$usuario) {
                echo "No se encontró el usuario.";
                exit();
            }
            // Guardamos funcionario asignado para que aparezca en el select
            $id_funcionario_actual = $usuario['id_funcionario'];
        }
    } else {
        echo "ID inválido.";
        exit();
    }
}

// obtenemos los funcionarios disponibles
$Funcionarios = $usuarioService->ObtenerFuncionariosDisponibles($id_funcionario_actual);

// Manejo de formularios (crear/editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $usuarioNombre = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    $clave = trim($_POST['clave']);
    $id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

    if ($usuarioNombre && $id_funcionario) {
        $existingUser = $usuarioService->buscarPorId($id_usuario);

        if ($accion === 'crear') {
            if ($existingUser) {
                echo "Error: El usuario con el ID $id_usuario ya existe.";
            } else {
                if ($clave) {
                    $clave = password_hash($clave, PASSWORD_DEFAULT);
                    $usuarioService->adicionar($usuarioNombre, $clave, $id_funcionario);
                    header('Location: ADM_Usuario.php');
                    exit();
                } else {
                    echo "Error: La clave es obligatoria.";
                }
            }
        } elseif ($accion === 'guardar') {
            if ($existingUser) {
                if (empty($clave)) {
                    $clave = $existingUser['clave'];
                } else {
                    $clave = password_hash($clave, PASSWORD_DEFAULT);
                }

                $usuarioService->modificar($id_usuario, $usuarioNombre, $clave, $id_funcionario);
                header('Location: ADM_Usuario.php');
                exit();
            } else {
                echo "Error: El usuario no existe.";
            }
        } else {
            echo "Acción no válida.";
        }
    } else {
        echo "Todos los campos requeridos deben estar completos.";
    }
}

// Obtener listas
$funcionarios = $usuarioService->obtenerFuncionarios();
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';

$usuarios = $usuarioService->ObtenerUsuarios($searchTerm);

// Filtrar por estado
if ($estadoFiltro === 'activo') {
    $usuarios = array_filter($usuarios, fn($u) => $u['estado'] == 1);
} elseif ($estadoFiltro === 'inactivo') {
    $usuarios = array_filter($usuarios, fn($u) => $u['estado'] == 0);
}

// Texto de estado
foreach ($usuarios as &$usuariO) {
    $usuariO['estado_texto'] = $usuariO['estado'] == 1 ? 'Activo' : 'Inactivo';
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
    <script src="presentacion.js" defer></script>
    <title>Administrar Usuarios</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

    <main>
    <div class="card mb-4" style="max-width: 540px;margin-left: 60vh">
        <div class="row g-0">
          <div class="col-md-5">
              <img src="../IMG/img.png" class="img-fluid rounded-start">
          </div>
          <div class="col-md-7">
            <div class="card-body">
              <h4 class="card-title">USUARIOS</h4>
              <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
            </div>
          </div>
        </div>
      </div>
    

        <!-- Formulario para crear o editar -->
        <!-- Formulario único para crear o guardar cambios -->
<!-- Modal -->
<div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Puedes cambiar modal-lg por modal-md si lo prefieres -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel">Crear o Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Aquí va tu formulario -->
        <form id="formUsuario" action="ADM_Usuario.php" method="post">
            
                <div class="form-group">
                    <input type="hidden" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo isset($usuario) ? $usuario['id_usuario'] : ''; ?>"required>
                </div>

            <div class="form-group">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo isset($usuario) ? htmlspecialchars($usuario['usuario']) : ''; ?>" required>
            </div>
            <label for="clave">Contraseña</label>
            <div class="input-group">
                <input type="password" class="form-control" id="clave" name="clave" value="">
                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                    👁
                </button>
            </div>
             <div class="form-group">
                <label for="funcionario">Funcionario</label>
                <select name="id_funcionario" id="id_funcionario" class="form-control" required>
                    <option value="">Seleccione un funcionario</option>
                    <?php foreach ($Funcionarios as $Funcionario): ?>
                        <?php
                            // Si es edición, marcamos seleccionado el funcionario actual
                            $selected = (isset($usuario) && $usuario['id_funcionario'] == $Funcionario['id_funcionario']) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($Funcionario['id_funcionario']); ?>" <?= $selected; ?>>
                            <?= htmlspecialchars($Funcionario['f_nombre'] . ' ' . $Funcionario['f_apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Botones dentro del modal -->
            <div class="mt-3">
                <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Usuario</button>
                <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($usuario) ? '' : 'disabled'; ?>>Guardar Cambios</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>



        <!-- Lista de usuarios -->
<h3 class="mt-5">Administrar Usuarios</h3>
<form class="d-flex align-items-center mt-3" action="ADM_Usuario.php" method="get">
    <!-- Input de búsqueda -->
    <div class="d-flex">
        <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2" />
        <button type="submit" class="btn btn-info">Buscar</button>
    </div>

    <!-- Botones a la derecha -->
    <div class="d-flex align-items-center ms-auto">
        <!-- Aquí va el dropdown “Todos los Usuarios” -->
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Todos los Usuarios
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item <?php echo $estadoFiltro === 'activo' ? 'active' : ''; ?>" 
                       href="ADM_Usuario.php?estado=activo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                        Activos
                    </a>
                </li>
                <li>
                    <a class="dropdown-item <?php echo $estadoFiltro === 'inactivo' ? 'active' : ''; ?>" 
                       href="ADM_Usuario.php?estado=inactivo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                        Inactivos
                    </a>
                </li>
            </ul>
        </div>

        <!-- Botón Registrar Usuario -->
        <button type="button" class="btn btn-success" id="btnCrearUsuario" data-bs-toggle="modal" data-bs-target="#Modal">
            Registrar Usuario
        </button>
    </div>
</form>

<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> mt-3">
        <?= htmlspecialchars($_SESSION['mensaje']); ?>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
<?php endif; ?>


        <table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Clave (Encriptada)</th>
            <th>Funcionario</th>
            <th>Fecha Registro</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($usuarios)): ?>
            <?php foreach ($usuarios as $Nusuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($Nusuario['usuario']); ?></td>
                    <td>********</td>
                    <td><?php echo htmlspecialchars($Nusuario['f_nombre'] . ' ' . $Nusuario['f_apellido']); ?></td>
                    <td><?php echo htmlspecialchars($Nusuario['fecha_registro']); ?></td>
                    <td>
                        <?php if ($Nusuario['estado'] == 1): ?>
                            <span style="color: green; font-weight: bold;">Activo</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($Nusuario['estado'] == 1): ?>
                            <!-- Si es activo -->
                            <a href="ADM_Usuario.php?id_usuario=<?php echo $Nusuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                            <a href="ADM_Usuario.php?id_usuario=<?php echo $Nusuario['id_usuario']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                        <?php else: ?>
                            <!-- Si es inactivo -->
                            <a href="ADM_Usuario.php?id_usuario=<?= $Nusuario['id_usuario']; ?>&action=activar" class="btn btn-primary" onclick="return confirm('¿Deseas activar este usuario?');">Activar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No hay usuarios para mostrar.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

    </div>
                </main>

                <?php if (isset($usuario)): ?>
                <script>
                    var myModal = new bootstrap.Modal(document.getElementById('Modal'));
                    window.addEventListener('load', () => {
                        myModal.show();
                    });
                </script>
                <?php endif; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Referencias a los elementos
    const btnCrearUsuario = document.getElementById("btnCrearUsuario");
    const togglePasswordBtn = document.getElementById("togglePassword");
    const form = document.getElementById("formUsuario");
    const passwordInput = document.getElementById('clave');
    const idInput = document.getElementById("id_usuario");
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');

    // Función para limpiar formulario
    btnCrearUsuario.addEventListener("click", function () {
        // Limpia valores de todos los inputs
        form.querySelectorAll("input").forEach(input => input.value = "");

        // Limpia id_usuario (no lo elimina, solo lo vacía)
        if (idInput) idInput.value = "";

        // Desactiva "Guardar Cambios"
        if (btnGuardar) btnGuardar.disabled = true;

        // Activa "Crear Usuario"
        if (btnCrear) btnCrear.disabled = false;
    });

    // Función para mostrar/ocultar contraseña
    togglePasswordBtn.addEventListener('click', function () {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;

        // Cambia el icono del botón según el estado
        togglePasswordBtn.textContent = type === 'password' ? '👁' : '🙈';
    });
});
</script>

</body>
</html>
