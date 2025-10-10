<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Ingreso.php';
require_once '../NEGOCIO/N_Material.php';
require_once '../NEGOCIO/N_Proveedor.php';

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// exit();

$ingresoService = new N_Ingreso();

$materialService = new N_Material();
$provService = new N_Proveedor();

$detalle = null;
$ingreso = null;

//eliminar ingreso por id
$ingresos = $ingresoService->ObtenerIngresosRegistrado();

    // Verificar si se ha solicitado eliminar un ingreso
if (isset($_GET['id_material']) && $_GET['accion'] === 'delete') {
    $id_material = filter_input(INPUT_GET, 'id_material', FILTER_VALIDATE_INT);

    if ($id_material) {
        try {
            $ingresoService->eliminarIngreso($id_material);
            header('Location: Ingreso.php?msg=Ingreso eliminado correctamente');
            exit();
        } catch (Exception $e) {
            echo "Error al eliminar el ingreso: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "ID de ingreso no válido.";
    }
}

// Procesar POST para registrar ingreso y detalles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_SANITIZE_SPECIAL_CHARS);
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_SPECIAL_CHARS);

    $id_material = $_POST['id_material'] ?? [];
    $precios = $_POST['precio'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $subtotales = $_POST['sub_total'] ?? [];

    // Filtrar filas incompletas
    $id_material = array_filter($id_material, fn($value) => !empty($value));
    $precios = array_filter($precios, fn($value) => !empty($value));
    $cantidades = array_filter($cantidades, fn($value) => !empty($value));
    $subtotales = array_filter($subtotales, fn($value) => !empty($value));

    // Validar que los arrays tengan la misma longitud
    if (count($id_material) !== count($precios) || count($id_material) !== count($cantidades) || count($id_material) !== count($subtotales)) {
        echo "Error: Los detalles no están sincronizados.";
        exit();
    }

    $totalCalculado = 0;
    $detallesValidos = [];

    for ($i = 0; $i < count($id_material); $i++) {
        $idMat = isset($id_material[$i]) ? trim($id_material[$i]) : null;
        $precio = isset($precios[$i]) ? filter_var($precios[$i], FILTER_VALIDATE_FLOAT) : false;
        $cantidad = isset($cantidades[$i]) ? filter_var($cantidades[$i], FILTER_VALIDATE_INT) : false;
        $subtotal = isset($subtotales[$i]) ? filter_var($subtotales[$i], FILTER_VALIDATE_FLOAT) : false;

        if (empty($idMat) || $precio === false || $cantidad === false || $subtotal === false) {
            echo "Error: Verifica que todos los detalles estén completos y válidos en la fila " . ($i + 1) . ".";
            exit();
        }

        $totalCalculado += $subtotal;
        $detallesValidos[] = [
            'id_material' => $idMat,
            'precio' => $precio,
            'cantidad' => $cantidad,
            'sub_total' => $subtotal
        ];
    }

    // Procesar acción
    if ($accion === 'crear') {
        try {
            $mensaje = $ingresoService->registrarIngresoCompleto($id_proveedor, $totalCalculado, $detallesValidos);
            echo "<script>
                alert('¡Ingreso registrado correctamente!');
                window.location.href='Ingreso.php';
            </script>";
            exit();
        } catch (Exception $e) {
            echo "Error al registrar: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Error: Acción no válida.";
    }
}

// Carga inicial de datos

$materiales = $materialService->obtenerMateriales();
$proveedores = $provService->obtenerProveedoresActivos();

// buscar
$ingresoService = new N_Ingreso();
$ingresos = $ingresoService->ObtenerIngresosRegistrado();
// Buscador
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
                    
                    <div id="materiales-container">
                      <div class="parte-row row align-items-end mb-2">
                        <!-- Material -->
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                          <label class="form-label">Material</label>
                          <select name="id_material[]" class="form-control material-select" required>
                            <option value="">Seleccione un material</option>
                            <?php
                                foreach ($materiales as $material) {
                                    echo "<option value='" . htmlspecialchars($material['id_material']) . "' 
                                                  data-unidad='" . htmlspecialchars($material['u_medida']) . "'>" .
                                            htmlspecialchars($material['m_nombre']) . 
                                            " (Stock: " . htmlspecialchars($material['stock']) . ")" .
                                        "</option>";
                                }
                            ?>
                          </select>
                        </div>
                        
                        <!-- Precio -->
                        <div class="col-12 col-md-2 mb-2 mb-md-0">
                          <label class="form-label">Precio</label>
                          <input type="number" step="0.01" name="precio[]" placeholder="Precio" class="form-control" required>
                        </div>
                        
                        <!-- Cantidad -->
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                          <label class="form-label">Cantidad</label>
                          <div class="input-group">
                            <input type="number" name="cantidad[]" placeholder="Cantidad" class="form-control cantidad-input" required>
                            <span class="input-group-text unidad-medida">--</span>
                          </div>
                        </div>

                        
                        <!-- Subtotal -->
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                          <label class="form-label">Subtotal</label>
                          <input type="number" name="sub_total[]" placeholder="Subtotal" class="form-control" required>
                        </div>
                        
                        <!-- Botón Eliminar -->
                        <div class="col-12 col-md-1 text-center mb-2 mb-md-0">
                          <label class="form-label" style="visibility: hidden;">Eliminar</label>
                          <button type="button" class="btn btn-danger btn-sm remove-parte w-100">X</button>
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
                    <a href="Ingreso.php?id_material=<?php echo $ingreso['id_ingreso']; ?>&accion=delete" class="btn btn-danger btn-sm btn-responsive" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro de ingreso?');">Eliminar</a>
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
    <div id="parte-template" class="parte-row row align-items-end mb-2 d-none">
      <div class="col-12 col-md-3 mb-2 mb-md-0">
        <select name="id_material[]" class="form-control material-select" required>
          <option value="">Seleccione un material</option>
          <?php
              foreach ($materiales as $material) {
                  echo "<option value='" . htmlspecialchars($material['id_material']) . "' 
                                data-unidad='" . htmlspecialchars($material['u_medida']) . "'>" .
                          htmlspecialchars($material['m_nombre']) . 
                          " (Stock: " . htmlspecialchars($material['stock']) . ")" .
                      "</option>";
              }
          ?>
        </select>
      </div>
      <div class="col-12 col-md-2 mb-2 mb-md-0">
        <input type="number" name="precio[]" placeholder="Precio" class="form-control" required>
      </div>
      <div class="col-12 col-md-3 mb-2 mb-md-0">
        <div class="input-group">
          <input type="number" name="cantidad[]" placeholder="Cantidad" class="form-control cantidad-input" required>
          <span class="input-group-text unidad-medida">--</span>
        </div>
      </div>
      <div class="col-12 col-md-3 mb-2 mb-md-0">
        <input type="number" name="sub_total[]" placeholder="Sub_total" class="form-control" required>
      </div>
      <div class="col-12 col-md-1 text-center mb-2 mb-md-0">
        <button type="button" class="btn btn-danger btn-sm remove-parte w-100">X</button>
      </div>
    </div>        
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
  // ----------- Modal Ver Detalle de Ingreso -----------
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
  const partesContainer = document.getElementById('materiales-container');
  const totalInput = document.getElementById('totalGeneral');

  function calcularTotal() {
    let total = 0;
    document.querySelectorAll('input[name="sub_total[]"]').forEach(input => {
      const val = parseFloat(input.value);
      if (!isNaN(val)) total += val;
    });
    totalInput.value = total.toFixed(2);
  }

  function actualizarSubtotal(row) {
    const precioInput = row.querySelector('input[name="precio[]"]');
    const cantidadInput = row.querySelector('input[name="cantidad[]"]');
    const subtotalInput = row.querySelector('input[name="sub_total[]"]');

    function calcularSubtotal() {
      const precio = parseFloat(precioInput.value) || 0;
      const cantidad = parseFloat(cantidadInput.value) || 0;
      subtotalInput.value = (precio * cantidad).toFixed(2);
      calcularTotal();
    }

    precioInput.addEventListener('input', calcularSubtotal);
    cantidadInput.addEventListener('input', calcularSubtotal);
  }

  function agregarFila() {
    const template = document.getElementById('parte-template').cloneNode(true);
    template.classList.remove('d-none');
    template.removeAttribute('id');
    template.querySelectorAll('input').forEach(input => input.value = '');
    template.querySelector('select').value = '';
    template.querySelector('.unidad-medida').textContent = '--';

    template.querySelector('.remove-parte').addEventListener('click', () => {
      template.remove();
      calcularTotal();
    });

    actualizarSubtotal(template);
    partesContainer.appendChild(template);
  }

  // Inicializar filas existentes
  document.querySelectorAll('.parte-row').forEach(row => {
    actualizarSubtotal(row);
    row.querySelector('.remove-parte').addEventListener('click', () => {
      row.remove();
      calcularTotal();
    });
  });

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