<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Ingreso.php';
require_once '../NEGOCIO/N_Material.php';
require_once '../NEGOCIO/N_Proveedor.php';

$ingresoService = new N_Ingreso();
$materialService = new N_Material();
$provService = new N_Proveedor();

//session_start(); //verificar si un usuario se a inisiado para obtenre el id
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "<script>alert('No se pudo identificar al usuario que realiza el ingreso.'); window.history.back();</script>";
    exit();
}

// Cargar materiales y proveedores
$materiales = $materialService->obtenerMateriales();
$proveedores = $provService->obtenerProveedoresActivos();

// Agrupar materiales por categoría (para el frontend)
$materialesPorCategoria = [];
foreach ($materiales as $mat) {
    $cat = $mat['c_nombre'] ?? 'Sin categoría';
    if (!isset($materialesPorCategoria[$cat])) {
        $materialesPorCategoria[$cat] = [];
    }
    $materialesPorCategoria[$cat][] = $mat;
}

// =================== ELIMINAR INGRESO ===================
if (isset($_GET['id_ingreso']) && $_GET['accion'] === 'delete') {
    $id_ingreso = filter_input(INPUT_GET, 'id_ingreso', FILTER_VALIDATE_INT);

    if ($id_ingreso) {
        try {
            // Llamar al servicio que ejecuta el procedimiento almacenado
            $resultado = $ingresoService->eliminarIngreso($id_ingreso);

            // Verificar el resultado
            if (isset($resultado['success']) && $resultado['success'] == 1) {
                $_SESSION['mensaje'] = "Ingreso eliminado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = $resultado['message'] ?? "No se puede eliminar el ingreso. por que ya tiene egresos relacionados.";
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: Ingreso.php');
            exit();

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar ingreso: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Ingreso.php');
            exit();
        }
    } else {
        $_SESSION['mensaje'] = "ID de ingreso no válido.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Ingreso.php');
        exit();
    }
}


// =================== REGISTRAR INGRESO ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_SPECIAL_CHARS);

    $categorias = $_POST['categoria'] ?? [];
    $id_material = $_POST['id_material'] ?? [];
    $precios = $_POST['precio'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $subtotales = $_POST['sub_total'] ?? [];

    // Validar campos obligatorios
    if (!$id_proveedor) {
        echo "Error: Debe seleccionar un proveedor.";
        exit();
    }

    // Filtrar filas incompletas
    $totalFilas = count($id_material);
    $detallesValidos = [];
    $materialesSeleccionados = [];
    $totalCalculado = 0;

    for ($i = 0; $i < $totalFilas; $i++) {
        $categoria = trim($categorias[$i] ?? '');
        $idMat = trim($id_material[$i] ?? '');
        $precio = filter_var($precios[$i] ?? '', FILTER_VALIDATE_FLOAT);
        $cantidad = filter_var($cantidades[$i] ?? '', FILTER_VALIDATE_INT);
        $subtotal = filter_var($subtotales[$i] ?? '', FILTER_VALIDATE_FLOAT);

        // Validar que no se seleccione material sin categoría
        if (empty($categoria)) {
            echo "Error: Debe seleccionar una categoría antes de elegir un material (fila " . ($i + 1) . ").";
            exit();
        }

        // Validar material único y completo
        if (empty($idMat)) {
            echo "Error: Falta seleccionar material en la fila " . ($i + 1);
            exit();
        }
        if (in_array($idMat, $materialesSeleccionados)) {
            echo "Error: El material en la fila " . ($i + 1) . " ya fue seleccionado.";
            exit();
        }
        if ($precio === false || $cantidad === false || $subtotal === false) {
            echo "Error: Verifica los datos numéricos en la fila " . ($i + 1);
            exit();
        }

        // Guardar fila válida
        $detallesValidos[] = [
            'id_material' => $idMat,
            'precio' => $precio,
            'cantidad' => $cantidad,
            'sub_total' => $subtotal
        ];
        $totalCalculado += $subtotal;
        $materialesSeleccionados[] = $idMat;
    }

    // Si la acción es crear ingreso
    if ($accion === 'crear') {
        try {
            $resultado = $ingresoService->registrarIngresoCompleto($id_proveedor, $totalCalculado, $id_usuario, $detallesValidos);
                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Ingreso registrado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se pudo registrar el Ingreso.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
        } catch (Exception $e) {
            echo "Error al registrar ingreso: " . htmlspecialchars($e->getMessage());
        }
        header('Location: Ingreso.php');
        exit();
    } else {
        echo "Acción no válida.";
    }
}

// =================== BUSCAR INGRESOS ===================
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) : '';
if ($searchTerm) {
    $ingresos = $ingresoService->buscarPorSimilitud($searchTerm);
} else {
    $ingresos = $ingresoService->ObtenerIngresosRegistrado();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ingreso de Materiales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../DEMO/styles.css?v=<?php echo(rand()); ?>"> 
  <script src="../DEMO/contrarer.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
    }
    .custom-table-header {
      background-color: #0d1b2a;
      color: white;
    }
    .btn-add {
      background-color: #4caf50;
      color: white;
    }
    
    .btn-add:hover,
    .btn-add:focus,
    .btn-add:active {
        background-color: #428a44ff !important; /* Mantener el mismo color */
        color: #fff !important; /* Mantener el mismo color */
        box-shadow: none !important; /* Eliminar sombra de Bootstrap */
    }
    .btn-delete {
      background-color: #c0392b;
      color: white;
    }
    .btn-register {
      background-color: #0d6efd;
      color: white;
    }
    .btn-register:hover,
    .btn-register:focus,
    .btn-register:active {
    background-color: #2963b9ff !important; /* Mantener el mismo color */
    color: #fff !important; /* Mantener el mismo color */
    box-shadow: none !important; /* Eliminar sombra de Bootstrap */
}
    .card {
      border-radius: 10px;
      padding: 20px;
    }
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
  </style>
</head>
<body>
<?php include '../DEMO/index.php'; ?>
<main>
   <!-- Modal -->
    <div class="modal fade" id="ingresoModal" tabindex="-1" aria-labelledby="ingresoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ingresoModalLabel">Crear registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <form id="formIngreso" action="Ingreso.php" method="post">
                    <div class="row mb-3">
                      <div class="col-12 col-md-3 mb-2 mb-md-0 d-flex align-items-center">
                        <label for="id_proveedor" class="form-label fw-bold mb-0">Proveedor:</label>
                      </div>
                      <div class="col-12 col-md-6 mb-2 mb-md-0">
                        <select name="id_proveedor" id="id_proveedor" class="form-control" required>
                          <option value="">Seleccione un proveedor</option>
                          <?php
                              foreach ($proveedores as $proveedor) {
                                  echo "<option value='" . htmlspecialchars($proveedor['id_proveedor']) . "'>" . htmlspecialchars($proveedor['p_nombre']) . "</option>";
                              }
                          ?>
                        </select>  
                      </div>
                      <div class="col-12 col-md-3">
                        <button type="button" class="btn btn-add w-100 btn-responsive" id="btnAgregar">AÑADIR MATERIAL</button>
                      </div>
                    </div>
                  <div class="gray-bg p-3 rounded border bg-light">
                    <!-- Contenedor de materiales -->
                    <div id="materiales-container">
                      <!-- Fila de material base -->
                      <div class="parte-row row align-items-end mb-3 border rounded p-3 bg-primary bg-opacity-10">
                        <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
                          <label class="form-label fw-bold mb-0">Categoría:</label>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                          <select name="categoria[]" class="form-control categoria-select" required>
                            <option value="">Seleccione categoría</option>
                            <?php foreach (array_keys($materialesPorCategoria) as $cat): ?>
                              <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
                          <label class="form-label fw-bold mb-0">Material:</label>
                        </div>
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                          <select name="id_material[]" class="form-control material-select" disabled required>
                            <option value="">Seleccione un material</option>
                            <?php foreach ($materiales as $material): ?>
                              <option value="<?= htmlspecialchars($material['id_material']) ?>"
                                      data-categoria="<?= htmlspecialchars($material['c_nombre'] ?? $material['categoria']) ?>"
                                      data-unidad="<?= htmlspecialchars($material['u_medida']) ?>">
                                  <?= htmlspecialchars($material['m_nombre']) ?> 
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <!-- Precio -->
                        <div class="col-12 col-md-2 mt-3">
                          <label class="form-label">Precio</label>
                          <input type="float" step="0.01" name="precio[]" placeholder="Precio" class="form-control" required>
                        </div>

                        <!-- Cantidad -->
                        <div class="col-12 col-md-4 mt-3">
                          <label class="form-label">Cantidad</label>
                          <div class="input-group">
                            <input type="number" name="cantidad[]" placeholder="Cantidad" class="form-control cantidad-input" required>
                            <span class="input-group-text unidad-medida">--</span>
                          </div>
                        </div>

                        <!-- Subtotal -->
                        <div class="col-12 col-md-3 mt-3">
                          <label class="form-label">Subtotal</label>
                          <input type="number" name="sub_total[]" placeholder="Subtotal" class="form-control" readonly>
                        </div>

                        <!-- Botón eliminar -->
                        <div class="col-12 col-md-3 mt-3">
                          <button type="button" class="btn btn-danger btn-sm remove-parte w-100">X</button>
                        </div>
                      </div>
                    </div>

                  </div>
 
                    <!-- Total -->
                    <div class="row mt-3">
                      <div class="col-12 col-md-2 fw-bold d-flex align-items-center justify-content-center justify-content-md-start mb-2 mb-md-0">
                        TOTAL:
                      </div>
                      <div class="col-12 col-md-4">
                        <input type="text" id="totalGeneral" class="form-control" readonly>
                      </div>
                    </div>

                    <!-- Botón Registrar -->
                    <div class="row mt-4">
                      <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8">
                        <button type="submit" name="accion" value="crear" class="btn btn-register w-100 btn-responsive">REGISTRAR</button>
                      </div>
                    </div>
                  </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">INGRESAR MATERIALES</h3>
    
    <!-- Formulario de búsqueda responsivo -->
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="Ingreso.php" method="get">
      <div class="d-flex flex-grow-1 me-md-2">
        <input type="text" name="search" placeholder="Buscar por nombre, ID o fecha" 
               value="<?php echo htmlspecialchars($searchTerm); ?>" 
               class="form-control me-2">
        <button type="submit" class="btn btn-info flex-shrink-0 btn-responsive">Buscar</button>
      </div>
      <button type="button" class="btn btn-success btn-responsive" id="btnCrearIngreso" data-bs-toggle="modal" data-bs-target="#ingresoModal">
        Registrar Ingreso
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
            <th>Proveedor</th>
            <th>Total Ingreso</th>
            <th>Fecha de ingreso</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($ingresos)): ?>
            <?php foreach ($ingresos as $ingreso): ?>
              <tr>
                <td><?php echo htmlspecialchars($ingreso['proveedor_nombre']); ?></td>
                <td><?php echo htmlspecialchars($ingreso['total_ingreso'] . " Bs"); ?></td>
                <td><?php echo htmlspecialchars($ingreso['i_fecha']); ?></td>
                <td>
                  <div class="d-flex flex-column flex-md-row gap-1">
                    <a href="#" class="btn btn-info btn-sm btn-ver-ingreso btn-responsive" data-id="<?php echo $ingreso['id_ingreso']; ?>">Ver</a>
                    <a href="Ingreso.php?id_ingreso=<?php echo $ingreso['id_ingreso']; ?>&accion=delete" class="btn btn-danger btn-sm btn-responsive" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro de ingreso?');">Eliminar</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">No se encontraron resultados.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Template oculto para duplicar -->
        <div id="parte-template" class="d-none">
          <div class="parte-row row align-items-end mb-2 border rounded p-3 bg-primary bg-opacity-10">
            <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
              <label class="form-label fw-bold mb-0">Categoría:</label>
            </div>
            <div class="col-12 col-md-4 mb-2 mb-md-0">
              <select name="categoria[]" class="form-control categoria-select" required>
                <option value="">Seleccione categoría</option>
                <?php foreach (array_keys($materialesPorCategoria) as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
              <label class="form-label fw-bold mb-0">Material:</label>
            </div>
            <div class="col-12 col-md-4 mb-2 mb-md-0">
              <select name="id_material[]" class="form-control material-select" disabled required>
                <option value="">Seleccione un material</option>
                <?php foreach ($materiales as $material): ?>
                  <option value="<?= htmlspecialchars($material['id_material']) ?>"
                          data-categoria="<?= htmlspecialchars($material['c_nombre'] ?? $material['categoria']) ?>"
                          data-unidad="<?= htmlspecialchars($material['u_medida']) ?>">
                      <?= htmlspecialchars($material['m_nombre']) ?> 
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-2 mt-3">
              <label class="form-label">Precio</label>
              <input type="float" step="0.01" name="precio[]" placeholder="Precio" class="form-control" required>
            </div>
            <div class="col-12 col-md-4 mt-3">
              <label class="form-label">Cantidad</label>
              <div class="input-group">
                <input type="number" name="cantidad[]" placeholder="Cantidad" class="form-control cantidad-input" required>
                <span class="input-group-text unidad-medida">--</span>
              </div>
            </div>
            <div class="col-12 col-md-3 mt-3">
              <label class="form-label">Subtotal</label>
              <input type="number" name="sub_total[]" placeholder="Subtotal" class="form-control" readonly>
            </div>
            <div class="col-12 col-md-3 mt-3">
              <button type="button" class="btn btn-danger btn-sm remove-parte w-100">X</button>
            </div>
          </div>
        </div>  
        <!-- /* Fin del template oculto */    -->
      </main>

<!-- Modal Detalle Ingreso -->
<div class="modal fade" id="detalleIngresoModal" tabindex="-1" aria-labelledby="detalleIngresoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleIngresoLabel">Detalle de Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="detalleIngresoBody">
        <div class="text-center">
          <span class="spinner-border"></span> Cargando...
        </div>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {

  // ----------- Modal Ver Detalle de Ingreso (AJAX) -----------
  document.querySelectorAll('.btn-ver-ingreso').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const idIngreso = this.getAttribute('data-id');
      const modalBody = document.getElementById('detalleIngresoBody');
      modalBody.innerHTML = "<div class='text-center'><span class='spinner-border'></span> Cargando...</div>";
      const modal = new bootstrap.Modal(document.getElementById('detalleIngresoModal'));
      modal.show();

      fetch('detalle_ingreso.php?id=' + idIngreso)
        .then(response => response.text())
        .then(html => {
          modalBody.innerHTML = html;
        })
        .catch(() => {
          modalBody.innerHTML = "<div class='alert alert-danger'>Error al cargar el detalle.</div>";
        });
    });
  });

  // ----------- Modal Crear Ingreso (limpieza de formulario) -----------
  const btnCrearIngreso = document.getElementById("btnCrearIngreso");
  if (btnCrearIngreso) {
    btnCrearIngreso.addEventListener("click", () => {
      const form = document.getElementById("formIngreso");
      form.querySelectorAll("input, textarea").forEach(input => input.value = "");
      const idInput = document.getElementById("id_material");
      if (idInput) idInput.remove();
    });
  }

  // ----------- Lógica de materiales dinámicos -----------
  const btnAgregar = document.getElementById('btnAgregar');
  const container = document.getElementById('materiales-container');
  const totalInput = document.getElementById('totalGeneral');
  const parteTemplate = document.getElementById('parte-template');

  // Calcular subtotal y total general
  function calcularTotales() {
    let total = 0;
    container.querySelectorAll('.parte-row').forEach(row => {
      const precio = parseFloat(row.querySelector('input[name="precio[]"]').value) || 0;
      const cantidad = parseFloat(row.querySelector('input[name="cantidad[]"]').value) || 0;
      const subtotal = precio * cantidad;
      row.querySelector('input[name="sub_total[]"]').value = subtotal.toFixed(2);
      total += subtotal;
    });
    totalInput.value = total.toFixed(2);
  }

  // Obtener materiales seleccionados (para evitar duplicados)
  function materialesSeleccionados() {
    return Array.from(container.querySelectorAll('.material-select'))
      .map(sel => sel.value)
      .filter(v => v !== "");
  }

  // Configurar eventos de una fila
  function configurarFila(row) {
    const categoriaSelect = row.querySelector('.categoria-select');
    const materialSelect = row.querySelector('.material-select');
    const precio = row.querySelector('input[name="precio[]"]');
    const cantidad = row.querySelector('input[name="cantidad[]"]');
    const unidad = row.querySelector('.unidad-medida');

    // Deshabilitar material al inicio
    materialSelect.disabled = true;

    // --- Evento: cambio de categoría ---
    categoriaSelect.addEventListener('change', () => {
      const categoria = categoriaSelect.value;
      materialSelect.disabled = (categoria === "");
      materialSelect.value = "";
      unidad.textContent = "--";

      materialSelect.querySelectorAll('option').forEach(opt => {
        const pertenece = opt.getAttribute('data-categoria');
        const seleccionados = materialesSeleccionados();
        if (opt.value === "" || (pertenece === categoria && !seleccionados.includes(opt.value))) {
          opt.hidden = false;
        } else {
          opt.hidden = true;
        }
      });
    });

    // --- Evento: cambio de material ---
    materialSelect.addEventListener('change', () => {
      if (!categoriaSelect.value) {
        alert("Debe seleccionar una categoría antes del material.");
        materialSelect.value = "";
        materialSelect.disabled = true;
        return;
      }
      const unidadText = materialSelect.selectedOptions[0]?.getAttribute("data-unidad") || "--";
      unidad.textContent = unidadText;
    });

    // --- Eventos: recalcular totales ---
    [precio, cantidad].forEach(inp => inp.addEventListener('input', calcularTotales));

    // --- Botón eliminar fila ---
    row.querySelector('.remove-parte').addEventListener('click', () => {
      row.remove();
      calcularTotales();
      if (container.querySelectorAll('.parte-row').length === 0) {
        agregarFila();
      }
    });
  }

  // --- Agregar nueva fila desde template ---
  function agregarFila() {
    if (!parteTemplate) return;
    const templateRow = parteTemplate.querySelector('.parte-row');
    if (!templateRow) return;
    const clone = templateRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    clone.querySelectorAll('select').forEach(s => s.value = '');
    clone.querySelector('.unidad-medida').textContent = '--';
    configurarFila(clone);
    container.appendChild(clone);
  }

  // Inicializar la primera fila visible
  if (container.firstElementChild) {
    configurarFila(container.firstElementChild);
  }

  // --- Botón agregar fila ---
  if (btnAgregar) btnAgregar.addEventListener('click', agregarFila);

  // --- Evento global de cambio de material (unidad de medida) ---
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("material-select")) {
      const unidad = e.target.selectedOptions[0].getAttribute("data-unidad") || "--";
      const row = e.target.closest(".parte-row");
      row.querySelector(".unidad-medida").textContent = unidad;
    }
  });

  // --- Validaciones de entrada ---
  document.addEventListener('input', function(e) {

    // 🔹 Solo números en campo cantidad
    if (e.target.name === 'cantidad[]') {
      e.target.value = e.target.value.replace(/[^0-9]/g, '');
            if (e.target.value.length > 1 && e.target.value.startsWith('0')) {
                e.target.value = e.target.value.replace(/^0+/, '');
            } else if (e.target.value === '0') {
                e.target.value = '';
            }
        }

    // 🔹 Validar precio (sin negativos, solo números y punto)
    if (e.target.name === 'precio[]') {
      e.target.value = e.target.value.replace(/[^0-9.]/g, ''); // eliminar cualquier carácter no permitido
      if (e.target.value.startsWith('.')) {
        e.target.value = ''; // evitar que empiece con un punto
      }
      const parts = e.target.value.split('.');
      if (parts.length > 2) {
        e.target.value = parts[0] + '.' + parts[1]; // solo un punto decimal
      }
      if (e.target.value.includes('-')) {
        e.target.value = e.target.value.replace('-', ''); // quitar signos negativos
      }
    }
  });

});
</script>
<script>
function imprimirModal(id) {
    var contenido = document.getElementById(id).innerHTML;
    
    var ventana = window.open('', '', 'width=900,height=700');
    ventana.document.write('<html><head><title>Detalle de Ingreso</title>');
    ventana.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">');
    ventana.document.write('<style>');
    ventana.document.write('@media print {');
    ventana.document.write('  body { margin: 20px; font-family: Arial, sans-serif; }');
    ventana.document.write('  .btn-print { display: none !important; }');
    ventana.document.write('}');
    ventana.document.write('body { padding: 30px; background: white; }');
    ventana.document.write('h2 { color: #2c3e50; text-align: center; margin-bottom: 30px; font-weight: bold; }');
    ventana.document.write('.info-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #dee2e6; }');
    ventana.document.write('.info-row { display: flex; justify-content: space-between; margin-bottom: 15px; }');
    ventana.document.write('.info-item { flex: 1; }');
    ventana.document.write('.info-item strong { color: #495057; display: block; margin-bottom: 5px; font-size: 12px; text-transform: uppercase; }');
    ventana.document.write('.info-item p { color: #212529; margin: 0; font-size: 14px; font-weight: 500; }');
    ventana.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }');
    ventana.document.write('thead { background: #343a40; color: white; }');
    ventana.document.write('th { padding: 12px; text-align: left; font-weight: 600; }');
    ventana.document.write('td { padding: 10px; border-bottom: 1px solid #dee2e6; }');
    ventana.document.write('tbody tr:hover { background: #f8f9fa; }');
    ventana.document.write('tfoot { background: #e9ecef; font-weight: bold; }');
    ventana.document.write('tfoot th { padding: 12px; color: #212529; }');
    ventana.document.write('.btn-print { margin-top: 20px; }');
    ventana.document.write('</style>');
    ventana.document.write('</head><body>');
    ventana.document.write('<h2> DETALLE DE INGRESO </h2>');
    ventana.document.write(contenido);
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.focus();
    
    setTimeout(function() {
        ventana.print();
        ventana.close();
    }, 250);
}
</script>
</body>
</html>