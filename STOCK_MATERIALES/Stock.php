<?php
require_once '../NEGOCIO/N_Egreso.php';
$egresoService = new N_Egreso();
$stockPorLote = $egresoService->obtenerStockPorLote(); // Debe llamar a tu nuevo procedimiento

// Procesar filtros (igual que antes)
$busqueda = $_GET['busqueda'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$errores = [];
$fecha_min = '2020-01-01';
$fecha_max = date('Y-m-d');

if ($fecha_inicio && $fecha_inicio < $fecha_min) $errores[] = "La fecha de inicio no puede ser menor a $fecha_min";
if ($fecha_fin && $fecha_fin > $fecha_max) $errores[] = "La fecha de fin no puede ser mayor a hoy";
if ($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin) $errores[] = "La fecha de inicio no puede ser mayor que la de fin";

// Agrupar por material
$agrupado = [];
foreach ($stockPorLote as $fila) {
    $material = $fila['m_nombre'];
    if (!isset($agrupado[$material])) {
        $agrupado[$material] = [
            'subfilas' => [],
            'stock_total' => 0,
            'total_bs' => 0
        ];
    }
    $agrupado[$material]['subfilas'][] = $fila;
    $agrupado[$material]['stock_total'] += $fila['stock_restante'];
    $agrupado[$material]['total_bs'] += $fila['stock_restante'] * $fila['precio'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Stock de Materiales</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; }
        th { background: #f0f0f0; }
        .material-row { background: #e0eaff; font-weight: bold; }
        .btn { padding: 6px 14px; margin: 4px; border-radius: 4px; border: none; background: #007bff; color: #fff; cursor: pointer; }
        .btn-exit { background: #dc3545; }
        .btn-print { background: #28a745; }
        .buscador { margin-bottom: 18px; }
        .errores { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Stock de Materiales</h2>
    <div class="buscador">
        <form method="get">
            <input type="text" name="busqueda" placeholder="Buscar material, proveedor..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <label>Desde: <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>"></label>
            <label>Hasta: <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>"></label>
            <button type="submit" class="btn">Buscar</button>
            <button type="button" class="btn btn-exit" onclick="window.location.href='../PRESENTACION/ADM_Material.php'">Salir</button>
            <button type="button" class="btn btn-print" onclick="window.print()">Imprimir</button>
        </form>
        <?php if ($errores): ?>
            <div class="errores">
                <?php foreach ($errores as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>
    </div>
    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th>Proveedor</th>
                <th>Fecha Ingreso</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
                <th>Stock Actual / Total Bs</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agrupado as $material => $datos): ?>
                <tr class="material-row">
                    <td rowspan="<?php echo count($datos['subfilas']) + 1; ?>" style="background:#e0eaff; font-weight:bold; text-align:center; vertical-align:middle;">
                        <?php echo htmlspecialchars($material); ?>
                    </td>
                    <td colspan="5"></td>
                    <td rowspan="<?php echo count($datos['subfilas']) + 1; ?>" style="background:#e0eaff; font-weight:bold; text-align:center; vertical-align:middle;">
                        <?php echo htmlspecialchars($datos['stock_total']); ?> <br>
                        <span style="font-size:12px; color:#007bff;">(Total Bs: <?php echo number_format($datos['total_bs'], 2); ?>)</span>
                    </td>
                </tr>
                <?php foreach ($datos['subfilas'] as $sub): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sub['proveedor_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($sub['i_fecha']); ?></td>
                        <td><?php echo htmlspecialchars($sub['stock_restante'] . ' - ' . $sub['u_medida']); ?></td>
                        <td><?php echo htmlspecialchars($sub['precio'] . ' Bs'); ?></td>
                        <td><?php echo htmlspecialchars($sub['stock_restante'] * $sub['precio'] . ' Bs'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>