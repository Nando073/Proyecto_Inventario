<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador', 'Supervisor']);
require_once '../NEGOCIO/N_Funcionario.php';
require_once '../NEGOCIO/N_Area.php';
require_once '../NEGOCIO/N_Cargo.php';
$funcionarioService = new N_Funcionario();
$areaService = new N_Area();
$cargoService = new N_Cargo();

// Filtro por estado (activo/inactivo)
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'activo'; // por defecto activos

$funcionario = null;
if (isset($_GET['id_funcionario'])) {
    $funcionario_id = filter_input(INPUT_GET, 'id_funcionario', FILTER_VALIDATE_INT);
    if ($funcionario_id) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Eliminado lógico
            try {
                $resultado = $funcionarioService->eliminar($funcionario_id);
                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Funcionario eliminado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar el Funcionario.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar Funcionario: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
            header('Location: ADM_Funcionario.php');
            exit();
        

        } elseif (isset($_GET['action']) && $_GET['action'] === 'activar') {
            // Activar funcionario y capturar resultado
            $resultado = $funcionarioService->activarFuncionario($funcionario_id);

            if ($resultado) {
                $mensaje = "Funcionario activado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "El funcionario no pudo ser activado. Verifique el área y cargo.";
                $tipo_mensaje = "danger";
            }

            // Guardar mensaje en sesión para mostrar después del redirect
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = $tipo_mensaje;

            header('Location: ADM_Funcionario.php?estado=activo'); 
            exit();

        } else {
            // Cargar un funcionario para edición
            $funcionario = $funcionarioService->buscarPorId($funcionario_id);
            if (!$funcionario) {
                echo "Funcionario no encontrado.";
                exit();
            }
        }
    } else {
        echo "ID inválido.";
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
    $f_nombre = trim(filter_input(INPUT_POST, 'f_nombre', FILTER_SANITIZE_STRING));
    $f_apellido = trim(filter_input(INPUT_POST, 'f_apellido', FILTER_SANITIZE_STRING));
    $f_correo = trim(filter_input(INPUT_POST, 'f_correo', FILTER_SANITIZE_EMAIL));
    $area = filter_input(INPUT_POST, 'area', FILTER_VALIDATE_INT);
    $id_cargo = filter_input(INPUT_POST, 'id_cargo', FILTER_VALIDATE_INT);
    $CI = trim(filter_input(INPUT_POST, 'CI', FILTER_VALIDATE_INT));
    $complemento = trim(filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_STRING));
    $complemento = $complemento !== "" ? $complemento : null;
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

    // Validar campos básicos
    //$resultado = $funcionarioService->buscarPorId($id_funcionario);
    
    if ($accion === 'crear') {
            if ($f_nombre && $f_apellido && $f_correo && $area && $id_cargo && $CI) {
                try {
                    $resultado = $funcionarioService->adicionar($f_nombre, $f_apellido, $f_correo, $area, $id_cargo, $CI, $complemento);

                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Funcionario registrado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo registrar el funcionario. C.I. o Correo ya registrado.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al registrar funcionario: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                 header('Location: ADM_Funcionario.php');
                 exit();
            }
        } elseif ($accion === 'guardar') {
            if ($f_nombre && $f_apellido && $f_correo && $area && $id_cargo && $CI && $id_funcionario) {
                try {
                    $resultado = $funcionarioService->modificar($id_funcionario, $f_nombre, $f_apellido, $f_correo, $area, $id_cargo, $CI, $complemento);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Funcionario modificado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo modificar el funcionario.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al modificar funcionario: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                header('Location: ADM_Funcionario.php');
                exit();
            } else {
                $_SESSION['mensaje'] = "Error: El funcionario con el ID $id_funcionario no existe. No se puede modificar.";
                $_SESSION['tipo_mensaje'] = "danger";
            }
            
        } 
}



$funcionarios = $funcionarioService->obtenerFuncionarios();
// Llama al método para obtener las áreas
$areas = $areaService->obtenerAreas();  // Obtienes todas las áreas de la base de datos
$cargos = $cargoService->obtenerCargos(); // Obtienes todos los cargos de la base de datos
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $funcionarios = $funcionarioService->buscarPorSimilitud($searchTerm);
}

// Filtrar en PHP según estado
if ($estadoFiltro === 'activo') {
    $funcionarios = array_filter($funcionarios, function($f) {
        return $f['f_estado'] == 1;
    });
} elseif ($estadoFiltro === 'inactivo') {
    $funcionarios = array_filter($funcionarios, function($f) {
        return $f['f_estado'] == 0;
    });
}
// Opcional: para mostrar en la vista como "Activo" o "Inactivo"
foreach ($funcionarios as &$funcionariO) {
    $funcionariO['estado_texto'] = $funcionariO['f_estado'] == 1 ? 'Activo' : 'Inactivo';
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
    <title>Administrar Funcionarios</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

    <main>
    <!-- Modifica tu tarjeta principal -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 mx-auto" style="max-width: 520px;">
  <div class="row g-0 align-items-center">
    <!-- Imagen -->
    <div class="col-5">
      <img src="../IMG/funcionario.jpg" alt="Funcionarios" class="img-fluid h-100 object-fit-cover">
    </div>

    <!-- Contenido -->
    <div class="col-7 bg-light">
      <div class="card-body d-flex flex-column justify-content-center h-100 text-center p-3">
        <h4 class="card-title fw-bold" style="color: #3498db;">Gestión de Funcionarios</h4>
        <p class="card-text text-secondary mb-0">Administra el registro y control del personal de la institución.</p>
      </div>
    </div>
  </div>
</div>

    

        <!-- Formulario para crear o editar -->
        <!-- Formulario único para crear o guardar cambios -->
<!-- Modal -->
<div class="modal fade" id="funcionarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Puedes cambiar modal-lg por modal-md si lo prefieres -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="usuarioModalLabel">Formulario Funcionarios</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Aquí va tu formulario -->
        <form id="formFunci" action="ADM_Funcionario.php" method="post">
            
                <div class="form-group">
                    <input type="hidden" class="form-control" id="id_funcionario" name="id_funcionario" value="<?php echo isset($funcionario) ? $funcionario['id_funcionario'] : ''; ?>"required>
                </div>
           
            <div class="form-group">
                <label for="f_nombre">Nombre</label>
                <input type="text" class="form-control" id="f_nombre" name="f_nombre" value="<?php echo isset($funcionario) ? htmlspecialchars($funcionario['f_nombre']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="f_apellido">Apellido</label>
                <input type="text" class="form-control" id="f_apellido" name="f_apellido" value="<?php echo isset($funcionario) ? htmlspecialchars($funcionario['f_apellido']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="f_correo">Correo</label>
                <input type="email" class="form-control" id="f_correo" name="f_correo" value="<?php echo isset($funcionario) ? htmlspecialchars($funcionario['f_correo']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="area">Área</label>
                <select name="area" id="area" class="form-control" required>
                    <option value="">Seleccione un área</option>
                    <?php
                        // Asegúrate de que $areas contiene los datos de las áreas
                        foreach ($areas as $area) {
                            // Si estamos editando un funcionario, seleccionamos el área previamente asignada
                            $selected = (isset($funcionario) && $funcionario['area'] == $area['id_area']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($area['id_area']) . "' $selected>" . htmlspecialchars($area['a_nombre']) . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_cargo">Cargo</label>
                <select name="id_cargo" id="id_cargo" class="form-control" required>
                    <option value="">Seleccione un cargo</option>
                    <?php
                        // Asegúrate de que $cargos contiene los datos de los cargos
                        foreach ($cargos as $cargo) {
                            // Si estamos editando un funcionario, seleccionamos el cargo previamente asignado
                            $selected = (isset($funcionario) && $funcionario['id_cargo'] == $cargo['id_cargo']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($cargo['id_cargo']) . "' $selected>" . htmlspecialchars($cargo['nombre_c']) . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="CI">Cédula de Identidad</label>
                <input type="number" class="form-control" id="CI" name="CI" value="<?php echo isset($funcionario) ? htmlspecialchars($funcionario['ci']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <input type="checkbox" id="toggleComplemento">
                <label for="toggleComplemento">Añadir complemento</label>
            </div>

            <div class="form-group" id="complementoGroup" style="display:none;">
                <label for="complemento">Complemento</label>
                <input type="text" class="form-control" id="complemento" name="complemento" 
                    value="<?php echo isset($funcionario) ? htmlspecialchars($funcionario['complemento_ci']) : ''; ?>">
            </div>
            <!-- Botones dentro del modal -->
            <div class="mt-3">
                <button type="submit" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($funcionario) ? 'display:none;' : ''; ?>">Crear Funcionario</button>
                <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($funcionario) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
              </div>
        </form>
      </div>
    </div>
  </div>
</div>



        <!-- Lista de funcionarios -->
        <h3 class="mt-5">Administrar Funcionarios</h3>
        <!-- Reemplaza tu formulario de búsqueda actual con este -->
            <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Funcionario.php" method="get">
                <!-- Búsqueda -->
                <div class="d-flex flex-grow-1 me-md-2">
                    <input type="text" name="search" placeholder="Buscar por nombre" 
                        value="<?php echo htmlspecialchars($searchTerm); ?>" 
                        class="form-control me-2">
                    <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
                </div>
                
                <!-- Botones de administración -->
                <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <!-- Dropdown -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Todos los Funcionarios
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo $estadoFiltro === 'activo' ? 'active' : ''; ?>" 
                                    href="ADM_Funcionario.php?estado=activo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                                        Activos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo $estadoFiltro === 'inactivo' ? 'active' : ''; ?>" 
                                    href="ADM_Funcionario.php?estado=inactivo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                                        Inactivos
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Botón Registrar -->
                        <button type="button" class="btn btn-success" id="btnCrearFunci" data-bs-toggle="modal" data-bs-target="#funcionarioModal">
                            Registrar
                        </button>
                    </div>
                <?php endif; ?>
            </form>
<!-- Mensaje -->
<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> mt-3">
        <?= htmlspecialchars($_SESSION['mensaje']); ?>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
<?php endif; ?>

        <!-- Reemplaza tu tabla actual con esto -->
                    <div class="table-responsive">
                        <table class="table table-bordered mt-3">
                            <!-- El contenido de tu tabla permanece igual -->
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Correo</th>
                                    <th>Area</th>
                                    <th>Cargo</th>
                                    <th>Cédula de Identidad</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                    <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($funcionarios)): ?>
                                    <?php foreach ($funcionarios as $Nfuncionarios): ?>
                                        <tr>
                                            
                                            <td><?php echo htmlspecialchars($Nfuncionarios['f_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($Nfuncionarios['f_apellido']); ?></td>
                                            <td><?php echo htmlspecialchars($Nfuncionarios['f_correo']); ?></td>
                                            <td><?php echo htmlspecialchars($Nfuncionarios['a_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($Nfuncionarios['nombre_c']); ?></td>
                                            <td><?php echo htmlspecialchars($Nfuncionarios['ci'] . ' ' . $Nfuncionarios['complemento_ci']); ?></td>
                                            <td><?php echo htmlspecialchars($Nfuncionarios['f_fecha_registro']); ?></td>
                                            <td>
                                                <?php if ($Nfuncionarios['f_estado'] == 1): ?>
                                                    <span style="color: green; font-weight: bold;">Activo</span>
                                                <?php else: ?>
                                                    <span style="color: red; font-weight: bold;">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
                                                <td>
                                                    <div class="d-flex flex-column flex-md-row gap-1">
                                                        <?php if ($Nfuncionarios['f_estado'] == 1): ?>
                                                            <!-- Si es activo -->
                                                                <a href="ADM_Funcionario.php?id_funcionario=<?php echo $Nfuncionarios['id_funcionario']; ?>" class="btn btn-warning">Editar</a>
                                                                <a href="ADM_Funcionario.php?id_funcionario=<?php echo $Nfuncionarios['id_funcionario']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este funcionario?');">Eliminar</a>
                                                        <?php else: ?>
                                                            <!-- Si es inactivo -->
                                                            <a href="ADM_Funcionario.php?id_funcionario=<?= $Nfuncionarios['id_funcionario']; ?>&action=activar" class="btn btn-primary" onclick="return confirm('¿Deseas activar este funcionario?');">Activar</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No hay funcionarios para mostrar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </main>

                <?php if (isset($funcionario)): ?>
                <script>
                    var myModal = new bootstrap.Modal(document.getElementById('funcionarioModal'));
                    window.addEventListener('load', () => {
                        myModal.show();
                    });
                </script>
                <?php endif; ?>
<script>
    document.getElementById("btnCrearFunci").addEventListener("click", function () {
        const form = document.getElementById("formFunci");

        // Limpia todos los inputs manualmente
        form.querySelectorAll("input").forEach(input => {
            input.value = "";
        });

        // Si existe el campo oculto id_funcionario, lo eliminamos del DOM directamente
        const idInput = document.getElementById("id_funcionario");
        if (idInput) idInput.remove();

        // Desactiva el botón "Guardar Cambios"
        const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
        if (btnGuardar) btnGuardar.style.display = "none";

        // Activa el botón "Crear Usuario"
        const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
        if (btnCrear) btnCrear.style.display = "inline-block";
    });
    document.getElementById('toggleComplemento').addEventListener('change', function () {
    let complementoGroup = document.getElementById('complementoGroup');
    complementoGroup.style.display = this.checked ? 'block' : 'none';
});
</script>



</body>
</html>
