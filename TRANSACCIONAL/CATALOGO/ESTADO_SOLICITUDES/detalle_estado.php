<?php
require_once __DIR__ . '/../../../Seguridad.php';
verificarAcceso(['Administrador','Supervisor','Supervisor','Funcionario','Operador']);
require_once __DIR__ . '/../../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

$id_solicitud = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_solicitud <= 0) {
    echo "<div class='alert alert-danger text-center'>ID de solicitud no válido.</div>";
    exit;
}

$solicitudService = new N_Solicitud();
$detalles = $solicitudService->obtenerDetallesSolicitudes($id_solicitud);

if (empty($detalles)) {
    echo "<div class='alert alert-info text-center'>No hay detalles para esta solicitud.</div>";
    exit;
}
?>

<!-- Tabla simple de detalles -->
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Categoría</th>
                <th>Material</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $i => $d): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($d['categoria']); ?></td>
                <td><?php echo htmlspecialchars($d['material']); ?></td>
                <td><?php echo htmlspecialchars($d['cantidad'] . ' ' . $d['medida']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
