<?php
require_once '../../NEGOCIO/N_Egreso.php';
$egresoService = new N_Egreso();

// Obtener los datos igual que en stock.php
$stockPorLote = $egresoService->obtenerStockPorLote();

// Configurar cabeceras para Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_stock_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Encabezado del archivo
echo "<table border='1'>";
echo "<tr style='background-color:#D9EAD3; font-weight:bold;'>
        <th>Material</th>
        <th>Proveedor</th>
        <th>Fecha Ingreso</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Total (Bs)</th>
      </tr>";

// Rellenar filas
foreach ($stockPorLote as $fila) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($fila['m_nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($fila['proveedor_nombre']) . "</td>";
    echo "<td>" . htmlspecialchars($fila['i_fecha']) . "</td>";
    echo "<td>" . htmlspecialchars($fila['stock_restante'] . ' ' . $fila['u_medida']) . "</td>";
    echo "<td>" . htmlspecialchars($fila['precio']) . "</td>";
    echo "<td>" . htmlspecialchars(number_format($fila['stock_restante'] * $fila['precio'], 2)) . "</td>";
    echo "</tr>";
}
echo "</table>";
exit;
?>
