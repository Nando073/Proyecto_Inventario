<?php 
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Ingreso.php';

if (isset($_GET['msg'])) {
    echo "<script>alert('" . htmlspecialchars($_GET['msg']) . "');</script>";
}
// Obtener los detalles de los ingresos
$ingresoService = new N_Ingreso();
$detalles = $ingresoService->ObtenerDetallesIngresos();

// Agrupar detalles por `id_ingreso`
$ingresosAgrupados = [];
foreach ($detalles as $detalle) {
    $id_ingreso = $detalle['id_ingreso'];
    if (!isset($ingresosAgrupados[$id_ingreso])) {
        $ingresosAgrupados[$id_ingreso] = [];
    }
    $ingresosAgrupados[$id_ingreso][] = $detalle;
}

// Verificar si se ha solicitado eliminar un ingreso
if (isset($_GET['id_ingreso']) && $_GET['action'] === 'delete') {
    $id_ingreso = filter_input(INPUT_GET, 'id_ingreso', FILTER_VALIDATE_INT);

    if ($id_ingreso) {
        try {
            $ingresoService->eliminarIngreso($id_ingreso);
            header('Location: historial_registro.php?msg=Ingreso eliminado correctamente');
            exit();
        } catch (Exception $e) {
            echo "Error al eliminar el ingreso: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "ID de ingreso no válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ingresos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="ingreso.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title"><i class="fas fa-history"></i> Historial de Ingresos</h1>
            <div class="action-buttons">
                <a href="../TRANSACCIONAL/Ingreso.php" class="btn btn-back">
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
                        <label for="material"><i class="fas fa-box"></i> Material</label>
                        <input type="text" id="material" name="material" class="search-input"
                            placeholder="Ej: Cemento..." value="<?php echo htmlspecialchars($_GET['material'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="proveedor"><i class="fas fa-truck"></i> Proveedor</label>
                        <input type="text" id="proveedor" name="proveedor" class="search-input"
                            placeholder="Ej: Proveedor XYZ..." value="<?php echo htmlspecialchars($_GET['proveedor'] ?? ''); ?>">
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
                        <label><button type="button" onclick="window.location.href='historial_registro.php'"><i class="fas fa-arrows-rotate"></i></button></label>
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
        
        <!-- Lista de ingresos -->
        <?php if (!empty($ingresosAgrupados)): ?>
            <?php foreach ($ingresosAgrupados as $id_ingreso => $detalles): 
                $totalIngreso = 0;
                foreach ($detalles as $detalle) {
                    $totalIngreso += $detalle['sub_total'];
                }
            ?>
                <div class="ingreso-card">
                    
                    <div class="ingreso-info">
                        <div class="info-item">
                            <span class="info-label"><i class="far fa-calendar-alt"></i> Fecha</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['i_fecha']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-truck"></i> Proveedor</span>
                            <span class="info-value"><?php echo htmlspecialchars($detalles[0]['proveedor_nombre']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-receipt"></i> Total del Ingreso</span>
                            <span class="info-value"><?php echo number_format($totalIngreso, 2); ?> Bs.</span>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Detalle</th>
                                    <th>Material</th>
                                    <th>Precio Unitario</th>
                                    <th>Cantidad</th>
                                    <th>Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detalle['id_d_ingreso']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
                                    <td><?php echo number_format($detalle['precio'], 2); ?> Bs.</td>
                                    <td><?php echo htmlspecialchars($detalle['cantidad'] . " " . $detalle['u_medida']); ?></td>
                                    <td><?php echo number_format($detalle['sub_total'], 2); ?> Bs.</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align: right;">Total del Ingreso:</td>
                                    <td><?php echo number_format($totalIngreso, 2); ?> Bs.</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-info-circle"></i> No se encontraron registros de ingresos
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
                <p>¿Está seguro de que desea eliminar este ingreso? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-confirm" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        // Función para abrir el modal de confirmación
        function openDeleteModal(id_ingreso) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDelete');
            
            // Configurar el evento de eliminación
            confirmBtn.onclick = function() {
                window.location.href = `?action=delete&id_ingreso=${id_ingreso}`;
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