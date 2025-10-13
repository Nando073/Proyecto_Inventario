<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Ingreso.php';
require_once '../NEGOCIO/N_Material.php';
require_once '../NEGOCIO/N_Proveedor.php';

$ingresoService = new N_Ingreso();
$materialService = new N_Material();
$provService = new N_Proveedor();

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
            $ingresoService->registrarIngresoCompleto($id_proveedor, $totalCalculado, $detallesValidos);
            echo "<script>
                    alert('¡Ingreso registrado correctamente!');
                    window.location.href='Ingreso.php';
                  </script>";
            exit();
        } catch (Exception $e) {
            echo "Error al registrar ingreso: " . htmlspecialchars($e->getMessage());
        }
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
    .btn-delete {
      background-color: #c0392b;
      color: white;
    }
    .btn-register {
      background-color: #8e44ad;
      color: white;
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
                      <div class="parte-row row align-items-end mb-2">
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
                          <input type="number" step="0.01" name="precio[]" placeholder="Precio" class="form-control" required>
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
          <div class="parte-row row align-items-end mb-2">
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
              <input type="number" step="0.01" name="precio[]" placeholder="Precio" class="form-control" required>
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
  document.getElementById("btnCrearIngreso").addEventListener("click", function () {
    const form = document.getElementById("formIngreso");
    form.querySelectorAll("input, textarea").forEach(input => input.value = "");
    const idInput = document.getElementById("id_material");
    if (idInput) idInput.remove();
  });

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

    // Cuando cambia la categoría, habilita y filtra los materiales
    categoriaSelect.addEventListener('change', () => {
      const categoria = categoriaSelect.value;
      materialSelect.disabled = (categoria === "");
      materialSelect.value = "";
      unidad.textContent = "--";

      materialSelect.querySelectorAll('option').forEach(opt => {
        const pertenece = opt.getAttribute('data-categoria');
        const seleccionado = materialesSeleccionados();
        if (opt.value === "" || (pertenece === categoria && !seleccionado.includes(opt.value))) {
          opt.hidden = false;
        } else {
          opt.hidden = true;
        }
      });
    });

    // Al elegir material → muestra unidad
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

    // Precio y cantidad recalculan subtotal y total
    [precio, cantidad].forEach(inp => inp.addEventListener('input', calcularTotales));

    // Botón eliminar
    row.querySelector('.remove-parte').addEventListener('click', () => {
      row.remove();
      calcularTotales();
      // Si no queda ninguna fila, agrega una nueva desde el template
      if (container.querySelectorAll('.parte-row').length === 0) {
        agregarFila();
      }
    });
  }

  // Añadir nueva fila desde el template oculto
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

  // Evento para añadir fila
  btnAgregar.addEventListener('click', agregarFila);

  // ----------- Evento de cambio para materiales (unidad de medida) -----------
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("material-select")) {
      const unidad = e.target.selectedOptions[0].getAttribute("data-unidad") || "--";
      const row = e.target.closest(".parte-row");
      row.querySelector(".unidad-medida").textContent = unidad;
    }
  });
});
</script>

</body>
</html>