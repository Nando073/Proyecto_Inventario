<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Ingreso.php';

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// exit();

$ingresoService = new N_Ingreso();
$detalleService = new N_Ingreso();

$detalle = null;
$ingreso = null;

//eliminar ingreso por id
$ingresoService = new N_Ingreso();
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
            header('Location: historial_registro.php?msg=' . urlencode($mensaje));

            exit();
        } catch (Exception $e) {
            echo "Error al registrar: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Error: Acción no válida.";
    }
}

// Carga inicial de datos

$proveedores = $ingresoService->obtenerProveedores();
$materiales = $detalleService->obtenerMateriales();

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
  </style>
</head>
<body>
<?php include '../DEMO/index.php'; ?>
<main>
   <!-- Modal -->
    <div class="modal fade" id="ingresoModal" tabindex="-1" aria-labelledby="ingresoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ingresoModalLabel">Crear registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <form id="formIngreso" action="Ingreso.php" method="post">
      <div class="row mb-3">
        <div class="col-md-3">
          <label for="id_proveedor" class="form-label fw-bold">Proveedor:</label>
        </div>
        <div class="col-md-6">
          <select name="id_proveedor" id="id_proveedor" class="form-control" required>
            <option value="">Seleccione un proveedor</option>
            <?php
                foreach ($proveedores as $proveedor) {
                    echo "<option value='" . htmlspecialchars($proveedor['id_proveedor']) . "'>" . htmlspecialchars($proveedor['p_nombre']) . "</option>";
                }
            ?>
          </select>  
        </div>
        <div class="col-md-3">
          <button type="button" class="btn btn-add w-100" id="btnAgregar">AÑADIR MATERIAL</button>
        </div>
      </div>
      <div id="materiales-container">
        <div class="parte-row row align-items-end mb-2">
          <div class="col-md-3">
            <select name="id_material[]" class="form-control" required>
              <option value="">Seleccione un material</option>
              <?php
                  foreach ($materiales as $material) {
                      echo "<option value='" . htmlspecialchars($material['id_material']) . "'>" .
                          htmlspecialchars($material['m_nombre']) . " (Stock: " . htmlspecialchars($material['stock']) . ")" .
                          "</option>";
                  }
              ?>
            </select>
          </div>
          <div class="col-md-2"><input name="precio[]" placeholder="Precio" class="form-control" required></div>
          <div class="col-md-2"><input name="cantidad[]" placeholder="Cantidad" class="form-control" required></div>
          <div class="col-md-3"><input name="sub_total[]" placeholder="Sub_total" class="form-control" required></div>
          <div class="col-md-1 text-center">
            <button type="button" class="btn btn-danger btn-sm remove-parte">X</button>
          </div>
        </div>
      </div>
      
      <!-- Total -->
      <div class="row mt-3">
        <div class="col-md-2 fw-bold">TOTAL:</div>
        <div class="col-md-4">
          <input type="text" id="totalGeneral" class="form-control" readonly>
        </div>
      </div>

      <!-- Botón Registrar -->
      <div class="row mt-4">
        <div class="col-md-3 offset-md-9">
          <button type="submit" name="accion" value="crear" class="btn btn-register w-100" >REGISTRAR</button>
        </div>
      </div>
    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">INGRESAR MATERIALES</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="Ingreso.php" method="get">
    <div>
        <input type="text" name="search" placeholder="Buscar por nombre, ID o fecha" value="<?php echo htmlspecialchars($searchTerm); ?>" />
        <button type="submit" class="btn btn-info">Buscar</button>
    </div>
    <button type="button" class="btn btn-success m-3" id="btnCrearIngreso" data-bs-toggle="modal" data-bs-target="#ingresoModal">
        Registrar Ingreso de material
    </button>
</form>

<!-- tabla -->
<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>ID</th>
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
                    <td><?php echo htmlspecialchars($ingreso['id_ingreso']); ?></td>
                    <td><?php echo htmlspecialchars($ingreso['proveedor_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($ingreso['total_ingreso']); ?></td>
                    <td><?php echo htmlspecialchars($ingreso['i_fecha']); ?></td>
                    <td>
                        <a href="#" class="btn btn-info btn-ver-ingreso" data-id="<?php echo $ingreso['id_ingreso']; ?>">Ver</a>
                        <a href="Ingreso.php?id_material=<?php echo $ingreso['id_ingreso']; ?>&accion=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro de ingreso?');">Eliminar</a>
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
  <!-- Template oculto para duplicar -->
<div id="parte-template" class="parte-row row align-items-end mb-2 d-none">
  <div class="col-md-3">
    <select name="id_material[]" class="form-control" required>
      <option value="">Seleccione un material</option>
      <?php
          foreach ($materiales as $material) {
              echo "<option value='" . htmlspecialchars($material['id_material']) . "'>" .
                  htmlspecialchars($material['m_nombre']) . " (Stock: " . htmlspecialchars($material['stock']) . ")" .
                  "</option>";
          }
      ?>
    </select>
  </div>
  <div class="col-md-2"><input name="precio[]" placeholder="Precio" class="form-control" required></div>
  <div class="col-md-2"><input name="cantidad[]" placeholder="Cantidad" class="form-control" required></div>
  <div class="col-md-3"><input name="sub_total[]" placeholder="Sub_total" class="form-control" required></div>
  <div class="col-md-1 text-center">
    <button type="button" class="btn btn-danger btn-sm remove-parte">X</button>
  </div>
</div>        
<a href="historial_registro.php"><button type="button"class="btn btn-info">Historial de Registro</button></a>
</main>

<!-- Modal Detalle Ingreso -->
<div class="modal fade" id="detalleIngresoModal" tabindex="-1" aria-labelledby="detalleIngresoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
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
    document.querySelectorAll('.btn-ver-ingreso').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const idIngreso = this.getAttribute('data-id');
            const modalBody = document.getElementById('detalleIngresoBody');
            modalBody.innerHTML = "<div class='text-center'><span class='spinner-border'></span> Cargando...</div>";
            const modal = new bootstrap.Modal(document.getElementById('detalleIngresoModal'));
            modal.show();

            fetch('detalle_ingreso_ajax.php?id=' + idIngreso)
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
// abrira el modal
document.getElementById("btnCrearIngreso").addEventListener("click", function () {
    const form = document.getElementById("formIngreso");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_material");
    if (idInput) idInput.remove();
});

document.addEventListener('DOMContentLoaded', () => {
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
    template.classList.remove('d-none'); // Mostrar la fila
    template.removeAttribute('id'); // Eliminar el ID para evitar duplicados
    template.querySelectorAll('input').forEach(input => input.value = '');
    template.querySelector('select').value = '';
    template.querySelector('.remove-parte').addEventListener('click', () => {
        template.remove();
        calcularTotal();
    });
    actualizarSubtotal(template);
    partesContainer.appendChild(template);
}

  document.querySelectorAll('.parte-row').forEach(row => {
    actualizarSubtotal(row);
    row.querySelector('.remove-parte').addEventListener('click', () => {
      row.remove();
      calcularTotal();
    });
  });

  btnAgregar.addEventListener('click', agregarFila);
});
</script>
</body>
</html>