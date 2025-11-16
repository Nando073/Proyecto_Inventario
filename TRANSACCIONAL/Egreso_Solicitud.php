<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Egreso.php';
require_once '../NEGOCIO/N_Funcionario.php';
require_once '../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

$egresoService = new N_Egreso();
$funcionarioService = new N_Funcionario();
$solicitudService = new N_Solicitud();

// session_start(); //verificar si un usuario se ha iniciado para obtener el id
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "<script>alert('No se pudo identificar al usuario que realiza el egreso.'); window.history.back();</script>";
    exit();
}

// Procesar POST para registrar egreso desde solicitud aprobada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_solicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);
    $id_usuario_post = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if (!$id_solicitud || !$id_usuario_post) {
        $_SESSION['mensaje'] = "Datos de solicitud inválidos.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Egreso_Solicitud.php');
        exit();
    }

    try {
        // Obtener las solicitudes aprobadas para extraer el id_usuario (funcionario)
        $todasLasSolicitudes = $solicitudService->obtenerSolicitudesAprobadas();
        
        // Buscar la solicitud específica en el array
        $solicitudEncontrada = null;
        foreach ($todasLasSolicitudes as $sol) {
            if ($sol['id_solicitud'] == $id_solicitud) {
                $solicitudEncontrada = $sol;
                break;
            }
        }
        
        // DEBUG: Ver qué campos tiene la solicitud
        error_log("DEBUG - Solicitud Encontrada completa: " . print_r($solicitudEncontrada, true));
        
        if (!$solicitudEncontrada) {
            $_SESSION['mensaje'] = "No se encontró la solicitud con ID: " . $id_solicitud;
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Egreso_Solicitud.php');
            exit();
        }
        
        // Obtener id_funcionario (el que solicitó) y cod_solicitud
        $id_funcionario = isset($solicitudEncontrada['id_funcionario']) ? $solicitudEncontrada['id_funcionario'] : null;
        $cod_solicitud = isset($solicitudEncontrada['cod_solicitud']) ? $solicitudEncontrada['cod_solicitud'] : null;
        
        if (!$id_funcionario) {
            $_SESSION['mensaje'] = "No se pudo obtener el ID del funcionario. Campos disponibles: " . implode(', ', array_keys($solicitudEncontrada));
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Egreso_Solicitud.php');
            exit();
        }
        
        if (!$cod_solicitud) {
            $_SESSION['mensaje'] = "No se pudo obtener el código de solicitud. Campos disponibles: " . implode(', ', array_keys($solicitudEncontrada));
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Egreso_Solicitud.php');
            exit();
        }
        
        // Obtener los detalles de la solicitud para construir el egreso
        $detallesSolicitud = $solicitudService->obtenerDetallesSolicitudes($id_solicitud);
        
        if (empty($detallesSolicitud)) {
            $_SESSION['mensaje'] = "No se encontraron detalles para esta solicitud.";
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Egreso_Solicitud.php');
            exit();
        }

        // DEBUG: Ver estructura de datos
        error_log("DEBUG - Solicitud Encontrada: " . print_r($solicitudEncontrada, true));
        error_log("DEBUG - Detalles Solicitud: " . print_r($detallesSolicitud, true));

        // Calcular total y construir detalles
        $e_total_cantidad = 0;
        $detalles = [];

        foreach ($detallesSolicitud as $detalle) {
            $cantidad = isset($detalle['cantidad']) ? intval($detalle['cantidad']) : 0;
            $e_total_cantidad += $cantidad;
            $detalles[] = [
                'id_material_e' => $detalle['id_material'],
                'e_stock' => $cantidad
            ];
        }

        // DEBUG: Ver valores calculados
        error_log("DEBUG - Total Cantidad: " . $e_total_cantidad);
        error_log("DEBUG - ID Funcionario: " . $id_funcionario);
        error_log("DEBUG - ID Solicitud: " . $id_solicitud);
        error_log("DEBUG - Código Solicitud: " . $cod_solicitud);

        // Validar que el total no sea 0
        if ($e_total_cantidad <= 0) {
            $_SESSION['mensaje'] = "Error: La cantidad total debe ser mayor a 0. Total calculado: " . $e_total_cantidad;
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Egreso_Solicitud.php');
            exit();
        }

        // Registrar el egreso completo (egreso + detalles + finalizar solicitud)
        $resultado = $egresoService->registrarEgresoCompleto(
            $id_solicitud,
            $cod_solicitud,
            $id_funcionario,
            $e_total_cantidad,
            $id_usuario_post,
            $detalles
        );
        
        if (isset($resultado['success']) && $resultado['success'] == 1) {
            $_SESSION['mensaje'] = "Egreso registrado correctamente. La solicitud ha sido finalizada.";
            $_SESSION['tipo_mensaje'] = "success";
            header('Location: Egreso.php');
            exit();
        } else {
            $_SESSION['mensaje'] = "No se pudo registrar el egreso.";
            $_SESSION['tipo_mensaje'] = "danger";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al registrar egreso: " . htmlspecialchars($e->getMessage());
        $_SESSION['tipo_mensaje'] = "danger";
    }
    
    header('Location: Egreso_Solicitud.php');
    exit();
}

$solicitudes = $solicitudService->obtenerSolicitudesAprobadas();
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
</head>

<body>
<?php include '../DEMO/index.php'; ?>

<main>
  <h2 class="text-center mb-4 fw-bold text-success display-6">📦 Solicitudes Aprobadas</h2>

  <!-- Mensaje de éxito/error -->
  <?php if (isset($_SESSION['mensaje'])): ?>
      <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['mensaje']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
  <?php endif; ?>

  <?php if (empty($solicitudes)): ?>
      <div class="alert alert-info text-center shadow-sm p-4 rounded-4">
          <i class="bi bi-info-circle"></i> No hay solicitudes aprobadas disponibles.
      </div>
  <?php else: ?>
      <?php 
      // Agrupar materiales por solicitud
      $agrupadas = [];
      foreach ($solicitudes as $s) {
          $agrupadas[$s['id_solicitud']]['info'] = $s;
          $agrupadas[$s['id_solicitud']]['materiales'][] = [
              'nombre' => $s['nombre_material'],
              'categoria' => $s['categoria'],
              'unidad' => $s['unidad_medida'],
              'cantidad' => $s['cantidad']
          ];
      }
      ?>

      <div class="d-flex flex-column gap-4">
          <?php foreach ($agrupadas as $id_solicitud => $data): 
              $info = $data['info'];
          ?>
          <form method="POST" action="Egreso_Solicitud.php" 
                class="card solicitud-card border-0 shadow-sm rounded-4 overflow-hidden">

              <div class="card-header bg-primary text-white p-3">
                  <div class="d-flex justify-content-between align-items-center flex-wrap">
                      <div>
                          <h5 class="mb-0 fw-bold">Solicitud Aprobada</h5>
                          <small class="text-white-50">
                              <i class="bi bi-calendar3"></i> <?php echo $info['fecha_solicitud']; ?>
                          </small>
                      </div>
                      <button type="submit" class="btn btn-sm px-4 rounded-pill fw-semibold text-white" style="background-color: #759A7B;">
                          <i class="bi bi-box-arrow-down"></i> Registrar Egreso
                      </button>
                  </div>
              </div>

              <div class="card-body p-4">
                  <div class="row">
                      <div class="col-md-6 mb-2">
                          <p class="mb-1"><strong>Solicitante:</strong> 
                              <span class="text-secondary"><?php echo $info['solicitante']; ?></span>
                          </p>
                      </div>
                      <div class="col-md-6 mb-2">
                          <p class="mb-1"><strong>Aprobado por:</strong> 
                              <span class="text-secondary"><?php echo $info['aprobador']; ?></span>
                          </p>
                      </div>
                      <div class="col-md-6 mb-2">
                          <p class="mb-1"><strong>Codigo Solicitud:</strong> 
                              <span class="text-secondary"><?php echo $info['cod_solicitud']; ?></span>
                          </p>
                      </div>
                      <div class="col-md-6 mb-2">
                          <p class="mb-0"><strong>Detalle:</strong> 
                              <span class="text-muted fst-italic"><?php echo htmlspecialchars($info['detalle_solicitud']); ?></span>
                          </p>
                      </div>
                  </div>

                  <div class="table-responsive mt-3">
                      <table class="table table-hover align-middle mb-0">
                          <thead class="table-light">
                              <tr>
                                  <th>Material</th>
                                  <th>Categoría</th>
                                  <th>Unidad</th>
                                  <th class="text-center">Cantidad</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php 
                              $total_cantidad = 0;
                              foreach ($data['materiales'] as $m): 
                                  $total_cantidad += $m['cantidad'];
                              ?>
                                  <tr>
                                      <td class="fw-semibold"><?php echo $m['nombre']; ?></td>
                                      <td><?php echo $m['categoria']; ?></td>
                                      <td><?php echo $m['unidad']; ?></td>
                                      <td class="text-center text-success fw-bold"><?php echo $m['cantidad']; ?></td>
                                  </tr>
                              <?php endforeach; ?>
                          </tbody>
                          <tfoot class="table-light">
                              <tr>
                                  <th colspan="3" class="text-end">Total Cantidad:</th>
                                  <th class="text-center text-primary fw-bold"><?php echo $total_cantidad; ?></th>
                              </tr>
                          </tfoot>
                      </table>
                  </div>

                  <!-- Campos ocultos -->
                  <input type="hidden" name="id_solicitud" value="<?php echo $id_solicitud; ?>">
                  <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
              </div>
          </form>
          <?php endforeach; ?>
      </div>
  <?php endif; ?>
</main>

<!-- Iconos Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- Estilos personalizados -->
<style>
  body {
      background-color: #f8f9fa;
  }
  .btn:hover {
      background-color: #759A7B;
      color: white;
      transform: scale(1.02);
  }
  .solicitud-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .solicitud-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  }

  .solicitud-card .card-header {
      background: linear-gradient(90deg, #131F31);
  }

  @media (max-width: 768px) {
      .solicitud-card .card-header h5 {
          font-size: 1rem;
      }

      .solicitud-card .btn {
          width: 100%;
          margin-top: 10px;
      }

      .solicitud-card .card-body {
          padding: 1rem !important;
      }

      .table th, .table td {
          font-size: 0.85rem;
      }
  }
</style>
</body>
</html>
