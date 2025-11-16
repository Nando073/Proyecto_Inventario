<?php
require_once __DIR__ . '/../../../Seguridad.php';
verificarAcceso(['Administrador','Supervisor','Supervisor']);
require_once __DIR__ . '/../../../NEGOCIO/SOLICITUDES_N/N_Solicitudes.php';

// Instancia
$solicitudService = new N_Solicitud();

// Obtener todas las solicitudes (solo cabeceras, sin duplicados)
$solicitudes = $solicitudService->obtenerSolicitudesCabecera();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes</title>
    <!-- Bootstrap CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../solicitud.css?v=<?php echo(rand()); ?>">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <style>
        :root {
            --bs-success-lighter: #5096e6ff;
            --bs-custom-teal: #41b488ff; 
        }
        .bg-success-lighter {
            background-color: var(--bs-success-lighter) !important;
        }
        
        .bg-custom-teal {
            background-color: var(--bs-custom-teal) !important;
        }
      </style>
</head>
<body class="container bg-light">
<?php include __DIR__ . '/../Cabecera.php';?>
<div class="container py-3">
    <!-- Mostrar mensajes de éxito/error -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
            <?php 
                echo htmlspecialchars($_SESSION['mensaje']); 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check"></i> Solicitudes Pendientes</h2>
        <span class="badge bg-primary fs-6">Total: <?php echo count($solicitudes); ?> Pendientes</span>
    </div>
    
    <div class="row g-4">
        <?php if (empty($solicitudes)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-3"></i>
                    <p class="mb-0 mt-2">No hay solicitudes registradas</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($solicitudes as $sol): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-solicitud shadow-sm h-100">
                    <div class="card-header bg-custom-teal text-white text-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i>
                            Solicitud #<?php echo str_pad($sol['cod_solicitud'], 4, '0', STR_PAD_LEFT); ?>
                        </h5>
                    </div>
                    <div class="card-body mx-2">
                        <p><strong><i class="bi bi-person"></i> Funcionario:</strong><br>
                           <?php echo htmlspecialchars($sol['funcionario']); ?></p>
                        
                        <p><strong><i class="bi bi-calendar"></i> Fecha:</strong><br>
                           <?php echo date('d/m/Y H:i', strtotime($sol['s_fecha'])); ?></p>

                        <p>
                          <strong><i class="bi bi-check-circle"></i> Estado:</strong><br>
                          <p class="text-primary"><?php 
                            echo ($sol['estado'] == 1) ? 'Pendiente' : htmlspecialchars($sol['estado']);
                          ?>
                          </p>
                        </p>

                        <?php if (!empty($sol['comentario'])): ?>
                        <p><strong><i class="bi bi-chat-left-text"></i> Comentario:</strong><br>
                           <?php echo htmlspecialchars(substr($sol['comentario'], 0, 50)); ?>
                           <?php echo strlen($sol['comentario']) > 50 ? '...' : ''; ?></p>
                        <?php endif; ?>
                        
                        <button class="btn bg-success-lighter w-100 ver-detalle" data-id="<?php echo $sol['id_solicitud']; ?>">
                            <i class="bi bi-eye text-white">Ver Detalle </i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-custom-teal text-white">
        <h5 class="modal-title">Detalle de Solicitud</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Funciones globales para los botones del modal
function rechazarSolicitud(idSolicitud) {
    console.log('Función rechazarSolicitud llamada con ID:', idSolicitud);
    
    const comentarioElement = document.getElementById('comentarioSupervisor');
    if (!comentarioElement) {
        alert('Error: No se encontró el campo de comentario');
        console.error('Elemento comentarioSupervisor no encontrado');
        return;
    }
    
    const comentario = comentarioElement.value.trim();
    console.log('Comentario capturado:', comentario);
    
    if (!comentario) {
        alert('Por favor, ingrese un comentario explicando el motivo del rechazo.');
        comentarioElement.focus();
        comentarioElement.style.borderColor = '#dc3545';
        return;
    }
    
    if (confirm('¿Está seguro de rechazar esta solicitud?')) {
        // Construir la URL
        const url = `detalle_espera.php?id_solicitud=${idSolicitud}&accion=rechazar&comentario=${encodeURIComponent(comentario)}`;
        console.log('Redirigiendo a:', url);
        
        // Redirigir
        window.location.href = url;
    } else {
        console.log('Rechazo cancelado por el usuario');
    }
}

// Función para rechazar solicitud
function rechazarSolicitud(idSolicitud) {
    console.log('Función rechazarSolicitud llamada con ID:', idSolicitud);
    
    const comentarioElement = document.getElementById('comentarioSupervisor');
    if (!comentarioElement) {
        alert('Error: No se encontró el campo de comentario');
        console.error('Elemento comentarioSupervisor no encontrado');
        return;
    }
    
    const comentario = comentarioElement.value.trim();
    console.log('Comentario capturado:', comentario);
    
    if (!comentario) {
        alert('Por favor, ingrese un comentario explicando el motivo del rechazo.');
        comentarioElement.focus();
        comentarioElement.style.borderColor = '#dc3545';
        return;
    }
    
    if (confirm('¿Está seguro de rechazar esta solicitud?')) {
        // Construir la URL
        const url = `detalle_espera.php?id_solicitud=${idSolicitud}&accion=rechazar&comentario=${encodeURIComponent(comentario)}`;
        console.log('Redirigiendo a:', url);
        
        // Redirigir
        window.location.href = url;
    } else {
        console.log('Rechazo cancelado por el usuario');
    }
}

// Función para aprobar solicitud
function aprobarSolicitud(idSolicitud) {
    console.log('=== INICIO APROBACIÓN ===' );
    console.log('ID Solicitud:', idSolicitud);
    
    const comentarioElement = document.getElementById('comentarioSupervisor');
    if (!comentarioElement) {
        alert('Error: No se encontró el campo de comentario');
        return;
    }
    
    const comentario = comentarioElement.value.trim() || 'Solicitud aprobada';
    console.log('Comentario:', comentario);
    
    // Recopilar solo los materiales que NO fueron removidos
    const formMateriales = document.getElementById('formMateriales');
    const materialesItems = formMateriales.querySelectorAll('.material-item');
    const materiales = [];
    
    let cantidadValida = true;
    let materialesActivos = 0;
    
    materialesItems.forEach(item => {
        // Ignorar materiales ocultos o removidos
        if (item.style.display === 'none' || item.classList.contains('material-removido')) {
            console.log('Material removido, ignorando:', item.id);
            return;
        }
        
        materialesActivos++;
        
        // Intentar obtener el ID del material de diferentes formas
        let idMaterial = item.getAttribute('data-material-id');
        console.log('DEBUG - Item:', item.id, 'data-material-id:', idMaterial);
        
        // Si está vacío, buscar en el input hidden
        if (!idMaterial || idMaterial === '') {
            const index = item.id.replace('material-item-', '');
            const hiddenInput = item.querySelector('.material-id-input');
            if (hiddenInput) {
                idMaterial = hiddenInput.value;
                console.log('DEBUG - ID obtenido del input hidden:', idMaterial);
            }
        }
        
        const inputCantidad = item.querySelector('.cantidad-input');
        
        if (!inputCantidad) {
            console.error('No se encontró input de cantidad');
            return;
        }
        
        const cantidad = parseInt(inputCantidad.value);
        
        if (cantidad <= 0 || isNaN(cantidad)) {
            alert('Por favor, ingrese cantidades válidas (mayores a 0)');
            inputCantidad.focus();
            inputCantidad.style.borderColor = '#dc3545';
            cantidadValida = false;
            return;
        }
        
        // Validar que el ID no esté vacío
        if (!idMaterial || idMaterial === '') {
            console.error('ERROR: ID de material vacío para el item', item.id);
            alert('Error: No se pudo obtener el ID del material. Revisa el error_log.');
            cantidadValida = false;
            return;
        }
        
        materiales.push({
            id: idMaterial,
            cantidad: cantidad
        });
        
        console.log('Material agregado:', {id: idMaterial, cantidad: cantidad});
    });
    
    if (!cantidadValida) {
        return;
    }
    
    // Validar que haya al menos un material
    if (materialesActivos === 0 || materiales.length === 0) {
        alert('Debe haber al menos un material en la solicitud para aprobar.');
        return;
    }
    
    console.log('Total de materiales a aprobar:', materiales.length);
    console.log('Materiales:', materiales);
    
    if (confirm('¿Está seguro de aprobar esta solicitud con ' + materialesActivos + ' material(es)?')) {
        // Mostrar indicador de carga
        const btnAprobar = document.querySelector('.btn-success');
        const btnRechazar = document.querySelector('.btn-danger');
        btnAprobar.disabled = true;
        btnRechazar.disabled = true;
        btnAprobar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
        
        // Crear formulario dinámico para enviar datos (igual que Generar_Solicitud.php)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'detalle_espera.php';
        
        // Campo oculto para acción
        const inputAccion = document.createElement('input');
        inputAccion.type = 'hidden';
        inputAccion.name = 'accion';
        inputAccion.value = 'aprobar';
        form.appendChild(inputAccion);
        
        // Campo oculto para ID de solicitud
        const inputIdSolicitud = document.createElement('input');
        inputIdSolicitud.type = 'hidden';
        inputIdSolicitud.name = 'id_solicitud';
        inputIdSolicitud.value = idSolicitud;
        form.appendChild(inputIdSolicitud);
        
        // Campo para comentario (detalle)
        const inputDetalle = document.createElement('input');
        inputDetalle.type = 'hidden';
        inputDetalle.name = 'detalle';
        inputDetalle.value = comentario;
        form.appendChild(inputDetalle);
        
        // Agregar materiales al formulario (igual que Generar_Solicitud.php)
        // Formato: materiales[0][id] y materiales[0][cantidad]
        materiales.forEach((material, index) => {
            const inputIdMaterial = document.createElement('input');
            inputIdMaterial.type = 'hidden';
            inputIdMaterial.name = `materiales[${index}][id]`;
            inputIdMaterial.value = material.id;
            form.appendChild(inputIdMaterial);
            
            const inputCantidad = document.createElement('input');
            inputCantidad.type = 'hidden';
            inputCantidad.name = `materiales[${index}][cantidad]`;
            inputCantidad.value = material.cantidad;
            form.appendChild(inputCantidad);
        });
        
        // Agregar formulario al body y enviarlo
        document.body.appendChild(form);
        console.log('Enviando formulario...');
        form.submit();
    } else {
        console.log('Aprobación cancelada por el usuario');
    }
}

// Función para inicializar eventos del modal
function inicializarEventosModal() {
    console.log('Inicializando eventos del modal...');
    
    // Event listeners para validar cantidades
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
    
    // Funcionalidad para quitar materiales
    document.querySelectorAll('.quitar-material').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const index = this.getAttribute('data-index');
            const materialItem = document.getElementById('material-item-' + index);
            
            if (!materialItem) {
                console.error('No se encontró el elemento material-item-' + index);
                return;
            }
            
            const nombreMaterial = materialItem.querySelector('.material-nombre').value;
            
            if (confirm('¿Está seguro de quitar "' + nombreMaterial + '" de la solicitud?')) {
                // Ocultar el elemento con animación
                materialItem.style.transition = 'all 0.3s ease';
                materialItem.style.opacity = '0';
                materialItem.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    materialItem.style.display = 'none';
                    materialItem.classList.add('material-removido');
                }, 300);
            }
        });
    });
    
    console.log('Eventos del modal inicializados correctamente');
}

function cambiarEstadoModal(idSolicitud, estado) {
    const comentario = document.getElementById('comentarioSupervisor').value;
    
    // Solo para aprobar (estado 2)
    if (estado !== 2) {
        return;
    }
    
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
    
    if (confirm('¿Está seguro de aprobar esta solicitud?')) {
        // Mostrar indicador de carga
        const btnAprobar = document.querySelector('.btn-success');
        btnAprobar.disabled = true;
        btnAprobar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
        
        // Aquí puedes implementar la lógica de aprobar con AJAX o formulario
        alert('Funcionalidad de aprobar en desarrollo');
        btnAprobar.disabled = false;
        btnAprobar.innerHTML = '<i class="bi bi-check-circle"></i> Aprobar Solicitud';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ver-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const modalBody = document.querySelector('#detalleModal .modal-body');
            
            // Mostrar spinner
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            `;
            
            // Abrir modal
            let modal = new bootstrap.Modal(document.getElementById('detalleModal'));
            modal.show();

            // Cargar contenido
            fetch('detalle_espera.php?id=' + id)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta');
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                    
                    // Inicializar event listeners después de cargar el contenido
                    inicializarEventosModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                        <div class='alert alert-danger'>
                            <i class='bi bi-exclamation-triangle'></i> 
                            Error al cargar el detalle. Por favor, intente nuevamente.
                        </div>
                    `;
                });
        });
    });
});
</script>
</body>
</html>