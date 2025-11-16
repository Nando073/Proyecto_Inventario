<?php
require_once __DIR__ . '/../../../Seguridad.php';
require_once __DIR__ . '/../../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

// =================== APROBAR SOLICITUD ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'aprobar') {
    error_log("=== INICIO PROCESO APROBACIÓN ===");
    error_log("POST params: " . print_r($_POST, true));
    
    $id_solicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    $materiales = $_POST['materiales'] ?? []; // Array: materiales[0][id], materiales[0][cantidad]
    $detalle = trim($_POST['detalle'] ?? 'Solicitud aprobada');
    
    error_log("ID Solicitud: " . $id_solicitud);
    error_log("ID Usuario: " . $id_usuario);
    error_log("Detalle: " . $detalle);
    error_log("Materiales recibidos: " . print_r($materiales, true));
    
    if (!$id_usuario) {
        error_log("ERROR: No hay id_usuario en sesión");
        $_SESSION['mensaje'] = "No se pudo identificar al usuario.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Solicitud_espera.php');
        exit();
    }
    
    if (!$id_solicitud) {
        error_log("ERROR: ID de solicitud no válido");
        $_SESSION['mensaje'] = "ID de solicitud no válido.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Solicitud_espera.php');
        exit();
    }
    
    // Armar array de detalles para el procedimiento (igual que Generar_Solicitud.php)
    $detalles_solicitud = [];
    foreach ($materiales as $mat) {
        $id_material = isset($mat['id']) ? intval($mat['id']) : 0;
        $cantidad = isset($mat['cantidad']) ? intval($mat['cantidad']) : 0;
        if ($id_material > 0 && $cantidad > 0) {
            $detalles_solicitud[] = [
                'id_material' => $id_material,
                'cantidad' => $cantidad
            ];
        }
    }
    
    if (empty($detalles_solicitud)) {
        error_log("ERROR: No hay materiales válidos");
        $_SESSION['mensaje'] = "Debe incluir al menos un material válido.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Solicitud_espera.php');
        exit();
    }
    
    error_log("Detalles procesados: " . print_r($detalles_solicitud, true));
    
    try {
        error_log("Llamando a aprobarSolicitudConDetalles...");
        $solicitudService = new N_Solicitud();
        $solicitudService->aprobarSolicitudConDetalles($id_solicitud, $id_usuario, $detalle, $detalles_solicitud);
        
        error_log("EXITO: Solicitud aprobada");
        $_SESSION['mensaje'] = "¡Solicitud aprobada correctamente!";
        $_SESSION['tipo_mensaje'] = "success";
        header('Location: Solicitud_espera.php');
        exit();
        
    } catch (Exception $e) {
        error_log("EXCEPCION: " . $e->getMessage());
        $_SESSION['mensaje'] = "Error al aprobar solicitud: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Solicitud_espera.php');
        exit();
    }
}

// =================== RECHAZAR (ELIMINAR) SOLICITUD ===================
if (isset($_GET['id_solicitud']) && isset($_GET['accion']) && $_GET['accion'] === 'rechazar') {
    // Log para debugging
    error_log("=== INICIO PROCESO RECHAZO ===");
    error_log("GET params: " . print_r($_GET, true));
    
    $id_solicitud_eliminar = filter_input(INPUT_GET, 'id_solicitud', FILTER_VALIDATE_INT);
    $comentario = isset($_GET['comentario']) ? trim($_GET['comentario']) : 'Solicitud rechazada';
    
    error_log("ID Solicitud: " . $id_solicitud_eliminar);
    error_log("Comentario: " . $comentario);
    
    // Obtener el ID del usuario que realiza la acción
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    error_log("ID Usuario de sesión: " . $id_usuario);
    
    if (!$id_usuario) {
        error_log("ERROR: No hay id_usuario en sesión");
        $_SESSION['mensaje'] = "No se pudo identificar al usuario.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Solicitud_espera.php');
        exit();
    }

    if ($id_solicitud_eliminar) {
        try {
            error_log("Intentando rechazar solicitud...");
            $solicitudService = new N_Solicitud();
            // Llamar al método eliminarSolicitud pasando id_usuario y comentario
            $resultado = $solicitudService->eliminarSolicitud($id_solicitud_eliminar, $id_usuario, $comentario);
            
            error_log("Resultado: " . print_r($resultado, true));

            // Verificar el resultado
            if (isset($resultado['success']) && $resultado['success']) {
                error_log("EXITO: Solicitud rechazada");
                $_SESSION['mensaje'] = "Solicitud rechazada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                error_log("ERROR: No se pudo rechazar");
                $_SESSION['mensaje'] = "No se pudo rechazar la solicitud.";
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header('Location: Solicitud_espera.php');
            exit();

        } catch (Exception $e) {
            error_log("EXCEPCION: " . $e->getMessage());
            $_SESSION['mensaje'] = "Error al rechazar solicitud: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: Solicitud_espera.php');
            exit();
        }
    } else {
        error_log("ERROR: ID de solicitud no válido");
        $_SESSION['mensaje'] = "ID de solicitud no válido.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: Solicitud_espera.php');
        exit();
    }
}

$id_solicitud = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_solicitud <= 0) {
    echo "<div class='alert alert-danger'>ID de solicitud no válido.</div>";
    exit;
}

$solicitudService = new N_Solicitud();
$solicitudDetalles = $solicitudService->obtenerDetallesSolicitudes($id_solicitud);

// Debug: Verificar estructura de datos
if (!empty($solicitudDetalles)) {
    error_log("=== ESTRUCTURA DE DATOS ===");
    error_log("Primer detalle completo: " . print_r($solicitudDetalles[0], true));
    error_log("Campos disponibles: " . implode(', ', array_keys($solicitudDetalles[0])));
}

if (empty($solicitudDetalles)) {
    echo "<div class='alert alert-warning'>No se encontraron detalles para esta solicitud.</div>";
    exit;
}

// Tomamos la cabecera del primer registro
$solicitud = $solicitudDetalles[0];
?>

<style>
    .material-item {
        background-color: #f8f9fa;
        border-left: 4px solid #667eea;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    .material-item:hover {
        background-color: #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .material-item.material-removido {
        opacity: 0;
        transform: scale(0.8);
    }
    .material-number {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .form-label-custom {
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
    }
    .input-readonly {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
    .cantidad-input {
        font-size: 1.1rem;
        font-weight: bold;
        text-align: center;
        border: 2px solid #ced4da;
    }
    .cantidad-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-custom {
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }
    .info-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .quitar-material {
        transition: all 0.3s ease;
    }
    .quitar-material:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }
</style>


<!-- <?php if (!empty($solicitud['comentario'])): ?>
<div class="mb-4">
    <label class="form-label-custom">
        <i class="bi bi-chat-left-text"></i> Comentario del Solicitante
    </label>
    <textarea class="form-control input-readonly" rows="2" readonly><?php echo htmlspecialchars($solicitud['comentario']); ?></textarea>
</div>
<?php endif; ?> -->

<!-- Materiales solicitados -->
<h6 class="mb-3 text-success">
    <i class="bi bi-box-seam"></i> Materiales Solicitados
</h6>

<form id="formMateriales">
    <?php foreach ($solicitudDetalles as $index => $detalle): 
        // Determinar qué campo usar para el ID del material
        $material_id = isset($detalle['id_material']) ? $detalle['id_material'] : (isset($detalle['id']) ? $detalle['id'] : '');
        $material_nombre = isset($detalle['material']) ? $detalle['material'] : (isset($detalle['m_nombre']) ? $detalle['m_nombre'] : '');
        
        // DEBUG: Ver qué campos existen
        error_log("Material #$index: id_material=" . ($detalle['id_material'] ?? 'NO EXISTE') . 
                  ", id=" . ($detalle['id'] ?? 'NO EXISTE') . 
                  ", material=" . ($detalle['material'] ?? 'NO EXISTE') . 
                  ", m_nombre=" . ($detalle['m_nombre'] ?? 'NO EXISTE'));
        error_log("Usando: material_id='$material_id', material_nombre='$material_nombre'");
    ?>
    <div class="material-item" id="material-item-<?php echo $index; ?>" data-material-id="<?php echo htmlspecialchars($material_id); ?>">
        <div class="row align-items-center">
            <!-- Información del material -->
            <div class="col">
                <div class="row g-3">
                    <!-- Categoría -->
                    <div class="col-md-3">
                        <label class="form-label-custom">Categoría</label>
                        <input type="text" 
                               class="form-control input-readonly" 
                               value="<?php echo htmlspecialchars($detalle['categoria'] ?? ''); ?>" 
                               readonly>
                    </div>
                    
                    <!-- Material -->
                    <div class="col-md-4">
                        <label class="form-label-custom">Material</label>
                        <input type="hidden" 
                               class="material-id-input"
                               name="material_id_<?php echo $index; ?>" 
                               value="<?php echo htmlspecialchars($material_id); ?>">
                        <input type="text" 
                               class="form-control input-readonly material-nombre" 
                               value="<?php echo htmlspecialchars($material_nombre); ?>" 
                               readonly>
                    </div>
                    
                    <!-- Cantidad (editable) -->
                    <div class="col-md-3">
                        <label class="form-label-custom">
                            Cantidad
                            <i class="bi bi-pencil-square text-primary" title="Editable"></i>
                        </label>
                        <input type="number" 
                               class="form-control cantidad-input" 
                               name="cantidad_<?php echo $index; ?>" 
                               data-index="<?php echo $index; ?>"
                               value="<?php echo htmlspecialchars($detalle['cantidad'] ?? ''); ?>"
                               min="1"
                               step="1">
                        <span class="input-group-text"><?php echo htmlspecialchars($detalle['medida'] ?? $detalle['u_medida'] ?? ''); ?></span>
                    </div>
                    
                    <!-- Botón eliminar -->
                    <div class="col-md-2 text-center">
                        <label class="form-label-custom d-block">&nbsp;</label>
                        <button type="button" 
                                class="btn btn-danger btn-sm quitar-material" 
                                data-index="<?php echo $index; ?>"
                                title="Quitar este material">
                            <i class="bi bi-x-circle"></i> Quitar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</form>

<!-- Comentario del Supervisor -->
<div class="mt-4">
    <label class="form-label-custom">
        <i class="bi bi-chat-dots"></i> Comentario del Supervisor
    </label>
    <textarea class="form-control" 
              id="comentarioSupervisor" 
              rows="3" 
              placeholder="Escribe un comentario sobre esta solicitud (opcional)..."
              style="border: 2px solid #ced4da;"></textarea>
    <small class="text-muted">Este comentario será visible para el solicitante</small>
</div>

<!-- Botones de acción -->
<div class="mt-4 d-flex gap-2 justify-content-end">
    <button type="button" 
            class="btn btn-danger btn-custom" 
            onclick="rechazarSolicitud(<?php echo $id_solicitud; ?>)">
        <i class="bi bi-x-circle"></i> Rechazar Solicitud
    </button>
    <button type="button" 
            class="btn btn-success btn-custom" 
            onclick="aprobarSolicitud(<?php echo $id_solicitud; ?>)">
        <i class="bi bi-check-circle"></i> Aprobar Solicitud
    </button>
</div>