
<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Seguridad.php';
verificarAcceso(['Administrador','Supervisor', 'Funcionario', 'Operador']);
require_once __DIR__ . '/../../NEGOCIO/N_Egreso.php';
require_once __DIR__ . '/../../NEGOCIO/N_Material.php';
require_once __DIR__ . '/../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';
// Instanciar el servicio de egreso
$egresoService = new N_Egreso();
$materialService = new N_Egreso();

$solicitudService = new N_Solicitud();

// Verificar si hay solicitudes pendientes para mostrar indicador
$haySolicitudesPendientes = false;
if (count(array_intersect(['Administrador', 'Supervisor'], $_SESSION['rol_asignado'])) > 0) {
    try {
        $solicitudesPendientes = $solicitudService->obtenerSolicitudesCabecera();
        $haySolicitudesPendientes = !empty($solicitudesPendientes);
    } catch (Exception $e) {
        $haySolicitudesPendientes = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['materiales'])) {
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    $materiales = $_POST['materiales']; // Array: materiales[0][id], materiales[0][cantidad]
    $detalle = trim($_POST['detalle'] ?? '');

    if (!$id_usuario) {
        echo "<script>alert('No se pudo identificar al usuario.');</script>";
        exit();
    }

    // Armar array de detalles para el procedimiento
    $detalles_solicitud = [];
    foreach ($materiales as $mat) {
        $id_material = isset($mat['id']) ? intval($mat['id']) : 0;
        $cantidad = isset($mat['cantidad']) ? intval($mat['cantidad']) : 0;
        if ($id_material > 0 && $cantidad > 0) {
            $detalles_solicitud[] = [
                'id_material' => $id_material,
                'cantidad' => $cantidad
            ];
        }
    }

    if (empty($detalles_solicitud)) {
        echo "<script>alert('Debe añadir al menos un material válido.');</script>";
        exit();
    }

    try {
        $solicitudService->registrarSolicitudConDetalles($id_usuario, $detalle, $detalles_solicitud);
        echo "<script>
            alert('¡Solicitud registrada correctamente!');
            window.location.href='Generar_Solicitud.php';
        </script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Error al registrar la solicitud: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}


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
   <link rel="stylesheet" href="<?php echo url('TRANSACCIONAL/CATALOGO/solicitud.css?v=' . rand()); ?>">     
</head>
<body class="bg-light">
<header>
  <nav class="navbar fixed-top shadow" style="background-color: rgb(65, 180, 136);">
    <div class="container-fluid">
      <div class="d-flex align-items-center w-100">
        
        <!-- Logo -->
        <a class="navbar-brand me-3 flex-shrink-0" href="<?php echo url(); ?>">
          <img src="<?php echo url('IMG/LOGODDE.png'); ?>" alt="Logo" width="50" height="50" class="d-inline-block align-text-top">
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
                <li><a class="dropdown-item" href="<?php echo url('PRESENTACION/ADM_Usuario.php'); ?>"><i class="bi bi-people"></i> Administrar Usuarios</a></li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="<?php echo url('PRESENTACION/ADM_Material.php'); ?>"><i class="bi bi-box-seam"></i> Administrar Materiales</a></li>
              <li><a class="dropdown-item" href="<?php echo url('TRANSACCIONAL/Ingreso.php'); ?>"><i class="bi bi-download"></i> Ingreso de Materiales</a></li>
              <li><a class="dropdown-item" href="<?php echo url('REPORTES/Stock.php'); ?>"><i class="bi bi-graph-up"></i> Reportes de Stock</a></li>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Búsqueda -->
        <div class="d-flex search-container me-3 flex-grow-1 position-relative">
          <input class="form-control form-control-sm" type="search" id="searchMaterial" placeholder="Buscar material..." aria-label="Search" autocomplete="off">
          <button class="btn btn-outline-light btn-sm ms-2 flex-shrink-0" type="button" id="btnLimpiarBusqueda">
            <i class="bi bi-x-circle"></i>
            <span class="ms-1 d-none d-md-inline">Limpiar</span>
          </button>
          <!-- Sugerencias de autocompletado -->
          <div id="suggestionsList" class="position-absolute bg-white border rounded shadow-sm" style="top: 100%; left: 0; right: 40px; z-index: 1050; max-height: 300px; overflow-y: auto; display: none;"></div>
        </div>

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
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle p-2 rounded user-container position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
              <?php if ($haySolicitudesPendientes): ?>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 12px; height: 12px;" title="Hay solicitudes pendientes">
                  <span class="visually-hidden">Solicitudes pendientes</span>
                </span>
              <?php endif; ?>
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
              <?php if (count(array_intersect(['Administrador', 'Supervisor'], $_SESSION['rol_asignado'])) > 0): ?>
                <li>
                  <a class="dropdown-item d-flex justify-content-between align-items-center position-relative" href="<?php echo url('TRANSACCIONAL/CATALOGO/APROBAR_SOLICITUDES/Solicitud_espera.php'); ?>">
                    <span>
                      <i class="bi bi-clipboard-check me-2"></i>
                      Solicitudes Pendientes
                    </span>
                    <?php if ($haySolicitudesPendientes): ?>
                      <span class="badge bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 11px; margin-left: 8px;" title="Hay solicitudes pendientes">
                        <i class="bi bi-exclamation-lg"></i>
                      </span>
                    <?php endif; ?>
                  </a>
                </li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="<?php echo url('TRANSACCIONAL/CATALOGO/ESTADO_SOLICITUDES/Estado_solicitud.php'); ?>"><i class="bi bi-box-arrow-right"></i>Mis Solicitudes</a></li>
              <li><a class="dropdown-item" href="<?php echo url('logout.php'); ?>"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
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
      <h2 class="text-center mb-4">📦 Solicitud de Materiales</h2>
      
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
                <div class="col-12 col-md-6 col-lg-4 mb-3 material-item" data-material-nombre="<?= strtolower(htmlspecialchars($material['m_nombre'])) ?>" data-material-id="<?= $material['id_material'] ?>">
                  <div class="card material-card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title material-nombre"><?= htmlspecialchars($material['m_nombre']) ?></h5>
                      <p class="card-text">Stock: <?= htmlspecialchars($material['stock_total'] . ' ' . $material['u_medida']) ?></p>
                      <div class="mb-2">
                        <label for="cantidad_<?= $material['id_material'] ?>" class="form-label">Cantidad a egresar:</label>
                        <input type="number" min="1" max="<?= htmlspecialchars($material['stock_total']) ?>" 
                            name="cantidad[<?= $material['id_material'] ?>]" 
                            id="cantidad_<?= $material['id_material'] ?>" 
                            class="form-control cantidad-input" placeholder="Cantidad" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
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
            <h5 class="modal-title" id="modalCarritoLabel">Generar Solicitud</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formSolicitud" method="post" action="Generar_Solicitud.php">
            <div class="modal-body">
              <div class="row mb-3">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                  <label class="form-label fw-bold">Funcionario:</label>
                  <input type="text" class="form-control" name="nombre_usuario" value="<?= htmlspecialchars($nombreUsuario) ?>" readonly>
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
              <div class="row mt-3">
                <div class="col-12 col-md-4 fw-bold d-flex align-items-center justify-content-center justify-content-md-start mb-2 mb-md-0">
                  Comentarios (opcional):
                </div>
                <div class="col-12 col-md-8">
                  <input type="text" class="form-control" name="detalle" id="detalleSolicitud">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-responsive" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary btn-responsive">Enviar Solicitud</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo url('TRANSACCIONAL/CATALOGO/solicitud.js?v=' . rand()); ?>"></script>
</body>
</html>