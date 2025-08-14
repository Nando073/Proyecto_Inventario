<?php
// filepath: c:\xampp\htdocs\DDE_INVENTARIO\TRANSACCIONAL\detalle_ingreso_ajax.php
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
<h5>ID Ingreso: <?php echo htmlspecialchars($ingreso['id_ingreso']); ?></h5>
<h5>Fecha: <?php echo htmlspecialchars($ingreso['i_fecha']); ?></h5>
<h5>Proveedor: <?php echo htmlspecialchars($ingreso['proveedor_nombre']); ?></h5>

<table class="table table-bordered mt-2">
    <thead>
        <tr>
            <th>ID Detalle</th>
            <th>Material</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Sub Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalIngreso = 0; ?>
        <?php foreach ($ingresoDetalles as $detalle): 
            $totalIngreso += $detalle['sub_total'];
        ?>
        <tr>
            <td><?php echo htmlspecialchars($detalle['id_d_ingreso']); ?></td>
            <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
            <td><?php echo htmlspecialchars($detalle['precio']); ?> Bs.</td>
            <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
            <td><?php echo htmlspecialchars($detalle['sub_total']); ?> Bs.</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4">Total del Ingreso</th>
            <th><?php echo number_format($totalIngreso, 2); ?> Bs.</th>
        </tr>
    </tfoot>
</table>