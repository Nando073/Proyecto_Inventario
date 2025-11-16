<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador', 'Supervisor']);
require_once '../NEGOCIO/N_Distrital.php';
require_once '../NEGOCIO/N_Distrito.php';
$distritalService = new N_Distrital();
$distroService = new N_Distrito();

// Filtro por estado (activo/inactivo)
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'activo'; // por defecto activos

$distrital = null;
$id_distrito_actual = null; // Para edición

if (isset($_GET['id_distrital'])) {
    $distrital_id = filter_input(INPUT_GET, 'id_distrital', FILTER_VALIDATE_INT);
    if ($distrital_id) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Eliminado lógico
            try {
                $resultado = $distritalService->eliminar($distrital_id);
                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Distrital eliminado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar al Distrital.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar Distrital: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
            header('Location: ADM_Distrital.php');
            exit();
        

        } elseif (isset($_GET['action']) && $_GET['action'] === 'activar') {
            // Activar distrital y capturar resultado
            $resultado = $distritalService->activarDistrital($distrital_id);

            if ($resultado) {
                $mensaje = "Distrital activado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "El distrital no pudo ser activado. Verifique el distrito.";
                $tipo_mensaje = "danger";
            }

            // Guardar mensaje en sesión para mostrar después del redirect
            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = $tipo_mensaje;

            header('Location: ADM_Distrital.php?estado=activo'); 
            exit();

        } else {
            // Cargar un distrital para edición
            $distrital = $distritalService->buscarPorId($distrital_id);
            if (!$distrital) {
                echo "Distrital no encontrado.";
                exit();
            }
            // Guardamos distrito actual para que aparezca en el select
            $id_distrito_actual = $distrital['id_distrito'];
        }
    } else {
        echo "ID inválido.";
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_distrital = filter_input(INPUT_POST, 'id_distrital', FILTER_VALIDATE_INT);
    $di_nombre = trim(filter_input(INPUT_POST, 'di_nombre', FILTER_SANITIZE_STRING));
    $di_apellido = trim(filter_input(INPUT_POST, 'di_apellido', FILTER_SANITIZE_STRING));
    $di_correo = trim(filter_input(INPUT_POST, 'di_correo', FILTER_SANITIZE_EMAIL));
    $id_distrito = filter_input(INPUT_POST, 'id_distrito', FILTER_VALIDATE_INT);
    $di_ci = trim(filter_input(INPUT_POST, 'di_ci', FILTER_VALIDATE_INT));
    $ci_complemento = trim(filter_input(INPUT_POST, 'ci_complemento', FILTER_SANITIZE_STRING));
    $ci_complemento = $ci_complemento !== "" ? $ci_complemento : null;
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

    // Validar campos básicos
    //$resultado = $distritalService->buscarPorId($id_distrital);
    
    if ($accion === 'crear') {
            if ($di_nombre && $di_apellido && $di_correo && $id_distrito && $di_ci) {
                try {
                    $resultado = $distritalService->adicionar($di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento);

                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Distrital registrado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo registrar el distrital. C.I. o Correo ya registrado.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al registrar distrital: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                 header('Location: ADM_Distrital.php');
                 exit();
            }
        } elseif ($accion === 'guardar') {
            if ($di_nombre && $di_apellido && $di_correo && $id_distrito && $di_ci && $id_distrital) {
                try {
                    $resultado = $distritalService->modificar($id_distrital, $di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento);
                    if (isset($resultado['success']) && $resultado['success'] == 1) {
                        $_SESSION['mensaje'] = "Distrital modificado correctamente.";
                        $_SESSION['tipo_mensaje'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "No se pudo modificar el distrital.";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = "Error al modificar distrital: " . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                header('Location: ADM_Distrital.php');
                exit();
            } else {
                $_SESSION['mensaje'] = "Error: El distrital con el ID $id_distrital no existe. No se puede modificar.";
                $_SESSION['tipo_mensaje'] = "danger";
            }
            
        } 
}



$distritales = $distritalService->obtenerdistritales();

// Obtener distritos disponibles (sin distrital activo asignado o el distrito actual en edición)
$distritos = $distroService->obtenerDistritosDisponibles($id_distrito_actual);
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $distritales = $distritalService->buscarPorSimilitud($searchTerm);
}

// Filtrar en PHP según estado
if ($estadoFiltro === 'activo') {
    $distritales = array_filter($distritales, function($d) {
        return isset($d['di_estado']) && $d['di_estado'] == 1;
    });
} elseif ($estadoFiltro === 'inactivo') {
    $distritales = array_filter($distritales, function($d) {
        return isset($d['di_estado']) && $d['di_estado'] == 0;
    });
}
// Opcional: para mostrar en la vista como "Activo" o "Inactivo"
foreach ($distritales as &$distritalActivo) {
    $distritalActivo['estado_texto'] = (isset($distritalActivo['di_estado']) && $distritalActivo['di_estado'] == 1) ? 'Activo' : 'Inactivo';
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
    <title>Administrar Distritales</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

    <main>
    <!-- Modifica tu tarjeta principal -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 mx-auto" style="max-width: 520px;">
  <div class="row g-0 align-items-center">
    <!-- Imagen -->
    <div class="col-5">
      <img src="../IMG/funcionario.jpg" alt="Distritales" class="img-fluid h-100 object-fit-cover">
    </div>

    <!-- Contenido -->
    <div class="col-7 bg-light">
      <div class="card-body d-flex flex-column justify-content-center h-100 text-center p-3">
        <h4 class="card-title fw-bold" style="color: #3498db;">Gestión de Distritales</h4>
        <p class="card-text text-secondary mb-0">Administra el registro y control del personal de cada distrito.</p>
      </div>
    </div>
  </div>
</div>

    

        <!-- Formulario para crear o editar -->
        <!-- Formulario único para crear o guardar cambios -->
<!-- Modal -->
<div class="modal fade" id="distritalModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Puedes cambiar modal-lg por modal-md si lo prefieres -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="usuarioModalLabel">Formulario Distritales</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Aquí va tu formulario -->
        <form id="formDistri" action="ADM_Distrital.php" method="post">
            
                <div class="form-group">
                    <input type="hidden" class="form-control" id="id_distrital" name="id_distrital" value="<?php echo isset($distrital) ? $distrital['id_distrital'] : ''; ?>"required>
                </div>
           
            <div class="form-group">
                <label for="di_nombre">Nombre</label>
                <input type="text" class="form-control" id="di_nombre" name="di_nombre" value="<?php echo isset($distrital) ? htmlspecialchars($distrital['di_nombre']) : ''; ?>" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
                onkeypress="return soloLetras(event)">
            </div>
            <div class="form-group">
                <label for="di_apellido">Apellido</label>
                <input type="text" class="form-control" id="di_apellido" name="di_apellido" value="<?php echo isset($distrital) ? htmlspecialchars($distrital['di_apellido']) : ''; ?>" required oninput="this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g, '')"
                onkeypress="return soloLetras(event)">
            </div>
            
            <div class="form-group">
                <label for="di_correo">Correo</label>
                <input type="email" class="form-control" id="di_correo" name="di_correo" value="<?php echo isset($distrital) ? htmlspecialchars($distrital['di_correo']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="id_distrito">Distrito</label>
                <select name="id_distrito" id="id_distrito" class="form-control" required>
                    <option value="">Seleccione un distrito</option>
                    <?php
                        // Asegúrate de que $distritos contiene los datos de los distritos
                        foreach ($distritos as $distrito) {
                            // Si estamos editando un distrital, seleccionamos el distrito previamente asignado
                            $selected = (isset($distrital) && $distrital['id_distrito'] == $distrito['id_distrito']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($distrito['id_distrito']) . "' $selected>" . htmlspecialchars($distrito['d_nombre']) . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="di_ci">Cédula de Identidad</label>
                <input type="text" class="form-control" id="di_ci" name="di_ci"
                    value="<?php echo isset($distrital) ? htmlspecialchars($distrital['di_ci']) : ''; ?>"
                    required maxlength="9" oninput="validarCI(this)" placeholder="Ingrese solo números positivos">
                <small id="mensajeCI" class="text-danger"></small>
            </div>

            <div class="form-group">
                <input type="checkbox" id="toggleComplemento">
                <label for="toggleComplemento">Añadir Complemento</label>
            </div>

            <div class="form-group" id="complementoGroup" style="display:none;">
                <label for="ci_complemento">Complemento</label>
                <input type="text" class="form-control" id="ci_complemento" name="ci_complemento"
                    value="<?php echo isset($distrital) ? htmlspecialchars($distrital['ci_complemento']) : ''; ?>"
                    maxlength="2" oninput="validarComplemento(this)" placeholder="Ej: A o CH">
                <small id="mensajeComplemento" class="text-danger"></small>
            </div>
            <!-- Botones dentro del modal -->
            <div class="mt-3">
                <button type="submit" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($distrital) ? 'display:none;' : ''; ?>">Crear Distrital</button>
                <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($distrital) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
              </div>
        </form>
      </div>
    </div>
  </div>
</div>



        <!-- Lista de distritales -->
        <h3 class="mt-5">Administrar Distritales</h3>
        <!-- Reemplaza tu formulario de búsqueda actual con este -->
            <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Distrital.php" method="get">
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
                                Todos los Distritales
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo $estadoFiltro === 'activo' ? 'active' : ''; ?>" 
                                    href="ADM_Distrital.php?estado=activo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                                        Activos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo $estadoFiltro === 'inactivo' ? 'active' : ''; ?>" 
                                    href="ADM_Distrital.php?estado=inactivo<?php echo $searchTerm ? '&search='.urlencode($searchTerm) : ''; ?>">
                                        Inactivos
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Botón Registrar -->
                        <button type="button" class="btn btn-success" id="btnCrearDistri" data-bs-toggle="modal" data-bs-target="#distritalModal">
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
                                    <th>Distrito</th>
                                    <th>Cédula de Identidad</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                    <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($distritales)): ?>
                                    <?php foreach ($distritales as $Ndistritales): ?>
                                        <tr>
                                            
                                            <td><?php echo htmlspecialchars($Ndistritales['di_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($Ndistritales['di_apellido']); ?></td>
                                            <td><?php echo htmlspecialchars($Ndistritales['di_correo']); ?></td>
                                            <td><?php echo htmlspecialchars($Ndistritales['d_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($Ndistritales['di_ci'] . ' ' . $Ndistritales['ci_complemento']); ?></td>
                                            <td><?php echo htmlspecialchars($Ndistritales['di_fecha']); ?></td>
                                            <td>
                                                <?php if ($Ndistritales['di_estado'] == 1): ?>
                                                    <span style="color: green; font-weight: bold;">Activo</span>
                                                <?php else: ?>
                                                    <span style="color: red; font-weight: bold;">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if (in_array('Administrador', $_SESSION['rol_asignado'])): ?>
                                                <td>
                                                    <div class="d-flex flex-column flex-md-row gap-1">
                                                        <?php if ($Ndistritales['di_estado'] == 1): ?>
                                                            <!-- Si es activo -->
                                                                <a href="ADM_Distrital.php?id_distrital=<?php echo $Ndistritales['id_distrital']; ?>" class="btn btn-warning">Editar</a>
                                                                <a href="ADM_Distrital.php?id_distrital=<?php echo $Ndistritales['id_distrital']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este distrital?');">Eliminar</a>
                                                        <?php else: ?>
                                                            <!-- Si es inactivo -->
                                                            <a href="ADM_Distrital.php?id_distrital=<?= $Ndistritales['id_distrital']; ?>&action=activar" class="btn btn-primary" onclick="return confirm('¿Deseas activar este distrital?');">Activar</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No hay distritales para mostrar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </main>

                <?php if (isset($distrital)): ?>
                <script>
                    var myModal = new bootstrap.Modal(document.getElementById('distritalModal'));
                    window.addEventListener('load', () => {
                        myModal.show();
                    });
                </script>
                <?php endif; ?>
<script>
document.getElementById("btnCrearDistri").addEventListener("click", function () {
    const form = document.getElementById("formDistri");

    // Limpia todos los inputs manualmente
    form.querySelectorAll("input").forEach(input => {
        input.value = "";
    });

    // Si existe el campo oculto id_distrital, lo eliminamos del DOM directamente
    const idInput = document.getElementById("id_distrital");
    if (idInput) idInput.remove();

    // Desactiva el botón "Guardar Cambios"
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    if (btnGuardar) btnGuardar.style.display = "none";

    // Activa el botón "Crear distrital"
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.style.display = "inline-block";
});

// ✅ Mostrar/ocultar campo ci_complemento
document.getElementById('toggleComplemento').addEventListener('change', function () {
    let complementoGroup = document.getElementById('complementoGroup');
    complementoGroup.style.display = this.checked ? 'block' : 'none';
});

// ✅ Función de validación del di_ci
function validarCI(input) {
    const mensaje = document.getElementById('mensajeCI');

    // Eliminar todo lo que no sea número
    input.value = input.value.replace(/[^0-9]/g, '');

    // Evitar ceros a la izquierda
    if (input.value.startsWith('0')) {
        input.value = input.value.replace(/^0+/, '');
    }

    // Mostrar advertencias según longitud
    if (input.value.length > 9) {
        input.value = input.value.slice(0, 9);
        mensaje.textContent = "Solo se permiten hasta 9 dígitos.";
        mensaje.className = "text-danger";
    } else if (input.value.length === 9) {
        mensaje.textContent = "⚠️ Se permite 9 dígitos, aunque lo normal es 8.";
        mensaje.className = "text-warning";
    } else if (input.value.length === 8) {
        mensaje.textContent = "✔️ Longitud ideal (8 dígitos).";
        mensaje.className = "text-success";
    } else {
        mensaje.textContent = "";
    }
}

// ✅ Función de validación del Complemento
function validarComplemento(input) {
    const mensaje = document.getElementById('mensajeComplemento');

    // Permitir solo letras A-Z
    input.value = input.value.replace(/[^a-zA-Z]/g, '').toUpperCase();

    if (input.value.length > 2) {
        input.value = input.value.slice(0, 2);
        mensaje.textContent = "Solo se permiten hasta 2 letras.";
        mensaje.className = "text-danger";
    } else {
        mensaje.textContent = "";
    }
}

// ✅ Vincular las validaciones cuando cargue el DOM
document.addEventListener('DOMContentLoaded', function() {
    const ciInput = document.getElementById('di_ci');
    const complementoInput = document.getElementById('ci_complemento');

    if (ciInput) {
        ciInput.addEventListener('input', function() {
            validarCI(this);
        });
    }

    if (complementoInput) {
        complementoInput.addEventListener('input', function() {
            validarComplemento(this);
        });
    }
});
</script>

</script>
</body>
</html>
