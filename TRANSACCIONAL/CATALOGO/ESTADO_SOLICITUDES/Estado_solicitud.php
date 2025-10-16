<?php
require_once __DIR__ . '/../../../Seguridad.php';
verificarAcceso(['Administrador','Supervisor','Supervisor','Funcionario','Operador']);
require_once __DIR__ . '/../../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

$idUsuario = $_SESSION['id_usuario']; // O el campo que identifica al usuario logueado
$solicitudService = new N_Solicitud();
$solicitudes = $solicitudService->obtenerSolicitudesPorUsuario($idUsuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes</title>
    <!-- Bootstrap CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../solicitud.css?v=<?php echo(rand()); ?>">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
</head>
<?php /* HEADER NO SE MODIFICA */ ?>
<header>
  <nav class="navbar fixed-top shadow" style="background-color: #41b488ff;">
    <div class="container-fluid">
      <div class="d-flex align-items-center w-100">
        
        <!-- Logo -->
        <a class="navbar-brand me-3 flex-shrink-0" href="#">
          <img src="../../../IMG/LOGODDE.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-top">
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
                <li><a class="dropdown-item" href="../../../PRESENTACION/ADM_Usuario.php"><i class="bi bi-people"></i> Administrar Usuarios</a></li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="../../../PRESENTACION/ADM_Material.php"><i class="bi bi-box-seam"></i> Administrar Materiales</a></li>
              <li><a class="dropdown-item" href="../../../TRANSACCIONAL/Ingreso.php"><i class="bi bi-download"></i> Ingreso de Materiales</a></li>
              <li><a class="dropdown-item" href="../../../REPORTES/Stock.php"><i class="bi bi-graph-up"></i> Reportes de Stock</a></li>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Búsqueda -->
        <!-- <form class="d-flex search-container me-3 flex-grow-1" role="search">
          <input class="form-control form-control-sm" type="search" placeholder="Buscar material..." aria-label="Search">
          <button class="btn btn-outline-light btn-sm ms-2 flex-shrink-0" type="submit">
            <i class="bi bi-search"></i>
            <span class="ms-1 d-none d-md-inline">Buscar</span>
          </button>
        </form> -->

        <!-- Carrito y usuario -->
        <div class="d-flex align-items-center flex-shrink-0 ms-auto">
          <!-- Carrito -->
          <a href="../Generar_solicitud.php" class="btn cart-btn me-3 position-relative btn-sm">
            <i class="bi bi-cart"></i> 
            <span class="ms-2">Generar Solicitud</span>
          </a>


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
              <li><a class="dropdown-item" href="../../../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
<body>
    <div class="container py-4">
    <h2><i class="bi bi-clipboard-check"></i> Mis Solicitudes</h2>
    
    <div class="table-responsive">
    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th># Solicitud</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Comentario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($solicitudes)): ?>
                <tr>
                    <td colspan="5" class="text-center">No tienes solicitudes registradas</td>
                </tr>
            <?php else: ?>
                <?php foreach ($solicitudes as $sol): ?>
                <tr>
                    <td><?php echo str_pad($sol['id_solicitud'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($sol['funcionario']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($sol['fecha_cambio'])); ?></td>
                    <td>
                        <?php 
                        switch($sol['estado']) {
                            case 1: echo '<span class="badge bg-warning text-dark">Pendiente</span>'; break;
                            case 2: echo '<span class="badge bg-success">Aprobada</span>'; break;
                            case 3: echo '<span class="badge bg-danger">Rechazada</span>'; break;
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($sol['comentario']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary ver-detalle" data-id="<?php echo $sol['id_solicitud']; ?>">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal de detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-custom-teal text-dark">
        <h5 class="modal-title">Detalle de Solicitud</h5>
        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ver-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const modalBody = document.querySelector('#detalleModal .modal-body');
            
            modalBody.innerHTML = `<div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
            </div>`;
            
            let modal = new bootstrap.Modal(document.getElementById('detalleModal'));
            modal.show();

            // Cargar detalles via AJAX
            fetch('detalle_estado.php?id=' + id)
                .then(response => response.text())
                .then(html => { modalBody.innerHTML = html; })
                .catch(() => {
                    modalBody.innerHTML = `<div class="alert alert-danger text-center">
                        Error al cargar el detalle.
                    </div>`;
                });
        });
    });
});
</script>
</body>
</html>