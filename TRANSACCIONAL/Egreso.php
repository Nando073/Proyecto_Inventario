<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Egreso.php';
require_once '../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

$egresoService = new N_Egreso();
$solicitudService = new N_Solicitud();
//$detalleService = new N_Egreso();

//session_start(); //verificar si un usuario se a inisiado para obtenre el id
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo "<script>alert('No se pudo identificar al usuario que realiza el egreso.'); window.history.back();</script>";
    exit();
}

// Obtener el conteo de solicitudes aprobadas pendientes de egreso
$conteoSolicitudesAprobadas = 0;
try {
    $solicitudesAprobadas = $solicitudService->obtenerSolicitudesAprobadas();
    // Agrupar por id_solicitud para contar solicitudes únicas
    $solicitudesUnicas = [];
    foreach ($solicitudesAprobadas as $s) {
        $solicitudesUnicas[$s['id_solicitud']] = true;
    }
    $conteoSolicitudesAprobadas = count($solicitudesUnicas);
} catch (Exception $e) {
    $conteoSolicitudesAprobadas = 0;
}

// Buscar egresos
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) : '';
if ($searchTerm) {
    $egresos = $egresoService->buscarPorSimilitud($searchTerm);
} else {
    $egresos = $egresoService->ObtenerEgresosRegistrado();
}
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
    <h3 class="mt-5">EGRESAR MATERIALES</h3>
    
    <!-- Formulario de búsqueda responsivo -->
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="Egreso.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre, ID o fecha" 
                   value="<?php echo htmlspecialchars($searchTerm); ?>" 
                   class="form-control me-2">
            <button type="submit" class="btn btn-info flex-shrink-0 btn-responsive">Buscar</button>
        </div>

        <!-- <button type="button" class="btn btn-success btn-responsive" id="btnCrearEgreso" data-bs-toggle="modal" data-bs-target="#egresoModal">
            Registrar Egreso
        </button> -->
        <a href="Egreso_Solicitud.php" class="position-relative text-decoration-none">
            <button type="button" class="btn btn-success btn-responsive position-relative">
                <i class="bi bi-box-arrow-down me-1"></i>
                Egresar Solicitudes
                <?php if ($conteoSolicitudesAprobadas > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem;">
                        <?php echo $conteoSolicitudesAprobadas; ?>
                        <span class="visually-hidden">solicitudes pendientes</span>
                    </span>
                <?php endif; ?>
            </button>
        </a>
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
                    <th>Funcionario</th>
                    <th>Código Solicitud</th>
                    <th>Total Cantidad</th>
                    <th>Fecha de egreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($egresos)): ?>
                    <?php foreach ($egresos as $egreso): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($egreso['funcionario_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($egreso['e_solicitud']); ?></td>
                            <td><?php echo htmlspecialchars($egreso['e_total_cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($egreso['e_fecha']); ?></td>
                            <td>
                                <a href="#" class="btn btn-info btn-sm btn-ver-egreso btn-responsive " data-id="<?php echo $egreso['id_egreso']; ?>">Ver</a>
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
    </div>
</main>

<!-- Modal Detalle Egreso -->
<div class="modal fade" id="detalleEgresoModal" tabindex="-1" aria-labelledby="detalleEgresoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleEgresoLabel">Detalle de Egreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="detalleEgresoBody">
        <div class="text-center">
          <span class="spinner-border"></span> Cargando...
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function imprimirModal(id) {
    var contenido = document.getElementById(id).innerHTML;
    
    var ventana = window.open('', '', 'width=900,height=700');
    ventana.document.write('<html><head><title>Detalle de Egreso</title>');
    ventana.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">');
    ventana.document.write('<style>');
    ventana.document.write('@media print {');
    ventana.document.write('  body { margin: 20px; font-family: Arial, sans-serif; }');
    ventana.document.write('  .btn-print { display: none !important; }');
    ventana.document.write('}');
    ventana.document.write('body { padding: 30px; background: white; }');
    ventana.document.write('h2 { color: #2c3e50; text-align: center; margin-bottom: 30px; font-weight: bold; }');
    ventana.document.write('.info-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #dee2e6; }');
    ventana.document.write('.info-row { display: flex; justify-content: space-between; margin-bottom: 15px; }');
    ventana.document.write('.info-item { flex: 1; }');
    ventana.document.write('.info-item strong { color: #495057; display: block; margin-bottom: 5px; font-size: 12px; text-transform: uppercase; }');
    ventana.document.write('.info-item p { color: #212529; margin: 0; font-size: 14px; font-weight: 500; }');
    ventana.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }');
    ventana.document.write('thead { background: #343a40; color: white; }');
    ventana.document.write('th { padding: 12px; text-align: left; font-weight: 600; }');
    ventana.document.write('td { padding: 10px; border-bottom: 1px solid #dee2e6; }');
    ventana.document.write('tbody tr:hover { background: #f8f9fa; }');
    ventana.document.write('tfoot { background: #e9ecef; font-weight: bold; }');
    ventana.document.write('tfoot th { padding: 12px; color: #212529; }');
    ventana.document.write('.btn-print { margin-top: 20px; }');
    ventana.document.write('</style>');
    ventana.document.write('</head><body>');
    ventana.document.write('<h2> DETALLE DE EGRESO </h2>');
    ventana.document.write(contenido);
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.focus();
    
    setTimeout(function() {
        ventana.print();
        ventana.close();
    }, 250);
}
</script>
<script>
    // --- 🔹 Modal detalle egreso ---
    document.querySelectorAll('.btn-ver-egreso').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const idEgreso = this.getAttribute('data-id');
            const modalBody = document.getElementById('detalleEgresoBody');
            modalBody.innerHTML = "<div class='text-center'><span class='spinner-border'></span> Cargando...</div>";
            const modal = new bootstrap.Modal(document.getElementById('detalleEgresoModal'));
            modal.show();

            fetch('detalle_egreso.php?id=' + idEgreso)
                .then(response => response.text())
                .then(html => modalBody.innerHTML = html)
                .catch(() => modalBody.innerHTML = "<div class='alert alert-danger'>Error al cargar el detalle.</div>");
        });
    });
</script>
</body>
</html>