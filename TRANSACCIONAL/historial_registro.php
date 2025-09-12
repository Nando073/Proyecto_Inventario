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
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --dark: #34495e;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
        }
        
        .page-title {
            color: var(--primary);
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        /* Estilos para el panel de búsqueda */
        .search-panel {
            background: linear-gradient(to right, #f9f9f9, #f1f5f9);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e5e9;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }
        
        @media (max-width: 1200px) {
            .search-form {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .search-form {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }
        
        .search-input {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            text-decoration: none;
        }
        
        .btn-back {
            background: var(--secondary);
            color: white;
        }
        
        .btn-back:hover {
            background: #2980b9;
        }
        
        .btn-print {
            background: var(--success);
            color: white;
        }
        
        .btn-print:hover {
            background: #219653;
        }
        
        .btn-delete {
            background: var(--accent);
            color: white;
        }
        
        .btn-delete:hover {
            background: #c0392b;
        }
        
        .btn-search {
            background: var(--primary);
            color: white;
        }
        
        .btn-search:hover {
            background: #1a252f;
        }
        
        .errores {
            background: #ffebee;
            color: #c62828;
            padding: 10px 12px;
            border-radius: 6px;
            margin-top: 12px;
            border-left: 4px solid #c62828;
            font-size: 14px;
        }
        
        /* Panel de ingreso */
        .ingreso-card {
            background: linear-gradient(to right, #f9f9f9, #f1f5f9);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e5e9;
        }
        
        .ingreso-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            color: var(--primary);
        }
        
        /* Estilos para la tabla */
        .table-container {
            overflow-x: auto;
        }
        
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 10px;
        }
        
        th, td { 
            border: 1px solid #ccc; 
            padding: 10px 12px; 
            text-align: left;
        }
        
        th { 
            background: #f0f0f0; 
            font-weight: 600;
        }
        
        tbody tr:hover {
            background-color: #f5f7ff;
        }
        
        tfoot {
            font-weight: bold;
            background-color: #e0eaff;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-style: italic;
        }
        
        /* Modal de confirmación */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .modal-title {
            font-size: 18px;
            color: var(--primary);
        }
        
        .close {
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--accent);
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-cancel {
            background: var(--gray);
            color: white;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
        
        .btn-confirm {
            background: var(--accent);
            color: white;
        }
        
        .btn-confirm:hover {
            background: #c0392b;
        }
        
        @media (max-width: 768px) {
            .ingreso-info {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .modal-content {
                width: 90%;
                margin: 20% auto;
            }
        }
        
        /* Estilos para impresión */
        @media print {
            .search-panel, .btn, .ingreso-header .btn-delete {
                display: none !important;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
            
            body {
                background: white;
                padding: 10px;
            }
            
            .ingreso-card {
                border: 1px solid #000;
                page-break-inside: avoid;
            }
            
            .header {
                border-bottom: 2px solid #000;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title"><i class="fas fa-history"></i> Historial de Ingresos</h1>
            <div class="action-buttons">
                <a href="Ingreso.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
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
                        <label style="opacity: 0;">Buscar</label>
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