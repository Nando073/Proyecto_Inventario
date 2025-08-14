<?php
//session_start();
require_once '../NEGOCIO/N_Egreso.php';

$egresoService = new N_Egreso();
$detalleService = new N_Egreso();

// Obtener funcionarios y áreas para el select
$funcionarios = $egresoService->obtenerFuncionarios();
$areas = [];
foreach ($funcionarios as $f) {
    $areas[$f['id_area']] = $f['area_nombre'];
}

// Eliminar egreso por id
if (isset($_GET['id_egreso']) && $_GET['accion'] === 'delete') {
    $id_egreso = filter_input(INPUT_GET, 'id_egreso', FILTER_VALIDATE_INT);

    if ($id_egreso) {
        try {
            $egresoService->eliminarEgreso($id_egreso);
            header('Location: Egreso.php?msg=Egreso eliminado correctamente');
            exit();
        } catch (Exception $e) {
            echo "Error al eliminar el egreso: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "ID de egreso no válido.";
    }
}

// Procesar POST para registrar egreso y detalles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_SPECIAL_CHARS);

    $id_funcionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
    $codigo_solicitud = filter_input(INPUT_POST, 'codigo_solicitud', FILTER_SANITIZE_SPECIAL_CHARS);

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
    if (!preg_match('/^[a-zA-Z0-9]+$/', $codigo_solicitud)) {
        echo "<script>alert('El código de solicitud solo debe contener letras y números.'); window.history.back();</script>";
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
    $materiales = $detalleService->obtenerMateriales();
    $stockPorMaterial = [];
    foreach ($materiales as $mat) {
        $stockPorMaterial[$mat['id_material']] = $mat['stock'];
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
            $mensaje = $egresoService->registrarEgresoCompleto($id_funcionario, $codigo_solicitud, $totalCantidad, $detallesValidos);
            echo "<script>
                alert('¡Egreso registrado correctamente!');
                window.location.href='Egreso.php';
            </script>";
            exit();
        } catch (Exception $e) {
            echo "<script>alert('Error al registrar: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Error: Acción no válida.'); window.history.back();</script>";
    }
}

// Carga inicial de datos
$materiales = $detalleService->obtenerMateriales();

// Agrupar materiales por categoría para el JS
$materialesPorCategoria = [];
foreach ($materiales as $mat) {
    $cat = $mat['categoria_nombre'];
    if (!isset($materialesPorCategoria[$cat])) {
        $materialesPorCategoria[$cat] = [];
    }
    $materialesPorCategoria[$cat][] = $mat;
}

// Agrupar funcionarios por área para el JS
$funcionariosPorArea = [];
foreach ($funcionarios as $f) {
    $funcionariosPorArea[$f['id_area']][] = [
        'id_funcionario' => $f['id_funcionario'],
        'f_nombre' => $f['f_nombre']
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
    .btn-register { background-color: #8e44ad; color: white; }
    .card { border-radius: 10px; padding: 20px; }
  </style>
</head>
<body>
<?php include '../DEMO/index.php'; ?>
<main>
   <!-- Modal -->
    <div class="modal fade" id="egresoModal" tabindex="-1" aria-labelledby="egresoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="egresoModalLabel">Crear egreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <form id="formEgreso" action="Egreso.php" method="post" autocomplete="off">
                    <input type="hidden" name="accion" value="crear">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Área:</label>
                            <select name="area" id="selectArea" class="form-control" required>
                                <option value="">Seleccione un área</option>
                                <?php foreach ($areas as $id_area => $area_nombre): ?>
                                    <option value="<?php echo htmlspecialchars($id_area); ?>"><?php echo htmlspecialchars($area_nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Funcionario:</label>
                            <select name="id_funcionario" id="selectFuncionario" class="form-control" required disabled>
                                <option value="">Seleccione un funcionario</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="codigo_solicitud" class="form-label fw-bold">Código de Solicitud:</label>
                            <input type="text" name="codigo_solicitud" id="codigo_solicitud" class="form-control" required pattern="[a-zA-Z0-9]+">
                        </div>
                    </div>
                    <div id="materiales-container">
                        <div class="parte-row row align-items-end mb-2">
                            <div class="col-md-3">
                                <select name="categoria[]" class="form-control select-categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach (array_keys($materialesPorCategoria) as $categoria): ?>
                                        <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="id_material[]" class="form-control select-material" required disabled>
                                    <option value="">Seleccione un material</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input name="cantidad[]" placeholder="Cantidad" class="form-control input-cantidad" required pattern="[0-9]+">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-parte">X</button>
                            </div>
                        </div>
                    </div>
                    <!-- Botón Añadir material -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-add w-100" id="btnAgregar">AÑADIR MATERIAL</button>
                        </div>
                        <div class="col-md-6 fw-bold text-end">TOTAL CANTIDAD:</div>
                        <div class="col-md-3">
                            <input type="text" id="totalGeneral" class="form-control" readonly>
                        </div>
                    </div>
                    <!-- Botón Registrar -->
                    <div class="row mt-4">
                        <div class="col-md-3 offset-md-9">
                            <button type="submit" class="btn btn-register w-100">GENERAR EGRESO</button>
                        </div>
                    </div>
                  </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">EGRESAR MATERIALES</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="Egreso.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre, ID o fecha" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearEgreso" data-bs-toggle="modal" data-bs-target="#egresoModal">
            Registrar Egreso de material
        </button>
    </form>

    <!-- tabla -->
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
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
                        <td><?php echo htmlspecialchars($egreso['id_egreso']); ?></td>
                        <td><?php echo htmlspecialchars($egreso['funcionario_nombre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($egreso['e_solicitud']); ?></td>
                        <td><?php echo htmlspecialchars($egreso['e_total_cantidad']); ?></td>
                        <td><?php echo htmlspecialchars($egreso['e_fecha']); ?></td>
                        <td>
                            <a href="#" class="btn btn-info btn-ver-egreso" data-id="<?php echo $egreso['id_egreso']; ?>">Ver</a>
                            <a href="Egreso.php?id_egreso=<?php echo $egreso['id_egreso']; ?>&accion=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro de egreso?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron resultados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Template oculto para duplicar -->
    <div id="parte-template" class="parte-row row align-items-end mb-2 d-none">
        <div class="col-md-3">
            <select name="categoria[]" class="form-control select-categoria" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach (array_keys($materialesPorCategoria) as $categoria): ?>
                    <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="id_material[]" class="form-control select-material" required disabled>
                <option value="">Seleccione un material</option>
            </select>
        </div>
        <div class="col-md-3">
            <input name="cantidad[]" placeholder="Cantidad" class="form-control input-cantidad" required pattern="[0-9]+">
        </div>
        <div class="col-md-1 text-center">
            <button type="button" class="btn btn-danger btn-sm remove-parte">X</button>
        </div>
    </div>
    <a href="historial_egreso.php"><button type="button" class="btn btn-info">Historial de Registro</button></a>
</main>

<!-- Modal Detalle Egreso -->
<div class="modal fade" id="detalleEgresoModal" tabindex="-1" aria-labelledby="detalleEgresoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleEgresoLabel">Detalle de Egreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="detalleEgresoBody">
        <!-- Aquí se cargará el detalle por AJAX -->
        <div class="text-center">
          <span class="spinner-border"></span> Cargando...
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    // ...tu código existente...

    // Botón "Ver" para mostrar el detalle en el modal
    document.querySelectorAll('.btn-ver-egreso').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const idEgreso = this.getAttribute('data-id');
            const modalBody = document.getElementById('detalleEgresoBody');
            modalBody.innerHTML = "<div class='text-center'><span class='spinner-border'></span> Cargando...</div>";
            const modal = new bootstrap.Modal(document.getElementById('detalleEgresoModal'));
            modal.show();

            fetch('detalle_egreso_ajax.php?id=' + idEgreso)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(() => {
                    modalBody.innerHTML = "<div class='alert alert-danger'>Error al cargar el detalle.</div>";
                });
        });
    });
});
const materialesPorCategoria = <?php echo json_encode($materialesPorCategoria); ?>;
const funcionariosPorArea = <?php echo json_encode($funcionariosPorArea); ?>;

document.addEventListener('DOMContentLoaded', () => {
    const btnAgregar = document.getElementById('btnAgregar');
    const partesContainer = document.getElementById('materiales-container');
    const totalInput = document.getElementById('totalGeneral');
    const selectArea = document.getElementById('selectArea');
    const selectFuncionario = document.getElementById('selectFuncionario');

    // Área y funcionario
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

    // Validación solo números en cantidad
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-cantidad')) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        }
        if (e.target.id === 'codigo_solicitud') {
            e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
        }
    });

    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('input[name="cantidad[]"]').forEach(input => {
            const val = parseInt(input.value);
            if (!isNaN(val)) total += val;
        });
        totalInput.value = total;
    }

    function actualizarMaterialesPorCategoria(row) {
        const selectCategoria = row.querySelector('.select-categoria');
        const selectMaterial = row.querySelector('.select-material');
        selectCategoria.addEventListener('change', function() {
            const categoria = this.value;
            selectMaterial.innerHTML = '<option value="">Seleccione un material</option>';
            selectMaterial.disabled = true;
            if (categoria && materialesPorCategoria[categoria]) {
                materialesPorCategoria[categoria].forEach(mat => {
                    const option = document.createElement('option');
                    option.value = mat.id_material;
                    option.textContent = `${mat.m_nombre} (Stock: ${mat.stock})`;
                    selectMaterial.appendChild(option);
                });
                selectMaterial.disabled = false;
            }
        });
    }

    function agregarFila() {
        const template = document.getElementById('parte-template').cloneNode(true);
        template.classList.remove('d-none');
        template.removeAttribute('id');
        template.querySelectorAll('input').forEach(input => input.value = '');
        template.querySelector('.select-categoria').value = '';
        template.querySelector('.select-material').innerHTML = '<option value="">Seleccione un material</option>';
        template.querySelector('.select-material').disabled = true;
        template.querySelector('.remove-parte').addEventListener('click', () => {
            template.remove();
            calcularTotal();
        });
        actualizarMaterialesPorCategoria(template);
        template.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotal);
        partesContainer.appendChild(template);
    }

    document.querySelectorAll('.parte-row').forEach(row => {
        actualizarMaterialesPorCategoria(row);
        row.querySelector('.remove-parte').addEventListener('click', () => {
            row.remove();
            calcularTotal();
        });
        row.querySelector('input[name="cantidad[]"]').addEventListener('input', calcularTotal);
    });

    btnAgregar.addEventListener('click', agregarFila);

    // Abrir modal y limpiar formulario
    document.getElementById("btnCrearEgreso").addEventListener("click", function () {
        const form = document.getElementById("formEgreso");
        form.reset();
        // Eliminar filas adicionales
        document.querySelectorAll('#materiales-container .parte-row:not(:first-child)').forEach(row => row.remove());
        // Limpiar selects y total
        document.querySelectorAll('.select-material').forEach(sel => {
            sel.innerHTML = '<option value="">Seleccione un material</option>';
            sel.disabled = true;
        });
        totalInput.value = '';
        // Limpiar funcionarios
        selectFuncionario.innerHTML = '<option value="">Seleccione un funcionario</option>';
        selectFuncionario.disabled = true;
    });
});
</script>
</body>
</html>