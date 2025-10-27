<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Egreso.php';
require_once '../NEGOCIO/N_Funcionario.php';

$egresoService = new N_Egreso();
//$detalleService = new N_Egreso();

$funcionarioService = new N_Funcionario();

//session_start(); //verificar si un usuario se a inisiado para obtenre el id
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "<script>alert('No se pudo identificar al usuario que realiza el egreso.'); window.history.back();</script>";
    exit();
}


// Obtener funcionarios y áreas para el select
$funcionarios = $funcionarioService->obtenerFuncionarioD();
$areas = [];
foreach ($funcionarios as $f) {
    $areas[$f['area']] = $f['a_nombre'];
}

// Eliminar egreso por id
if (isset($_GET['id_egreso']) && $_GET['accion'] === 'delete') {
    $id_egreso = filter_input(INPUT_GET, 'id_egreso', FILTER_VALIDATE_INT);

    if ($id_egreso) {
        try {
            // Llamar al servicio que ejecuta el procedimiento almacenado
            $resultado = $egresoService->eliminarEgreso($id_egreso);

            // Verificar el resultado
            if (isset($resultado['success']) && $resultado['success'] == 1) {
                $_SESSION['mensaje'] = "Egreso eliminado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = $resultado['mensaje'] ?? "No se puede eliminar el egreso. Puede que ya esté eliminado o tenga relaciones.";
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: Egreso.php');
            exit();

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar egreso: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Egreso.php');
            exit();
        }
    } else {
        $_SESSION['mensaje'] = "ID de egreso no válido.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Egreso.php');
        exit();
    }
}


// Procesar POST para registrar egreso y detalles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);

    $area = filter_input(INPUT_POST, 'area', FILTER_VALIDATE_INT);
    $categorias = $_POST['categoria'] ?? [];
    $id_material = $_POST['id_material'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    // Validaciones adicionales
    if (!$area) {
        echo "<script>alert('Debe seleccionar un área.'); window.history.back();</script>";
        exit();
    }
    if (!$id_funcionario) {
        echo "<script>alert('Debe seleccionar un funcionario.'); window.history.back();</script>";
        exit();
    }

    // Filtrar filas incompletas
    $categorias = array_filter($categorias, fn($value) => !empty($value));
    $id_material = array_filter($id_material, fn($value) => !empty($value));
    $cantidades = array_filter($cantidades, fn($value) => !empty($value));

    // Validar que los arrays tengan la misma longitud
    if (count($id_material) !== count($cantidades) || count($id_material) !== count($categorias)) {
        echo "<script>alert('Error: Los detalles no están sincronizados.'); window.history.back();</script>";
        exit();
    }

    $totalCantidad = 0;
    $detallesValidos = [];

    // Obtener stocks actuales
    $materiales = $egresoService->ObtenerStockTotalPorMaterial();
    $stockPorMaterial = [];
    foreach ($materiales as $mat) {
        $stockPorMaterial[$mat['id_material']] = $mat['stock_total'];
    }

    // Validar stock antes de registrar el egreso
    for ($i = 0; $i < count($id_material); $i++) {
        $idMat = isset($id_material[$i]) ? trim($id_material[$i]) : null;
        $cantidad = isset($cantidades[$i]) ? filter_var($cantidades[$i], FILTER_VALIDATE_INT) : false;
        $categoria = isset($categorias[$i]) ? trim($categorias[$i]) : null;

        if (empty($idMat) || $cantidad === false || empty($categoria)) {
            echo "<script>alert('Verifique que todos los detalles estén completos y válidos en la fila " . ($i + 1) . ".'); window.history.back();</script>";
            exit();
        }
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            echo "<script>alert('La cantidad debe ser un número mayor a cero en la fila " . ($i + 1) . ".'); window.history.back();</script>";
            exit();
        }
        if (!isset($stockPorMaterial[$idMat])) {
            echo "<script>alert('El material seleccionado no existe (ID: $idMat).'); window.history.back();</script>";
            exit();
        }
        if ($cantidad > $stockPorMaterial[$idMat]) {
            echo "<script>alert('No hay suficiente stock para el material seleccionado (ID: $idMat). Stock disponible: {$stockPorMaterial[$idMat]}, solicitado: $cantidad'); window.history.back();</script>";
            exit();
        }

        $totalCantidad += $cantidad;
        $detallesValidos[] = [
            'id_material_e' => $idMat,
            'e_stock' => $cantidad
        ];
    }

    // Procesar acción
    if ($accion === 'crear') {
        try {
            $resultado = $egresoService->registrarEgresoCompleto($id_funcionario, $totalCantidad, $id_usuario, $detallesValidos);
            if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Egreso registrado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se pudo registrar el Egreso.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
        } catch (Exception $e) {
            echo "Error al registrar Egreso: " . htmlspecialchars($e->getMessage());
        }
        header('Location: Egreso.php');
        exit();
    } else {
        echo "<script>alert('Error: Acción no válida.'); window.history.back();</script>";
    }
}

// Carga inicial de datos
$materiales = $egresoService->obtenerStockTotalPorMaterial();

// Agrupar materiales por categoría para el JS
$materialesPorCategoria = [];
foreach ($materiales as $mat) {
    $cat = $mat['c_nombre'];
    if (!isset($materialesPorCategoria[$cat])) {
        $materialesPorCategoria[$cat] = [];
    }
    $materialesPorCategoria[$cat][] = $mat;
}

// Agrupar funcionarios por área para el JS
$funcionariosPorArea = [];
foreach ($funcionarios as $f) {
    $funcionariosPorArea[$f['area']][] = [
        'id_funcionario' => $f['id_funcionario'],
        'f_nombre' => $f['f_nombre'] . ' ' . $f['f_apellido']
    ];
}

// Buscar egresos
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) : '';
if ($searchTerm) {
    $egresos = $egresoService->buscarPorSimilitud($searchTerm);
} else {
    $egresos = $egresoService->ObtenerEgresosRegistrado();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Egreso de Materiales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../DEMO/styles.css?v=<?php echo(rand()); ?>">
  <script src="../DEMO/contrarer.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .custom-table-header { background-color: #0d1b2a; color: white; }
    .btn-add { background-color: #4caf50; color: white; }
    .btn-delete { background-color: #c0392b; color: white; }
    .btn-register { background-color: #2847f1ff; color: white; }
    .card { border-radius: 10px; padding: 20px; }
    .form-label {
      font-weight: bold;
      margin-bottom: 5px;
    }
    /* Mejoras para responsividad */
    .parte-row .col-md-1 {
      padding-bottom: 10px;
    }
    @media (max-width: 768px) {
      .btn-responsive {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
      }
      .modal-dialog {
        margin: 10px;
      }
    }
    .btn-register{
    /* Desactivar cualquier cambio al pasar el mouse */
    transition: none !important;
    background-color: #0d6efd; /* Mantener color de fondo */
    color: #fff; /* Mantener color de texto */
}

.btn-register:hover,
.btn-register:focus,
.btn-register:active {
    background-color: #2963b9ff !important; /* Mantener el mismo color */
    color: #fff !important; /* Mantener el mismo color */
    box-shadow: none !important; /* Eliminar sombra de Bootstrap */
}
.btn-add{
    /* Desactivar cualquier cambio al pasar el mouse */
    transition: none !important;
    background-color: #4caf50; /* Mantener color de fondo */
    color: #fff; /* Mantener color de texto */
}

.btn-add:hover,
.btn-add:focus,
.btn-add:active {
    background-color: #428a44ff !important; /* Mantener el mismo color */
    color: #fff !important; /* Mantener el mismo color */
    box-shadow: none !important; /* Eliminar sombra de Bootstrap */
}

  </style>
</head>
<body>
<?php include '../DEMO/index.php'; ?>
<main>
   <!-- Modal -->
    <div class="modal fade" id="egresoModal" tabindex="-1" aria-labelledby="egresoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="egresoModalLabel">Crear egreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <form id="formEgreso" action="Egreso.php" method="post" autocomplete="off">
                    <input type="hidden" name="accion" value="crear">
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <label class="form-label fw-bold">Área:</label>
                            <select name="area" id="selectArea" class="form-control" required>
                                <option value="">Seleccione un área</option>
                                <?php foreach ($areas as $area => $area_nombre): ?>
                                    <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area_nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <label class="form-label fw-bold">Funcionario:</label>
                            <select name="id_funcionario" id="selectFuncionario" class="form-control" required disabled>
                                <option value="">Seleccione un funcionario</option>
                            </select>
                        </div>
                        <!-- <div class="col-12 col-md-4">
                            <label for="codigo_solicitud" class="form-label fw-bold">Código de Solicitud:</label>
                            <input type="number" name="codigo_solicitud" id="codigo_solicitud" class="form-control" required pattern="[a-zA-Z0-9]+">
                        </div> -->
                    </div>
                    <div id="materiales-container" class="border rounded p-3 bg-light">
                        <div class="parte-row row align-items-end mb-2 border bg-primary bg-opacity-10 p-4 rounded">
                            <div class="col-12 col-md-3 mb-2 mb-md-0">
                                <label class="form-label">Categoría</label>
                                <select name="categoria[]" class="form-control select-categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach (array_keys($materialesPorCategoria) as $categoria): ?>
                                        <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="form-label">Material</label>
                               <select name="id_material[]" class="form-control select-material" required>
                                    <option value="">Seleccione un material</option>
                                    <?php foreach ($materiales as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['id_material']); ?>"
                                                data-unidad="<?php echo htmlspecialchars($material['u_medida']); ?>"
                                                data-stock="<?php echo htmlspecialchars($material['stock_total']); ?>">
                                            <?php echo htmlspecialchars($material['m_nombre']); ?> (Stock: <?php echo htmlspecialchars($material['stock_total']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="form-label">Cantidad</label>
                                <div class="input-group">
                                    <input name="cantidad[]" placeholder="Cantidad" class="form-control input-cantidad" required pattern="[0-9]+">
                                    <span class="input-group-text unidad-medida">--</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-1 text-center mb-2 mb-md-0">
                                <button type="button" class="btn btn-danger btn-sm remove-parte w-100">X</button>
                            </div>
                        </div>
                    </div>
                    <!-- Botón Añadir material -->
                    <div class="row mt-3">
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                            <button type="button" class="btn btn-add w-100 btn-responsive" id="btnAgregar">AÑADIR MATERIAL</button>
                        </div>
                        <div class="col-12 col-md-6 fw-bold text-center text-md-end mb-2 mb-md-0 d-flex align-items-center justify-content-center justify-content-md-end">
                            TOTAL CANTIDAD:
                        </div>
                        <div class="col-12 col-md-3">
                            <input type="text" id="totalGeneral" class="form-control" readonly>
                        </div>
                    </div>
                    <!-- Botón Registrar -->
                    <div class="row mt-4">
                        <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8">
                            <button type="submit" class="btn btn-register w-100 btn-responsive">GENERAR EGRESO</button>
                        </div>
                    </div>
                  </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">EGRESAR MATERIALES</h3>
    
    <!-- Formulario de búsqueda responsivo -->
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="Egreso.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre, ID o fecha" 
                   value="<?php echo htmlspecialchars($searchTerm); ?>" 
                   class="form-control me-2">
            <button type="submit" class="btn btn-info flex-shrink-0 btn-responsive">Buscar</button>
        </div>

        <button type="button" class="btn btn-success btn-responsive" id="btnCrearEgreso" data-bs-toggle="modal" data-bs-target="#egresoModal">
            Registrar Egreso
        </button>
    </form>

    <!-- Mensaje -->
<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> mt-3">
        <?= htmlspecialchars($_SESSION['mensaje']); ?>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
<?php endif; ?>

    <!-- Tabla Responsiva -->
    <div class="table-responsive">
        <table class="table table-bordered mt-3">
            <thead class="custom-table-header">
                <tr>
                    <th>Funcionario</th>
                    <th>Código Solicitud</th>
                    <th>Total Cantidad</th>
                    <th>Fecha de egreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($egresos)): ?>
                    <?php foreach ($egresos as $egreso): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($egreso['funcionario_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($egreso['e_solicitud']); ?></td>
                            <td><?php echo htmlspecialchars($egreso['e_total_cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($egreso['e_fecha']); ?></td>
                            <td>
                                <div class="d-flex flex-column flex-md-row gap-1">
                                    <a href="#" class="btn btn-info btn-sm btn-ver-egreso btn-responsive" data-id="<?php echo $egreso['id_egreso']; ?>">Ver</a>
                                    <a href="Egreso.php?id_egreso=<?php echo $egreso['id_egreso']; ?>&accion=delete" class="btn btn-danger btn-sm btn-responsive" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro de egreso?');">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No se encontraron resultados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Template oculto para duplicar -->
            <div id="parte-template" class="parte-row row align-items-end mb-2 d-none border rounded p-3 bg-primary bg-opacity-10">
                            <div class="col-12 col-md-3 mb-2 mb-md-0">
                                <label class="form-label">Categoría</label>
                                <select name="categoria[]" class="form-control select-categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach (array_keys($materialesPorCategoria) as $categoria): ?>
                                        <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="form-label">Material</label>
                                <select name="id_material[]" class="form-control select-material" required>
                                    <option value="">Seleccione un material</option>
                                    <?php foreach ($materiales as $material): ?>
                                        <option value="<?php echo htmlspecialchars($material['id_material']); ?>"
                                                data-unidad="<?php echo htmlspecialchars($material['u_medida']); ?>"
                                                data-stock="<?php echo htmlspecialchars($material['stock_total']); ?>">
                                            <?php echo htmlspecialchars($material['m_nombre']); ?> (Stock: <?php echo htmlspecialchars($material['stock_total']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                <label class="form-label">Cantidad</label>
                                <div class="input-group">
                                    <input name="cantidad[]" placeholder="Cantidad" class="form-control input-cantidad" required pattern="[0-9]+">
                                    <span class="input-group-text unidad-medida">--</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-1 text-center mb-2 mb-md-0">
                                <button type="button" class="btn btn-danger btn-sm remove-parte w-100">X</button>
                            </div>
                        </div>
            </div>
</main>

<!-- Modal Detalle Egreso -->
<div class="modal fade" id="detalleEgresoModal" tabindex="-1" aria-labelledby="detalleEgresoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleEgresoLabel">Detalle de Egreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="detalleEgresoBody">
        <div class="text-center">
          <span class="spinner-border"></span> Cargando...
        </div>
      </div>
    </div>
  </div>
</div>
<script>
    window.materialesPorCategoria = <?php echo json_encode($materialesPorCategoria); ?>;
    window.funcionariosPorArea = <?php echo json_encode($funcionariosPorArea); ?>;
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Variables globales
    const materialesPorCategoria = window.materialesPorCategoria || {};
    const funcionariosPorArea = window.funcionariosPorArea || {};
    const form = document.getElementById('formEgreso');
    const btnAgregar = document.getElementById('btnAgregar');
    const partesContainer = document.getElementById('materiales-container');
    const totalInput = document.getElementById('totalGeneral');
    const selectArea = document.getElementById('selectArea');
    const selectFuncionario = document.getElementById('selectFuncionario');

    // --- 🔹 Validar cantidad contra stock ---
    function validarCantidades() {
        let valido = true;
        const filas = partesContainer.querySelectorAll('.parte-row');

        filas.forEach(row => {
            const materialSelect = row.querySelector('.select-material');
            const cantidadInput = row.querySelector('.input-cantidad');
            if (!materialSelect || !cantidadInput) return;

            // Si no hay material seleccionado, no valida
            if (!materialSelect.value) {
                cantidadInput.classList.remove('is-invalid');
                return;
            }

            const stock = Number(materialSelect.selectedOptions[0]?.dataset.stock || 0);
            const cantidad = Number(cantidadInput.value.trim()) || 0;

            if (cantidad > stock) {
                cantidadInput.classList.add('is-invalid');
                valido = false;
            } else {
                cantidadInput.classList.remove('is-invalid');
            }
        });

        return valido;
    }

    // --- 🔹 Actualizar total general ---
    function calcularTotal() {
        let total = 0;
        partesContainer.querySelectorAll('input[name="cantidad[]"]').forEach(input => {
            const val = parseInt(input.value);
            if (!isNaN(val)) total += val;
        });
        totalInput.value = total;
    }

    // --- 🔹 Validación solo números ---
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-cantidad')) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value.length > 1 && e.target.value.startsWith('0')) {
                e.target.value = e.target.value.replace(/^0+/, '');
            } else if (e.target.value === '0') {
                e.target.value = '';
            }
            validarCantidades();
            calcularTotal();
        }
        if (e.target.id === 'codigo_solicitud') {
            e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
        }
    });

    // --- 🔹 Área → funcionario ---
    selectArea.addEventListener('change', function() {
        const areaId = this.value;
        selectFuncionario.innerHTML = '<option value="">Seleccione un funcionario</option>';
        selectFuncionario.disabled = true;
        if (areaId && funcionariosPorArea[areaId]) {
            funcionariosPorArea[areaId].forEach(f => {
                const option = document.createElement('option');
                option.value = f.id_funcionario;
                option.textContent = f.f_nombre;
                selectFuncionario.appendChild(option);
            });
            selectFuncionario.disabled = false;
        }
    });

    // --- 🔹 Obtener materiales ya seleccionados ---
    function materialesSeleccionados() {
        return Array.from(partesContainer.querySelectorAll('.select-material'))
            .map(sel => sel.value)
            .filter(v => v !== "");
    }

    // --- 🔹 Actualizar unidad de medida ---
    function actualizarUnidadMedida(selectMaterial) {
        const unidad = selectMaterial.selectedOptions[0]?.getAttribute('data-unidad') || '--';
        const row = selectMaterial.closest('.parte-row');
        const unidadSpan = row.querySelector('.unidad-medida');
        if (unidadSpan) unidadSpan.textContent = unidad;
    }

    // --- 🔹 Actualizar materiales por categoría ---
    function actualizarMaterialesPorCategoria(row) {
        const selectCategoria = row.querySelector('.select-categoria');
        const selectMaterial = row.querySelector('.select-material');

        selectCategoria.addEventListener('change', function() {
            const categoria = this.value;
            selectMaterial.innerHTML = '<option value="">Seleccione un material</option>';
            selectMaterial.disabled = true;

            const unidadSpan = row.querySelector('.unidad-medida');
            if (unidadSpan) unidadSpan.textContent = '--';

            if (categoria && materialesPorCategoria[categoria]) {
                const yaSeleccionados = materialesSeleccionados().filter(v => v !== selectMaterial.value);
                materialesPorCategoria[categoria].forEach(mat => {
                    if (!yaSeleccionados.includes(String(mat.id_material))) {
                        const option = document.createElement('option');
                        option.value = mat.id_material;
                        option.textContent = `${mat.m_nombre} (Stock: ${mat.stock_total})`;
                        option.setAttribute('data-unidad', mat.u_medida || '--');
                        option.setAttribute('data-stock', mat.stock_total || 0);
                        selectMaterial.appendChild(option);
                    }
                });
                selectMaterial.disabled = false;
            }
        });

        selectMaterial.addEventListener('change', function() {
            actualizarUnidadMedida(this);
            validarCantidades();
            actualizarTodosLosMateriales();
        });

        if (selectMaterial.value) {
            actualizarUnidadMedida(selectMaterial);
        }
    }

    // --- 🔹 Actualizar todos los selects de materiales (evitar duplicados) ---
    function actualizarTodosLosMateriales() {
        partesContainer.querySelectorAll('.parte-row').forEach(row => {
            const selectCategoria = row.querySelector('.select-categoria');
            const selectMaterial = row.querySelector('.select-material');
            if (selectCategoria.value && materialesPorCategoria[selectCategoria.value]) {
                const yaSeleccionados = materialesSeleccionados().filter(v => v !== selectMaterial.value);
                const actualSeleccionado = selectMaterial.value;
                selectMaterial.innerHTML = '<option value="">Seleccione un material</option>';
                materialesPorCategoria[selectCategoria.value].forEach(mat => {
                    if (!yaSeleccionados.includes(String(mat.id_material)) || String(mat.id_material) === actualSeleccionado) {
                        const option = document.createElement('option');
                        option.value = mat.id_material;
                        option.textContent = `${mat.m_nombre} (Stock: ${mat.stock_total})`;
                        option.setAttribute('data-unidad', mat.u_medida || '--');
                        option.setAttribute('data-stock', mat.stock_total || 0);
                        selectMaterial.appendChild(option);
                    }
                });
                selectMaterial.value = actualSeleccionado;
            }
        });
    }

    // --- 🔹 Agregar fila ---
    function agregarFila() {
        const template = document.getElementById('parte-template').cloneNode(true);
        template.classList.remove('d-none');
        template.removeAttribute('id');

        template.querySelector('.select-categoria').value = '';
        const selectMat = template.querySelector('.select-material');
        selectMat.innerHTML = '<option value="">Seleccione un material</option>';
        selectMat.disabled = true;
        template.querySelector('.input-cantidad').value = '';
        template.querySelector('.unidad-medida').textContent = '--';

        template.querySelector('.remove-parte').addEventListener('click', () => {
            template.remove();
            calcularTotal();
            actualizarTodosLosMateriales();
        });

        actualizarMaterialesPorCategoria(template);
        template.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotal);
        partesContainer.appendChild(template);
    }

    // --- 🔹 Inicializar filas existentes ---
    partesContainer.querySelectorAll('.parte-row').forEach(row => {
        actualizarMaterialesPorCategoria(row);
        row.querySelector('.remove-parte').addEventListener('click', () => {
            row.remove();
            calcularTotal();
            actualizarTodosLosMateriales();
        });
        row.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotal);
        const selectMaterial = row.querySelector('.select-material');
        if (selectMaterial && selectMaterial.value) {
            actualizarUnidadMedida(selectMaterial);
        }
    });

    btnAgregar.addEventListener('click', agregarFila);

    // --- 🔹 Validar antes de enviar ---
    form.addEventListener('submit', function(e) {
        if (!validarCantidades()) {
            alert('⚠️ Hay cantidades mayores al stock disponible. Corrige los campos resaltados.');
            e.preventDefault();
        }
    });

    // --- 🔹 Modal detalle egreso ---
    document.querySelectorAll('.btn-ver-egreso').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const idEgreso = this.getAttribute('data-id');
            const modalBody = document.getElementById('detalleEgresoBody');
            modalBody.innerHTML = "<div class='text-center'><span class='spinner-border'></span> Cargando...</div>";
            const modal = new bootstrap.Modal(document.getElementById('detalleEgresoModal'));
            modal.show();

            fetch('detalle_egreso.php?id=' + idEgreso)
                .then(response => response.text())
                .then(html => modalBody.innerHTML = html)
                .catch(() => modalBody.innerHTML = "<div class='alert alert-danger'>Error al cargar el detalle.</div>");
        });
    });
});
</script>
<script>
function imprimirModal(id) {
    var contenido = document.getElementById(id).innerHTML;
    
    var ventana = window.open('', '', 'width=800,height=600');
    ventana.document.write('<html><head><title>Imprimir Ingreso</title>');
    ventana.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">');
    ventana.document.write('</head><body>');
    ventana.document.write(contenido);
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.focus();
    ventana.print();
    ventana.close();
}
</script>
</body>
</html>