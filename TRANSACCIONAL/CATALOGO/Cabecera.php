<?php
include '../../../Seguridad.php';

// Detectar si es móvil (simple)
$isMobile = false;
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $isMobile = strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('TRANSACCIONAL/CATALOGO/styles.css?v=' . rand()); ?>">
    <link rel="stylesheet" href="<?php echo url('TRANSACCIONAL/CATALOGO/solicitud.css?v=' . rand()); ?>">   
</head>
<body class="bg-light">
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

        <!-- Carrito y usuario -->
        <div class="d-flex align-items-center flex-shrink-0 ms-auto">
          <!-- Carrito -->
          <a href="../Generar_solicitud.php" class="btn cart-btn me-3 position-relative btn-sm">
            <i class="bi bi-cart"></i> 
            <span class="d-md-inline ms-1">Generar Solicitud</span>
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
              <?php if (count(array_intersect(['Administrador', 'Supervisor'], $_SESSION['rol_asignado'])) > 0): ?>
                <li>
                  <a class="dropdown-item" href="<?php echo url('TRANSACCIONAL/CATALOGO/APROBAR_SOLICITUDES/Solicitud_espera.php'); ?>">
                    <i class="bi bi-clipboard-check"></i> Solicitudes Pendientes
                  </a>
                </li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="../ESTADO_SOLICITUDES/Estado_solicitud.php"><i class="bi bi-box-arrow-right"></i>Mis Solicitudes</a></li>
              <li><a class="dropdown-item" href="../../../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
</body>
</html>
