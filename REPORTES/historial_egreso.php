<?php 
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Egreso.php';

if (isset($_GET['msg'])) {
    echo "<script>alert('" . htmlspecialchars($_GET['msg']) . "');</script>";
}

// Obtener los detalles de los egresos
$egresoService = new N_Egreso();
$detalles = $egresoService->ObtenerDetallesEgresos();

// Agrupar detalles por `id_egreso`
$egresosAgrupados = [];
foreach ($detalles as $detalle) {
    $id_egreso = $detalle['id_egreso'];
    if (!isset($egresosAgrupados[$id_egreso])) {
        $egresosAgrupados[$id_egreso] = [];
    }
    $egresosAgrupados[$id_egreso][] = $detalle;
}

// Verificar si se ha solicitado eliminar un egreso
if (isset($_GET['id_egreso']) && $_GET['action'] === 'delete') {
    $id_egreso = filter_input(INPUT_GET, 'id_egreso', FILTER_VALIDATE_INT);

    if ($id_egreso) {
        try {
            $egresoService->eliminarEgreso($id_egreso);
            header('Location: historial_egreso.php?msg=Egreso eliminado correctamente');
            exit();
        } catch (Exception $e) {
            echo "Error al eliminar el egreso: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "ID de egreso no válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Egresos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="egreso.css">
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
            </div>
        </div>
        
        <!-- Panel de búsqueda -->
        <div class="search-panel">
            <form method="get">
                <div class="search-form">
                    <div class="form-group">
                        <label for="area"><i class="fas fa-box"></i> Area</label>
                        <input type="text" id="area" name="area" class="search-input"
                            placeholder="Ej: Administracion..." value="<?php echo htmlspecialchars($_GET['area'] ?? ''); ?>">
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
            
            <?php if (isset($errores) && $errores): ?>
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
                <div class="egreso-card">
                    <div class="egreso-info">
                        <div class="info-item">
                            <span class="info-label"><i class="far fa-calendar-alt"></i> Fecha</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['e_fecha']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-user"></i> Funcionario</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['funcionario_nombre']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-receipt"></i> Código Solicitud</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['e_solicitud']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-cubes"></i> Total de Materiales</span>
                            <span class="info-value"><?php echo number_format($totalEgreso); ?></span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Detalle</th>
                                    <th>Material</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
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
                                    <td colspan="3" style="text-align: right;">Total del Egreso:</td>
                                    <td><?php echo number_format($totalEgreso); ?></td>
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
    
    <!-- Modal de confirmación para eliminar -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirmar Eliminación</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este egreso? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-confirm" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        // Función para abrir el modal de confirmación
        function openDeleteModal(id_egreso) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDelete');
            
            // Configurar el evento de eliminación
            confirmBtn.onclick = function() {
                window.location.href = `?action=delete&id_egreso=${id_egreso}`;
            };
            
            // Mostrar el modal
            modal.style.display = 'block';
        }
        
        // Función para cerrar el modal
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        };
        
        // Mostrar mensaje de confirmación si existe
        <?php if (isset($_GET['msg'])): ?>
            alert('<?php echo htmlspecialchars($_GET['msg']); ?>');
        <?php endif; ?>
    </script>
</body>
</html>