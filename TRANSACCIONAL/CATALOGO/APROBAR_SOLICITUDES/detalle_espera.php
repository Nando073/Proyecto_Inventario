<?php
require_once __DIR__ . '/../../../Seguridad.php';
require_once __DIR__ . '/../../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

$id_solicitud = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_solicitud <= 0) {
    echo "<div class='alert alert-danger'>ID de solicitud no válido.</div>";
    exit;
}

$solicitudService = new N_Solicitud();
$solicitudDetalles = $solicitudService->obtenerDetallesSolicitudes($id_solicitud);

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
    <?php foreach ($solicitudDetalles as $index => $detalle): ?>
    <div class="material-item">
        <div class="row align-items-center">
            <!-- Información del material -->
            <div class="col">
                <div class="row g-3">
                    <!-- Categoría -->
                    <div class="col-md-4">
                        <label class="form-label-custom">Categoría</label>
                        <input type="text" 
                               class="form-control input-readonly" 
                               value="<?php echo htmlspecialchars($detalle['categoria']); ?>" 
                               readonly>
                    </div>
                    
                    <!-- Material -->
                    <div class="col-md-5">
                        <label class="form-label-custom">Material</label>
                        <input type="text" 
                               class="form-control input-readonly" 
                               value="<?php echo htmlspecialchars($detalle['material']); ?>" 
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
                               value="<?php echo htmlspecialchars($detalle['cantidad']); ?>"
                               min="1"
                               step="1">
                               <span class="input-group-text"><?php echo htmlspecialchars($detalle['medida']); ?></span>
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
            onclick="cambiarEstadoModal(<?php echo $id_solicitud; ?>, 3)">
        <i class="bi bi-x-circle"></i> Rechazar Solicitud
    </button>
    <button type="button" 
            class="btn btn-success btn-custom" 
            onclick="cambiarEstadoModal(<?php echo $id_solicitud; ?>, 2)">
        <i class="bi bi-check-circle"></i> Aprobar Solicitud
    </button>
</div>

<script>
function cambiarEstadoModal(idSolicitud, estado) {
    const comentario = document.getElementById('comentarioSupervisor').value;
    const estadoTexto = estado === 2 ? 'aprobar' : 'rechazar';
    
    // Recopilar las cantidades editadas
    const cantidades = [];
    const formMateriales = document.getElementById('formMateriales');
    const inputs = formMateriales.querySelectorAll('input[name^="cantidad_"]');
    
    inputs.forEach(input => {
        const cantidad = parseInt(input.value);
        if (cantidad <= 0 || isNaN(cantidad)) {
            alert('Por favor, ingrese cantidades válidas (mayores a 0)');
            input.focus();
            throw new Error('Cantidad inválida');
        }
        cantidades.push(cantidad);
    });
    
    if (confirm('¿Está seguro de ' + estadoTexto + ' esta solicitud?')) {
        // Mostrar indicador de carga
        const btnRechazar = document.querySelector('.btn-danger');
        const btnAprobar = document.querySelector('.btn-success');
        btnRechazar.disabled = true;
        btnAprobar.disabled = true;
        btnRechazar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
        btnAprobar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
        
        fetch('../../TRANSACCIONAL/cambiar_estado_solicitud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_solicitud=${idSolicitud}&estado=${estado}&comentario=${encodeURIComponent(comentario)}&cantidades=${JSON.stringify(cantidades)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Estado actualizado correctamente');
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                // Restaurar botones
                btnRechazar.disabled = false;
                btnAprobar.disabled = false;
                btnRechazar.innerHTML = '<i class="bi bi-x-circle"></i> Rechazar Solicitud';
                btnAprobar.innerHTML = '<i class="bi bi-check-circle"></i> Aprobar Solicitud';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el estado');
            // Restaurar botones
            btnRechazar.disabled = false;
            btnAprobar.disabled = false;
            btnRechazar.innerHTML = '<i class="bi bi-x-circle"></i> Rechazar Solicitud';
            btnAprobar.innerHTML = '<i class="bi bi-check-circle"></i> Aprobar Solicitud';
        });
    }
}

// Validar cantidades en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const inputsCantidad = document.querySelectorAll('.cantidad-input');
    inputsCantidad.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 1) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#667eea';
            }
        });
        
        // Evitar valores decimales
        input.addEventListener('keypress', function(e) {
            if (e.key === '.' || e.key === ',') {
                e.preventDefault();
            }
        });
    });
});
</script>