<?php

require_once '../NEGOCIO/N_Usuario.php';
$usuarioService = new N_Usuario();

// Verifica si se pasa un ID en la URL para editar o eliminar
$usuario = null;

if (isset($_GET['id_usuario'])) {
    $usuario_id = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT);

    if ($usuario_id) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $usuarioService->eliminar($usuario_id);
            header('Location: ADM_Usuario.php');
            exit();
        } else {
            $usuario = $usuarioService->buscarPorId($usuario_id);
            if (!$usuario) {
                echo "No se encontró el usuario.";
            }
        }
    } else {
        echo "ID inválido.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $usuarioNombre = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
    $clave = trim($_POST['clave']); // No sanitizar, para mantener formato de hash
    $id_funcionario =filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

    if ($accion === 'crear') {
        if ($usuarioNombre && $clave && $id_funcionario) {
            $clave = trim($_POST['clave']);
            $clave = password_hash($clave, PASSWORD_DEFAULT); // Nueva clave
            $usuarioService->adicionar($usuarioNombre, $clave, $id_funcionario);
            header('Location: ADM_Usuario.php');
            exit();
        } else {
            echo "Error: Todos los campos son obligatorios para crear un nuevo usuario.";
        }
    } elseif ($accion === 'guardar') {
        if ($usuarioNombre && $id_funcionario) {
            $existingUser = $usuarioService->buscarPorId($id_usuario);
            if ($existingUser) {
                if (empty($clave)) {
                    $clave = $existingUser['clave']; // Mantiene la clave anterior si no se escribe nueva
                } else {
                    $clave = password_hash($clave, PASSWORD_DEFAULT); // Nueva clave
                }

                $usuarioService->modificar($id_usuario, $usuarioNombre, $clave, $id_funcionario, 1);
                header('Location: ADM_Usuario.php');
                exit();
            } else {
                echo "Error: El usuario con el ID $id_usuario no existe.";
            }
        } else {
            echo "Error: Todos los campos requeridos para modificar deben estar completos.";
        }
    } else {
        echo "Error: Acción no válida.";
    }
}

// Obtener la lista de usuarios
$usuarios = $usuarioService->buscarTodo();
$funcionarios = $usuarioService->obtenerFuncionarios();
// Buscar por término
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
    <link rel="stylesheet" href="../DEMO/styles.css?v=<?php echo(rand()); ?>"> 
    <script src="../DEMO/contrarer.js" defer></script>
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
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Puedes cambiar modal-lg por modal-md si lo prefieres -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="usuarioModalLabel">Crear o Editar Usuario</h5>
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
                <select name="funcionario" id="funcionario" class="form-control" required>
                    <option value="">Seleccione un funcionario</option>
                    <?php
                        // Asegúrate de que $funcionarios contiene los datos de los funcionarios
                        foreach ($funcionarios as $funcionario) {
                            // Si estamos editando un funcionario, seleccionamos el funcionario previamente asignado
                            $selected = (isset($usuario) && $usuario['id_funcionario'] == $funcionario['id_funcionario']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($funcionario['id_funcionario']) . "' $selected>" . htmlspecialchars($funcionario['f_nombre']) . "</option>";
                        }
                    ?>
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
        <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Usuario.php" method="get">
            <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
            </div>
                           <!-- Botón que activa el modal -->
            <button type="button" class="btn btn-success m-3" id="btnCrearUsuario" data-bs-toggle="modal" data-bs-target="#usuarioModal">
                Registrar Usuario
            </button>

        </form>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Clave (Encriptada)</th>
                    <th>Funcionario</th>
                    <th>Estado</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $Nusuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($Nusuario['id_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($Nusuario['usuario']); ?></td>
                        <td>********</td>
                        <td><?php echo htmlspecialchars($Nusuario['f_nombre'] . ' ' . $Nusuario['f_apellido']); ?></td>
                        <td>
                            <?php if ($Nusuario['estado'] == 1): ?>
                                <span style="color: green; font-weight: bold;">Activo</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($Nusuario['fecha_registro']); ?></td>
                        <td>
                            <a href="ADM_Usuario.php?id_usuario=<?php echo $Nusuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                            <a href="ADM_Usuario.php?id_usuario=<?php echo $Nusuario['id_usuario']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
                </main>

                <?php if (isset($usuario)): ?>
                <script>
                    var myModal = new bootstrap.Modal(document.getElementById('usuarioModal'));
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
