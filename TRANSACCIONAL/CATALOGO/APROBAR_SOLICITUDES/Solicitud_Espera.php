<?php
require_once __DIR__ . '/../../../Seguridad.php';
verificarAcceso(['Administrador','Supervisor','Supervisor']);
require_once __DIR__ . '/../../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

// Instancia
$solicitudService = new N_Solicitud();

// Obtener todas las solicitudes (solo cabeceras, sin duplicados)
$solicitudes = $solicitudService->obtenerSolicitudesCabecera();

// header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSolicitud = isset($_POST['id_solicitud']) ? intval($_POST['id_solicitud']) : 0;
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
    
    if ($idSolicitud <= 0 || !in_array($estado, [2, 3])) {
        echo json_encode([
            'success' => false,
            'message' => 'Parámetros inválidos'
        ]);
        exit;
    }
    
    $nSolicitud = new N_Solicitud();
    $resultado = $nSolicitud->cambiarEstadoSolicitud($idSolicitud, $estado, $comentario);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el estado'
        ]);
    }
// } else {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Método no permitido'
//     ]);
// }
    exit;
}
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
     <style>
        :root {
            --bs-success-lighter: #5096e6ff;
            --bs-custom-teal: #41b488ff; 
        }
        .bg-success-lighter {
            background-color: var(--bs-success-lighter) !important;
        }
        
        .bg-custom-teal {
            background-color: var(--bs-custom-teal) !important;
        }
      </style>
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
          <a href="../Generar_solicitud.php" class="btn cart-btn me-3 position-relative btn-sm">
            <i class="bi bi-cart"></i> 
            <span class="d-none d-md-inline ms-1">Generar Solicitud</span>
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
              <li><a class="dropdown-item" href="../ESTADO_SOLICITUDES/Estado_solicitud.php"><i class="bi bi-box-arrow-right"></i> Estado de Solicitud</a></li>
              <li><a class="dropdown-item" href="../../../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>


<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check"></i> Estado de Solicitudes</h2>
        <span class="badge bg-primary fs-6">Total: <?php echo count($solicitudes); ?> Pendientes</span>
    </div>
    
    <div class="row g-4">
        <?php if (empty($solicitudes)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-3"></i>
                    <p class="mb-0 mt-2">No hay solicitudes registradas</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($solicitudes as $sol): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-solicitud shadow-sm h-100">
                    <div class="card-header bg-custom-teal text-white text-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i>
                            Solicitud #<?php echo str_pad($sol['id_solicitud'], 4, '0', STR_PAD_LEFT); ?>
                        </h5>
                    </div>
                    <div class="card-body mx-2">
                        <p><strong><i class="bi bi-person"></i> Funcionario:</strong><br>
                           <?php echo htmlspecialchars($sol['funcionario']); ?></p>
                        
                        <p><strong><i class="bi bi-calendar"></i> Fecha:</strong><br>
                           <?php echo date('d/m/Y H:i', strtotime($sol['s_fecha'])); ?></p>

                        <p>
                          <strong><i class="bi bi-check-circle"></i> Estado:</strong><br>
                          <p class="text-primary"><?php 
                            echo ($sol['estado'] == 1) ? 'Pendiente' : htmlspecialchars($sol['estado']);
                          ?>
                          </p>
                        </p>

                        <?php if (!empty($sol['comentario'])): ?>
                        <p><strong><i class="bi bi-chat-left-text"></i> Comentario:</strong><br>
                           <?php echo htmlspecialchars(substr($sol['comentario'], 0, 50)); ?>
                           <?php echo strlen($sol['comentario']) > 50 ? '...' : ''; ?></p>
                        <?php endif; ?>
                        
                        <button class="btn bg-success-lighter w-100 ver-detalle" data-id="<?php echo $sol['id_solicitud']; ?>">
                            <i class="bi bi-eye text-white">Ver Detalle </i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-custom-teal text-white">
        <h5 class="modal-title">Detalle de Solicitud</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
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
            
            // Mostrar spinner
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            `;
            
            // Abrir modal
            let modal = new bootstrap.Modal(document.getElementById('detalleModal'));
            modal.show();

            // Cargar contenido
            fetch('detalle_espera.php?id=' + id)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta');
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                        <div class='alert alert-danger'>
                            <i class='bi bi-exclamation-triangle'></i> 
                            Error al cargar el detalle. Por favor, intente nuevamente.
                        </div>
                    `;
                });
        });
    });
});
</script>
</body>
</html>