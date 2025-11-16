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
<?php include __DIR__ . '/../Cabecera.php';?>
<body class="container bg-light">
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
                    <td><?php echo str_pad($sol['cod_solicitud'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($sol['funcionario']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($sol['fecha_cambio'])); ?></td>
                    <td>
                        <?php 
                        switch($sol['estado']) {
                            case 1: echo '<span class="badge bg-warning text-dark">Pendiente</span>'; break;
                            case 2: echo '<span class="badge bg-success">Aprobada</span>'; break;
                            case 3: echo '<span class="badge bg-danger">Rechazada</span>'; break;
                            case 4: echo '<span class="badge bg-info">Finalizado</span>'; break;
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