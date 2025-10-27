<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador']);
require_once '../NEGOCIO/N_Usuario.php';
require_once '../NEGOCIO/N_Funcionario.php';

$usuarioService = new N_Usuario();
$funcionarioService = new N_Funcionario();

$usuario = null;
$id_funcionario_actual = null;

// Filtro por estado (activo/inactivo)
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'activo'; // por defecto activos

// Verificar si llega un ID en la URL
if (isset($_GET['id_usuario'])) {
    $usuario_id = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT);
    if ($usuario_id) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                $resultado = $usuarioService->eliminar($usuario_id);
                 if ($resultado['success']) {
                    $_SESSION['mensaje'] = "Usuario eliminada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar el Usuario";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar Usuario: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
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
$Funcionarios = $funcionarioService->ObtenerFuncionariosDisponibles($id_funcionario_actual);

// Manejo de formularios (crear/editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $usuarioNombre = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    $clave = trim($_POST['clave']);
    $id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

    $resultado = $usuarioService->buscarPorId($id_usuario);
    if ($usuarioNombre && $id_funcionario) {

        if ($accion === 'crear') {
            if ($existingUser) {
                echo "Error: El usuario con el ID $id_usuario ya existe.";
            } else {
                if ($clave) {
                    $clave = password_hash($clave, PASSWORD_DEFAULT);
                    try {
                    $resultado = $usuarioService->adicionar($usuarioNombre, $clave, $id_funcionario);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Usuario registrado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo registrar el Usuario. Nombre de usuario ya registrado ";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al registrar usuario: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                    header('Location: ADM_Usuario.php');
                    exit();
                
                }
            }
        } elseif ($accion === 'guardar') {
            if ($id_usuario && $usuarioNombre && $id_funcionario) {
                // Si la clave está vacía, conserva la anterior
                if (empty($clave)) {
                    $existingUser = $usuarioService->buscarPorId($id_usuario);
                    $clave = $existingUser['clave'];
                } else {
                    $clave = password_hash($clave, PASSWORD_DEFAULT);
                }

                try {
                    $resultado = $usuarioService->modificar($id_usuario, $usuarioNombre, $clave, $id_funcionario);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Usuario modificado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo modificar el Usuario.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al modificar Usuario: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }

                header('Location: ADM_Usuario.php');
                exit();
            }
        }

    } 
}


// Obtener listas
//$funcionarios = $funcionarioService->ObtenerFuncionariosDisponibles();
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
//buscar por termino 
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $usuarios = $usuarioService->buscarPorSimilitud($searchTerm);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../DEMO/styles.css?v=<?php echo(rand()); ?>"/> 
    <script src="../DEMO/contrarer.js" defer></script>
    <title>Administrar Usuarios</title>

</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <!-- Tarjeta Responsiva -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 mx-auto" style="max-width: 520px;">
  <div class="row g-0 align-items-center">
    <!-- Imagen -->
    <div class="col-5">
      <img src="../IMG/usuario.png" alt="Usuarios" class="img-fluid h-100 object-fit-cover">
    </div>

    <!-- Contenido -->
    <div class="col-7 bg-light">
      <div class="card-body d-flex flex-column justify-content-center h-100 text-center p-3">
        <h4 class="card-title fw-bold" style="color: #5DADE2;">Usuarios</h4>
        <p class="card-text text-secondary mb-0">Administra los usuarios del sistema y sus permisos de acceso.</p>
      </div>
    </div>
  </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalLabel">Formulario Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsuario" action="ADM_Usuario.php" method="post">
                        <input type="hidden" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo isset($usuario) ? $usuario['id_usuario'] : ''; ?>" required>
                        
                        <div class="form-group mb-3">
                            <label for="usuario">Nombre de usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo isset($usuario) ? htmlspecialchars($usuario['usuario']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="clave">Contraseña</label>
                            <div class="input-group">
                               <input type="password" class="form-control" id="clave" name="clave"
                                pattern="(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}"
                                title="Debe tener al menos 8 caracteres, una mayúscula y un número">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    👁
                                </button>
                            </div>
                        </div>
                         
                        <div class="form-group mb-3">
                            <label for="funcionario">Funcionario</label>
                            <select name="id_funcionario" id="id_funcionario" class="form-control" required>
                                <option value="">Seleccione un funcionario</option>
                                <?php foreach ($Funcionarios as $Funcionario): ?>
                                    <?php
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
                        <button type="submit" id="btnCrear" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($usuario) ? 'display:none;' : ''; ?>">Crear Usuario</button>
                        <button type="submit" id="btnGuardar" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($usuario) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Búsqueda Responsivo -->
    <h3 class="mt-5">Administrar Usuarios</h3>
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Usuario.php" method="get">
        <!-- Búsqueda -->
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2">
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        
        <!-- Botones de administración -->
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Todos
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
            
            <button type="button" class="btn btn-success" id="btnCrearUsuario" data-bs-toggle="modal" data-bs-target="#Modal">
                Registrar
            </button>
        </div>
    </form>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> mt-3">
            <?= htmlspecialchars($_SESSION['mensaje']); ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Tabla Responsiva -->
    <div class="table-responsive">
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
                                <div class="d-flex flex-column flex-md-row gap-1">
                                    <?php if ($Nusuario['estado'] == 1): ?>
                                        <a href="ADM_Usuario.php?id_usuario=<?php echo $Nusuario['id_usuario']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                        <a href="ADM_Usuario.php?id_usuario=<?php echo $Nusuario['id_usuario']; ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>

                                    <?php else: ?>
                                        <a href="ADM_Usuario.php?id_usuario=<?= $Nusuario['id_usuario']; ?>&action=activar" class="btn btn-primary btn-sm">Activar</a>
                                    <?php endif; ?>
                                </div>
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
    const btnCrearUsuario = document.getElementById("btnCrearUsuario"); // botón que abre el modal para crear
    const btnCrear = document.getElementById("btnCrear");
    const btnGuardar = document.getElementById("btnGuardar");
    const form = document.getElementById("formUsuario");
    const idInput = document.getElementById("id_usuario");
    const passwordInput = document.getElementById("clave");
    const togglePasswordBtn = document.getElementById("togglePassword");

    // 🔹 Mostrar/ocultar contraseña
    togglePasswordBtn.addEventListener("click", function () {
        const isPassword = passwordInput.type === "password";
        passwordInput.type = isPassword ? "text" : "password";
        togglePasswordBtn.textContent = isPassword ? "🙈" : "👁";
    });

    // 🔹 Cuando se presiona "Crear Usuario"
    btnCrearUsuario.addEventListener("click", function () {
        // Limpiar el formulario
        form.querySelectorAll("input, select, textarea").forEach(input => {
            input.value = "";
            if (input.type === "checkbox" || input.type === "radio") {
                input.checked = false;
            }
        });

        if (idInput) idInput.value = "";

        // Mostrar/ocultar botones
        btnCrear.style.display = "inline-block";
        btnGuardar.style.display = "none";

        // Hacer que la contraseña sea obligatoria
        passwordInput.required = true;
        passwordInput.value = ""; // asegúrate de limpiarla
    });

    // 🔹 Cuando se presiona "Editar Usuario"
    btnGuardar.forEach(function(btn) {
        btn.addEventListener("click", function() {
            const usuarioId = this.dataset.id; // id del usuario a editar
            idInput.value = usuarioId;

            // Mostrar/ocultar botones
            btnCrear.style.display = "none";
            btnGuardar.style.display = "inline-block";

            // La contraseña NO será obligatoria en edición
            passwordInput.required = false;
            passwordInput.value = ""; // mantener vacío para no mostrar hash
        });
    });
    // 🔐 Validación de contraseña segura
    form.addEventListener("submit", function (event) {
        if (passwordInput.required || passwordInput.value.trim() !== "") {
            const password = passwordInput.value.trim();

            // Expresión regular: mínimo 8 caracteres, 1 mayúscula, 1 número
            const regex = /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;

            if (!regex.test(password)) {
                event.preventDefault(); // Detiene el envío
                alert("La contraseña debe tener al menos 8 caracteres, incluir una mayúscula y un número.");
                passwordInput.focus();
            }
        }
    });
});
</script>


</body>
</html>