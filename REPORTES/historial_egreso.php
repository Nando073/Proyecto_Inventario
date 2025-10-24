<?php 
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Egreso.php';

if (isset($_GET['msg'])) {
    echo "<script>alert('" . htmlspecialchars($_GET['msg']) . "');</script>";
}

// Obtener parámetros de búsqueda
$funcionario = $_GET['funcionario'] ?? null;
$area = $_GET['area'] ?? null;
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;

$egresoService = new N_Egreso();

// Validaciones de fechas
$errores = [];
$fecha_min = '2020-01-01';
$fecha_max = date('Y-m-d');
if ($fecha_inicio && $fecha_inicio < $fecha_min) $errores[] = "La fecha de inicio no puede ser menor a $fecha_min";
if ($fecha_fin && $fecha_fin > $fecha_max) $errores[] = "La fecha de fin no puede ser mayor a hoy";
if ($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin) $errores[] = "La fecha de inicio no puede ser mayor que la de fin";

// Si hay búsqueda y no hay errores, usar buscarHistorialEgreso
if (($funcionario || $area || $fecha_inicio || $fecha_fin) && empty($errores)) {
    $detalles = $egresoService->buscarHistorialEgreso(
        $area ?: null,
        $funcionario ?: null,
        $fecha_inicio ?: null,
        $fecha_fin ?: null
    );
} else {
    // Si hay errores o no hay búsqueda, obtener todos los datos
    $detalles = $egresoService->ObtenerDetallesEgresos();
}

// Agrupar detalles por `id_egreso`
$egresosAgrupados = [];
foreach ($detalles as $detalle) {
    $id_egreso = $detalle['id_egreso'];
    if (!isset($egresosAgrupados[$id_egreso])) {
        $egresosAgrupados[$id_egreso] = [];
    }
    $egresosAgrupados[$id_egreso][] = $detalle;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Egresos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="ingreso_egreso.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title"><i class="fas fa-history"></i> Historial de Egresos</h1>
            <div class="action-buttons">
                <a href="../TRANSACCIONAL/Egreso.php" class="btn btn-back">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-excel" onclick="window.location.href='EXCEL/egreso.php'">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
            </div>
        </div>
        
        <!-- Panel de búsqueda -->
        <div class="search-panel">
            <form method="get">
                <div class="search-form">
                    <div class="form-group">
                        <label for="area"><i class="fas fa-building"></i> Área</label>
                        <input type="text" id="area" name="area" class="search-input"
                            placeholder="Ej: Administración..." value="<?php echo htmlspecialchars($_GET['area'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="funcionario"><i class="fas fa-user"></i> Funcionario</label>
                        <input type="text" id="funcionario" name="funcionario" class="search-input"
                            placeholder="Ej: Juan Pérez..." value="<?php echo htmlspecialchars($_GET['funcionario'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_inicio"><i class="far fa-calendar-alt"></i> Fecha desde</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="search-input" 
                               value="<?php echo htmlspecialchars($_GET['fecha_inicio'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_fin"><i class="far fa-calendar-alt"></i> Fecha hasta</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="search-input" 
                               value="<?php echo htmlspecialchars($_GET['fecha_fin'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><button type="button" onclick="window.location.href='historial_egreso.php'"><i class="fas fa-arrows-rotate"></i></button></label>
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($errores)): ?>
                <div class="errores">
                    <?php foreach ($errores as $e) echo "<div><i class='fas fa-exclamation-circle'></i> $e</div>"; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Lista de egresos -->
        <?php if (!empty($egresosAgrupados)): ?>
            <?php foreach ($egresosAgrupados as $id_egreso => $detalles): 
                $totalEgreso = 0;
                foreach ($detalles as $detalle) {
                    $totalEgreso += $detalle['e_stock'];
                }
            ?>
                <div class="cards">
                    <div class="info">
                        <div class="info-item">
                            <span class="info-label"><i class="far fa-calendar-alt"></i> Fecha</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['e_fecha']); ?></span>
                        </div>

                         <div class="info-item">
                            <span class="info-label"><i class="fas fa-cubes"></i> Área</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['a_nombre']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-user"></i> Funcionario</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['funcionario_nombre']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-receipt"></i> Código Solicitud</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['e_solicitud']); ?></span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['categoria_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['e_stock'] . ' ' . $detalle['u_medida']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-align: right;">Total del Egreso:</td>
                                    <td><?php echo number_format($totalEgreso, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-info-circle"></i> No se encontraron registros de egresos
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        
        <?php if (isset($_GET['msg'])): ?>
            alert('<?php echo htmlspecialchars($_GET['msg']); ?>');
        <?php endif; ?>
    </script>
</body>
</html>