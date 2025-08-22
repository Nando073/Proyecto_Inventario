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
<h5>ID Egreso: <?php echo htmlspecialchars($egreso['id_egreso']); ?></h5>
<h5>Fecha: <?php echo htmlspecialchars($egreso['e_fecha']); ?></h5>
<h5>Funcionario: <?php echo htmlspecialchars($egreso['funcionario_nombre']); ?></h5>
<h5>Código Solicitud: <?php echo htmlspecialchars($egreso['e_solicitud']); ?></h5>

<table class="table table-bordered mt-2">
    <thead>
        <tr>
            <th>ID Detalle</th>
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
            <td><?php echo htmlspecialchars($detalle['id_e_detalle']); ?></td>
            <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
            <td><?php echo htmlspecialchars($detalle['categoria_nombre']); ?></td>
            <td><?php echo htmlspecialchars($detalle['e_stock']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">Total del Egreso</th>
            <th><?php echo number_format($totalEgreso, 2); ?></th>
        </tr>
    </tfoot>
</table>