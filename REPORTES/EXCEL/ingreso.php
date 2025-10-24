<?php
require_once '../../NEGOCIO/N_Ingreso.php';

// Crear instancia del servicio
$ingresoService = new N_Ingreso();

// Obtener todos los ingresos (o puedes usar la misma búsqueda si envías GET)
$detalles = $ingresoService->ObtenerDetallesIngresos();

// Encabezados para forzar descarga como Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=historial_ingresos.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Generar tabla HTML que Excel podrá leer
echo "<table border='1'>";
echo "<thead>";
echo "<tr>";
echo "<th>Fecha</th>";
echo "<th>Proveedor</th>";
echo "<th>Material</th>";
echo "<th>Precio Unitario</th>";
echo "<th>Cantidad</th>";
echo "<th>Unidad</th>";
echo "<th>Sub Total</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($detalles as $detalle) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($detalle['i_fecha']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['proveedor_nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['material_nombre']) . "</td>";
    echo "<td>" . number_format($detalle['precio'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['cantidad']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['u_medida']) . "</td>";
    echo "<td>" . number_format($detalle['sub_total'], 2) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
exit;
?>
