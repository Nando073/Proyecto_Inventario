<?php
require_once '../NEGOCIO/N_Egreso.php';
$egresoService = new N_Egreso();
$stockPorLote = $egresoService->obtenerStockPorLote(); // Debe llamar a tu nuevo procedimiento

$material = $_GET['material'] ?? null;
$proveedor = $_GET['proveedor'] ?? null;
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;

if ($material || $proveedor || $fecha_inicio || $fecha_fin) {
    $stockPorLote = $egresoService->buscarStockPorLote(
        $material ?: null,
        $proveedor ?: null,
        $fecha_inicio ?: null,
        $fecha_fin ?: null
    );
} else {
    $stockPorLote = $egresoService->obtenerStockPorLote();
}

// Validaciones de fechas
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">  
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title"><i class="fas fa-boxes"></i> Stock de Materiales</h1>
            <div class="action-buttons">
                <button type="button" class="btn btn-exit" onclick="window.location.href='../PRESENTACION/ADM_Material.php'">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </button>
                <button type="button" class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
        
        <div class="search-panel">
            <form method="get">
                <div class="search-form">
                    <div class="form-group">
                        <label for="material"><i class="fas fa-box"></i> Material</label>
                        <input type="text" id="material" name="material" class="search-input"
                            placeholder="Ej: Material..." value="<?php echo htmlspecialchars($_GET['material'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="proveedor"><i class="fas fa-truck"></i> Proveedor</label>
                        <input type="text" id="proveedor" name="proveedor" class="search-input"
                            placeholder="Ej: Proveedor XYZ..." value="<?php echo htmlspecialchars($_GET['proveedor'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_inicio"><i class="far fa-calendar-alt"></i> Fecha desde</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="search-input" 
                               value="<?php echo htmlspecialchars($fecha_inicio ?? ''); ?>" 
                               min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_fin"><i class="far fa-calendar-alt"></i> Fecha hasta</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="search-input" 
                               value="<?php echo htmlspecialchars($fecha_fin ?? ''); ?>" 
                               min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><button type="button" onclick="window.location.href='stock.php'"><i class="fas fa-arrows-rotate"></i></button></label>
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
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
                <?php if (!empty($agrupado)): ?>
                    <?php foreach ($agrupado as $material => $datos): ?>
                        <tr class="material-row">
                            <td rowspan="<?php echo count($datos['subfilas']) + 1; ?>">
                                <?php echo htmlspecialchars($material); ?>
                            </td>
                            <td colspan="5"></td>
                            <td rowspan="<?php echo count($datos['subfilas']) + 1; ?>">
                                <?php echo htmlspecialchars($datos['stock_total']); ?> 
                                <span class="total-bs">(Total Bs: <?php echo number_format($datos['total_bs'], 2); ?>)</span>
                            </td>
                        </tr>
                        <?php foreach ($datos['subfilas'] as $sub): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['proveedor_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($sub['i_fecha']); ?></td>
                                <td><?php echo htmlspecialchars($sub['stock_restante'] . ' - ' . $sub['u_medida']); ?></td>
                                <td><?php echo htmlspecialchars($sub['precio'] . ' Bs'); ?></td>
                                <td><?php echo htmlspecialchars(number_format($sub['stock_restante'] * $sub['precio'], 2) . ' Bs'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">
                            <i class="fas fa-info-circle"></i> No se encontraron resultados para la búsqueda
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Pequeño script para mejorar la experiencia de usuario
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer el valor máximo de fecha_inicio como fecha_fin y viceversa
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            if(fechaInicio && fechaFin) {
                fechaInicio.addEventListener('change', function() {
                    fechaFin.min = this.value;
                });
                
                fechaFin.addEventListener('change', function() {
                    fechaInicio.max = this.value;
                });
            }
        });
    </script>
</body>
</html>