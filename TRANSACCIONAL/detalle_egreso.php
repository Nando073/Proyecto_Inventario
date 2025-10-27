<?php
// filepath: c:\xampp\htdocs\DDE_INVENTARIO\TRANSACCIONAL\detalle_egreso_ajax.php
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Egreso.php';

$id_egreso = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_egreso <= 0) {
    echo "<div class='alert alert-danger'>ID de egreso no válido.</div>";
    exit;
}

$egresoService = new N_Egreso();
$detalles = $egresoService->ObtenerDetallesEgresos();

$egresoDetalles = [];
foreach ($detalles as $detalle) {
    if ($detalle['id_egreso'] == $id_egreso) {
        $egresoDetalles[] = $detalle;
    }
}

if (empty($egresoDetalles)) {
    echo "<div class='alert alert-warning'>No se encontraron detalles para este egreso.</div>";
    exit;
}

// Usamos el primer detalle para los datos generales
$egreso = $egresoDetalles[0];
?>
<div id="contenido-modal" class="p-4">
    <!-- Información general -->
    <div class="row mb-3">
        <div class="col-md-4">
            <strong>Fecha:</strong>
            <p class="mb-0"><?php echo htmlspecialchars($egreso['e_fecha']); ?></p>
        </div>
        <div class="col-md-4">
            <strong>Registrado por:</strong>
            <p class="mb-0"><?php echo htmlspecialchars($egreso['usuario_registro']); ?></p>
        </div>
        <div class="col-md-4">
            <strong>Funcionario Solicitante:</strong>
            <p class="mb-0"><?php echo htmlspecialchars($egreso['funcionario_nombre']); ?></p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <strong>Área:</strong>
            <p class="mb-0"><?php echo htmlspecialchars($egreso['a_nombre']); ?></p>
        </div>
        <div class="col-md-4">
            <strong>Código Solicitud:</strong>
            <p class="mb-0"><?php echo htmlspecialchars($egreso['e_solicitud']); ?></p>
        </div>
    </div>

    <!-- Tabla de materiales -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Material</th>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalEgreso = 0; ?>
                <?php foreach ($egresoDetalles as $detalle): 
                    $totalEgreso += $detalle['e_stock'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($detalle['categoria_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($detalle['e_stock'] . " - " . $detalle['u_medida']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <th colspan="2" class="text-end">Total del Egreso</th>
                    <th><?php echo number_format($totalEgreso, 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Botón de imprimir -->
    <div class="mt-4">
        <button class="btn btn-success btn-print" onclick="imprimirModal('contenido-modal')">
            <i class="fas fa-print me-2"></i> Imprimir
        </button>
    </div>
</div>
