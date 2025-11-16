<?php
require_once '../../NEGOCIO/N_Egreso.php';

// Crear instancia del servicio
$egresoService = new N_Egreso();

// Obtener todos los egresos
$detalles = $egresoService->ObtenerDetallesEgresos();

// Encabezados para forzar descarga como Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=historial_egresos.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Tabla HTML para Excel
echo "<table border='1'>";
echo "<thead>";
echo "<tr>";
echo "<th>Fecha</th>";
echo "<th>Área</th>";
echo "<th>Funcionario</th>";
echo "<th>Código Solicitud</th>";
echo "<th>Material</th>";
echo "<th>Categoría</th>";
echo "<th>Cantidad</th>";
echo "<th>Unidad</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($detalles as $detalle) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($detalle['e_fecha']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['a_nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['funcionario_solicitante']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['e_solicitud']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['material_nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['categoria_nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['e_stock']) . "</td>";
    echo "<td>" . htmlspecialchars($detalle['u_medida']) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
exit;
?>
