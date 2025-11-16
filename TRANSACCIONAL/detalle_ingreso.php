<?php
// filepath: c:\xampp\htdocs\DDE_INVENTARIO\TRANSACCIONAL\detalle_ingreso_ajax.php
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Ingreso.php';

$id_ingreso = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_ingreso <= 0) {
    echo "<div class='alert alert-danger'>ID de ingreso no válido.</div>";
    exit;
}

$ingresoService = new N_Ingreso();
$detalles = $ingresoService->ObtenerDetallesIngresos();

$ingresoDetalles = [];
foreach ($detalles as $detalle) {
    if ($detalle['id_ingreso'] == $id_ingreso) {
        $ingresoDetalles[] = $detalle;
    }
}

if (empty($ingresoDetalles)) {
    echo "<div class='alert alert-warning'>No se encontraron detalles para este ingreso.</div>";
    exit;
}

$ingreso = $ingresoDetalles[0];
?>
<div id="contenido-modal" class="p-4">
    <!-- Información general -->
    <div class="info-section">
        <div class="row mb-3">
            <div class="col-md-4 info-item">
                <strong>Fecha:</strong>
                <p class="mb-0"><?php echo htmlspecialchars($ingreso['i_fecha']); ?></p>
            </div>
            <div class="col-md-4 info-item">
                <strong>Funcionario:</strong>
                <p class="mb-0"><?php echo htmlspecialchars($ingreso['funcionario_nombre']); ?></p>
            </div>
            <div class="col-md-4 info-item">
                <strong>Proveedor:</strong>
                <p class="mb-0"><?php echo htmlspecialchars($ingreso['proveedor_nombre']); ?></p>
            </div>
        </div>
    </div>

    <!-- Tabla de materiales -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Material</th>
                    <th>Precio (Bs.)</th>
                    <th>Cantidad</th>
                    <th>Sub Total (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalIngreso = 0; ?>
                <?php foreach ($ingresoDetalles as $detalle): 
                    $totalIngreso += $detalle['sub_total'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($detalle['precio'],2)); ?></td>
                    <td><?php echo htmlspecialchars($detalle['cantidad'] . " - " . $detalle['u_medida']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($detalle['sub_total'],2)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <th colspan="3" class="text-end">Total del Ingreso</th>
                    <th><?php echo number_format($totalIngreso, 2); ?> Bs.</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Botón de imprimir -->
    <div class="mt-4 text-center">
        <button class="btn btn-success btn-print" onclick="imprimirModal('contenido-modal')">
            <i class="fas fa-print me-2"></i> 🖨️ Imprimir Reporte
        </button>
    </div>
</div>
