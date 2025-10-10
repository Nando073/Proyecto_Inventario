
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); verificar errores


require_once '../../Seguridad.php';
verificarAcceso(['Administrador','Supervisor', 'Funcionario', 'Operador']);
require_once '../../NEGOCIO/N_Egreso.php';
require_once '../../NEGOCIO/N_Material.php';
// Instanciar el servicio de egreso
$egresoService = new N_Egreso();
$materialService = new N_Egreso();

// Obtener materiales agrupados por categoría
$materiales = $materialService->obtenerStockTotalPorMaterial();

// Agrupar materiales por categoría
$materialesPorCategoria = [];
foreach ($materiales as $mat) {
    $cat = $mat['c_nombre'];
    if (!isset($materialesPorCategoria[$cat])) {
        $materialesPorCategoria[$cat] = [];
    }
    $materialesPorCategoria[$cat][] = $mat;
}


// PROCESAR POST DEL EGRESO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_solicitud'])) {
    // 1. Obtener datos del formulario
    $codigoSolicitud = trim($_POST['codigo_solicitud']);
    $materiales = $_POST['materiales'] ?? [];

    // 2. Obtener el id_funcionario (de la sesión, deberías guardarlo al iniciar sesión)
    $id_funcionario = isset($_SESSION['id_funcionario']) ? $_SESSION['id_funcionario'] : null;

    if (!$id_funcionario) {
        echo "<script>alert('No se pudo identificar al usuario.');</script>";
        exit();
    }

    // 3. Calcular el total de cantidades y armar detalles
    $e_total_cantidad = 0;
    $detalles = [];
    foreach ($materiales as $mat) {
        $id_material = isset($mat['id']) ? intval($mat['id']) : 0;
        $cantidad = isset($mat['cantidad']) ? intval($mat['cantidad']) : 0;
        if ($id_material > 0 && $cantidad > 0) {
            $e_total_cantidad += $cantidad;
            $detalles[] = [
                'id_material_e' => $id_material,
                'e_stock' => $cantidad
            ];
        }
    }

    if (empty($detalles)) {
        echo "<script>alert('Debe añadir al menos un material válido.');</script>";
        exit();
    }

    // 4. Registrar el egreso usando la capa de negocio
    try {
    $mensaje = $egresoService->registrarEgresoCompleto($id_funcionario, $codigoSolicitud, $e_total_cantidad, $detalles);
    echo "<script>
        alert('¡Egreso registrado correctamente!\\n$mensaje');
        window.location.href='Egreso.php';
    </script>";
    exit();
} catch (Exception $e) {
    echo "<script>alert('Error al registrar el egreso: " . htmlspecialchars($e->getMessage()) . "');</script>";
}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="styles.css?v=<?php echo(rand()); ?>"> <!-- Tu archivo CSS generado arriba -->
<link rel="stylesheet" href="solicitud.css?v=<?php echo(rand()); ?>">   
</head>
<body class="bg-light">
<header>
  <nav class="navbar fixed-top shadow" style="background-color: rgb(65, 180, 136);">
    <div class="container-fluid">
      <div class="d-flex align-items-center w-100">
        
        <!-- Logo -->
        <a class="navbar-brand me-3 flex-shrink-0" href="#">
          <img src="../../IMG/LOGODDE.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-top">
          <span class="d-none d-sm-inline">D.D.E.</span>
        </a>

        <!-- Menú hamburguesa -->
        <?php if (count(array_intersect(['Administrador', 'Operador'], $_SESSION['rol_asignado'])) > 0): ?>
          <div class="dropdown me-3 flex-shrink-0">
            <button class="menu-back-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-custom">
              <?php if (count(array_intersect(['Administrador'], $_SESSION['rol_asignado'])) > 0): ?>
                <li><a class="dropdown-item" href="../../PRESENTACION/ADM_Usuario.php"><i class="bi bi-people"></i> Administrar Usuarios</a></li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="../../PRESENTACION/ADM_Material.php"><i class="bi bi-box-seam"></i> Administrar Materiales</a></li>
              <li><a class="dropdown-item" href="../../TRANSACCIONAL/Ingreso.php"><i class="bi bi-download"></i> Ingreso de Materiales</a></li>
              <li><a class="dropdown-item" href="../../REPORTES/Stock.php"><i class="bi bi-graph-up"></i> Reportes de Stock</a></li>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Búsqueda -->
        <form class="d-flex search-container me-3 flex-grow-1" role="search">
          <input class="form-control form-control-sm" type="search" placeholder="Buscar material..." aria-label="Search">
          <button class="btn btn-outline-light btn-sm ms-2 flex-shrink-0" type="submit">
            <i class="bi bi-search"></i>
            <span class="ms-1 d-none d-md-inline">Buscar</span>
          </button>
        </form>

        <!-- Carrito y usuario -->
        <div class="d-flex align-items-center flex-shrink-0 ms-auto">
          <!-- Carrito -->
          <button type="button" class="btn cart-btn me-3 position-relative btn-sm" data-bs-toggle="modal" data-bs-target="#modalCarrito">
            <i class="bi bi-cart"></i> 
            <span class="d-none d-md-inline ms-1">Carrito</span>
            <span id="badgeCarrito" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.7em; display:none;">
              0
            </span>
          </button>

          <!-- Usuario -->
          <div class="dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle p-2 rounded user-container" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="user-avatar">
                <?php 
                $iniciales = '';
                $nombres = explode(' ', htmlspecialchars($nombreUsuario));
                if (count($nombres) > 0) {
                  $iniciales = strtoupper(substr($nombres[0], 0, 1));
                  if (count($nombres) > 1) {
                    $iniciales .= strtoupper(substr($nombres[1], 0, 1));
                  }
                }
                echo $iniciales;
                ?>
              </div>
              <span class="user-name ms-2">
                <?php 
                $nombreDisplay = htmlspecialchars($nombreUsuario);
                if (strlen($nombreDisplay) > 20) {
                  $nombreDisplay = substr($nombreDisplay, 0, 18) . '...';
                }
                echo $nombreDisplay;
                ?>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li class="user-info border-bottom">
                <div class="user-avatar bg-primary">
                  <?php echo $iniciales; ?>
                </div>
                <div>
                  <div class="fw-bold"><?php echo htmlspecialchars($nombreUsuario); ?></div>
                  <small class="text-muted">Usuario</small>
                </div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="../../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
<main>
  <div class="container-fluid px-2 py-3">
    <div class="container-fluid px-2 py-3">
      <h2 class="text-center mb-4">📦 Egreso de Materiales</h2>
      
      <!-- ACORDEÓN DE CATEGORÍAS -->
      <div class="accordion" id="categoriasAccordion">
        <?php foreach ($materialesPorCategoria as $categoria => $items): 
          $categoriaId = 'categoria' . md5($categoria);
        ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#<?= $categoriaId ?>" 
                    aria-expanded="false" 
                    aria-controls="<?= $categoriaId ?>">
              <i class="bi bi-folder me-2"></i>
              <?= htmlspecialchars($categoria) ?>
              <span class="badge bg-primary ms-2"><?= count($items) ?></span>
              <i class="bi bi-chevron-down accordion-arrow ms-auto"></i>
            </button>
          </h2>
          <div id="<?= $categoriaId ?>" 
               class="accordion-collapse collapse" 
               data-bs-parent="#categoriasAccordion">
            <div class="accordion-body p-0 pt-3">
              <div class="row">
                <?php foreach ($items as $material): ?>
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                  <div class="card material-card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title"><?= htmlspecialchars($material['m_nombre']) ?></h5>
                      <p class="card-text">Stock: <?= htmlspecialchars($material['stock_total'] . ' ' . $material['u_medida']) ?></p>
                      <div class="mb-2">
                        <label for="cantidad_<?= $material['id_material'] ?>" class="form-label">Cantidad a egresar:</label>
                        <input type="number" min="1" max="<?= htmlspecialchars($material['stock_total']) ?>" 
                            name="cantidad[<?= $material['id_material'] ?>]" 
                            id="cantidad_<?= $material['id_material'] ?>" 
                            class="form-control" placeholder="Cantidad">
                      </div>
                      <div class="mt-auto">
                        <button type="button" class="btn btn-primary btn-sm agregar-egreso w-100 btn-responsive" 
                            data-id="<?= $material['id_material'] ?>">
                            Añadir al carrito
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Modal del carrito (sin cambios) -->
    <div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCarritoLabel">Generar Egreso</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formEgreso" method="post" action="Egreso.php">
            <div class="modal-body">
              <div class="row mb-3">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                  <label class="form-label fw-bold">Usuario:</label>
                  <input type="text" class="form-control" name="nombre_usuario" value="<?= htmlspecialchars($nombreUsuario) ?>" readonly>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label fw-bold">Código de Solicitud:</label>
                  <input type="text" class="form-control" name="codigo_solicitud" placeholder="Ingrese código de solicitud" required>
                </div>
              </div>
              <div class="table-responsive">
                <div id="carrito-materiales">
                  <!-- Aquí se agregan dinámicamente las filas de materiales -->
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-md-6 fw-bold d-flex align-items-center justify-content-center justify-content-md-start mb-2 mb-md-0">
                  Total de materiales añadidos:
                </div>
                <div class="col-12 col-md-6">
                  <input type="text" id="totalMateriales" class="form-control" readonly value="0">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-responsive" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary btn-responsive">Generar Egreso</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
// Tu código JavaScript del carrito - DEBE FUNCIONAR
document.addEventListener('DOMContentLoaded', function() {
    const carrito = [];
    const carritoMateriales = document.getElementById('carrito-materiales');
    const totalMateriales = document.getElementById('totalMateriales');

    // Añadir material al carrito
    document.querySelectorAll('.agregar-egreso').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const cardBody = this.closest('.card-body');
            const nombre = cardBody.querySelector('.card-title').textContent;
            
            // Encuentra la categoría de forma diferente para el acordeón
            const accordionItem = this.closest('.accordion-item');
            const categoria = accordionItem.querySelector('.accordion-button').textContent
                .replace(/[0-9]/g, '') // Remueve el número del badge
                .trim();
                
            const stock = cardBody.querySelector('input[type="number"]').max;
            const cantidadInput = document.getElementById('cantidad_' + id);
            const cantidad = cantidadInput ? cantidadInput.value : '';

            if (!cantidad || cantidad <= 0) {
                alert('Ingrese una cantidad válida para "' + nombre + '"');
                return;
            }
            if (parseInt(cantidad) > parseInt(stock)) {
                alert('No puede egresar más que el stock disponible.');
                return;
            }
            // Evitar duplicados
            if (carrito.find(item => item.id === id)) {
                alert('Este material ya está en el carrito.');
                return;
            }
            carrito.push({id, nombre, categoria, cantidad});
            // Efecto: botón rojo y desactivado
            this.classList.remove('btn-primary');
            this.classList.add('btn-danger');
            this.disabled = true;
            this.textContent = 'Agregado';
            renderCarrito();
            actualizarBadgeCarrito();
        });
    });

    // El resto de tu código JavaScript permanece igual...
    function renderCarrito() {
        carritoMateriales.innerHTML = '';
        let sumaTotal = 0;
        carrito.forEach((item, idx) => {
            sumaTotal += parseInt(item.cantidad) || 0;
            const row = document.createElement('div');
            row.className = 'row align-items-center mb-2';
            row.innerHTML = `
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="hidden" name="materiales[${idx}][id]" value="${item.id}">
                    <input type="text" class="form-control" value="${item.nombre}" readonly>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="text" class="form-control" value="${item.categoria}" readonly>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="number" class="form-control" name="materiales[${idx}][cantidad]" value="${item.cantidad}" readonly>
                </div>
                <div class="col-12 col-md-2 text-center">
                    <button type="button" class="btn btn-danger btn-sm quitar-material btn-responsive" data-idx="${idx}">X</button>
                </div>
            `;
            carritoMateriales.appendChild(row);
        });
        totalMateriales.value = sumaTotal;

        // Botón para quitar material
        document.querySelectorAll('.quitar-material').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.dataset.idx;
                const id = carrito[idx].id;
                carrito.splice(idx, 1);
                // Reactivar el botón en la card
                const btnCard = document.querySelector('.agregar-egreso[data-id="' + id + '"]');
                if (btnCard) {
                    btnCard.classList.remove('btn-danger');
                    btnCard.classList.add('btn-primary');
                    btnCard.disabled = false;
                    btnCard.textContent = 'Añadir al carrito';
                }
                renderCarrito();
                actualizarBadgeCarrito();
            });
        });
        actualizarBadgeCarrito();
    }

    function actualizarBadgeCarrito() {
        const badge = document.getElementById('badgeCarrito');
        let sumaTotal = 0;
        carrito.forEach(item => sumaTotal += parseInt(item.cantidad) || 0);
        if (sumaTotal > 0) {
            badge.textContent = sumaTotal;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    document.getElementById('formEgreso').addEventListener('submit', function(e) {
        if (carrito.length === 0) {
            alert('Debe añadir al menos un material al carrito.');
            e.preventDefault();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>