
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); verificar errores


require_once '../Seguridad.php';
verificarAcceso(['Administrador','Supervisor', 'Funcionario', 'Operador']);
require_once '../NEGOCIO/N_Egreso.php';
require_once '../NEGOCIO/N_Material.php';
// Instanciar el servicio de egreso
$egresoService = new N_Egreso();
$materialService = new N_Egreso();

// Obtener materiales agrupados por categoría
$materiales = $materialService->obtenerStockTotalPorMaterial();

// Agrupar materiales por categoría
$materialesPorCategoria = [];
foreach ($materiales as $mat) {
    $cat = $mat['c_nombre'];
    if (!isset($materialesPorCategoria[$cat])) {
        $materialesPorCategoria[$cat] = [];
    }
    $materialesPorCategoria[$cat][] = $mat;
}


// PROCESAR POST DEL EGRESO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_solicitud'])) {
    // 1. Obtener datos del formulario
    $codigoSolicitud = trim($_POST['codigo_solicitud']);
    $materiales = $_POST['materiales'] ?? [];

    // 2. Obtener el id_funcionario (de la sesión, deberías guardarlo al iniciar sesión)
    $id_funcionario = isset($_SESSION['id_funcionario']) ? $_SESSION['id_funcionario'] : null;

    if (!$id_funcionario) {
        echo "<script>alert('No se pudo identificar al usuario.');</script>";
        exit();
    }

    // 3. Calcular el total de cantidades y armar detalles
    $e_total_cantidad = 0;
    $detalles = [];
    foreach ($materiales as $mat) {
        $id_material = isset($mat['id']) ? intval($mat['id']) : 0;
        $cantidad = isset($mat['cantidad']) ? intval($mat['cantidad']) : 0;
        if ($id_material > 0 && $cantidad > 0) {
            $e_total_cantidad += $cantidad;
            $detalles[] = [
                'id_material_e' => $id_material,
                'e_stock' => $cantidad
            ];
        }
    }

    if (empty($detalles)) {
        echo "<script>alert('Debe añadir al menos un material válido.');</script>";
        exit();
    }

    // 4. Registrar el egreso usando la capa de negocio
    try {
    $mensaje = $egresoService->registrarEgresoCompleto($id_funcionario, $codigoSolicitud, $e_total_cantidad, $detalles);
    echo "<script>
        alert('¡Egreso registrado correctamente!\\n$mensaje');
        window.location.href='Egreso.php';
    </script>";
    exit();
} catch (Exception $e) {
    echo "<script>alert('Error al registrar el egreso: " . htmlspecialchars($e->getMessage()) . "');</script>";
}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Egreso de Materiales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            padding-top: 100px;
            background-color: #f8f9fa;
        }
        .material-card {
            margin: 10px 0;
        }
        .category-title {
            margin: 30px 0 10px 0;
            font-weight: bold;
            font-size: 1.5rem;
            border-left: 5px solid #007bff;
            padding-left: 10px;
        }
        .navbar {
            background-color: rgb(65, 180, 136);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            margin-right: 10px;
        }
        samp {
            font-family: "Poppins", sans-serif; 
            font-weight: bold; 
            font-size: 20px; 
            color: rgb(255, 255, 255); 
            letter-spacing: 2px; 
        }
        
        /* Botón de menú responsivo */
        .menu-back-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            cursor: pointer;
        }
        
        /* Menú desplegable */
        .dropdown-menu-custom {
            min-width: 200px;
        }
        
        /* Mejoras responsivas */
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .navbar-brand span {
                font-size: 1rem;
            }
            
            .navbar-brand img {
                width: 50px;
                height: 50px;
            }
            
            .category-title {
                font-size: 1.2rem;
                margin-left: 10px;
            }
            
            .btn-responsive {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }
            
            /* Ocultar texto en móviles pequeños */
            @media (max-width: 480px) {
                .navbar-brand span {
                    display: none;
                }
                
                .search-text {
                    display: none;
                }
            }
        }
        
        /* Cards responsivas */
        .card {
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-light">
<header>
  <nav class="navbar fixed-top shadow">
    <div class="container-fluid align-items-center d-flex">
      
      <!-- Botón de menú desplegable -->
      <div class="dropdown">
        <button class="menu-back-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" >
          <i class="bi bi-arrow-left"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-custom">
          <li><a class="dropdown-item" href="../PRESENTACION/ADM_Usuario.php"><i class="bi bi-people"></i> Administrar Usuarios</a></li>
          <li><a class="dropdown-item" href="../PRESENTACION/ADM_Material.php"><i class="bi bi-box-seam"></i> Administrar Materiales</a></li>
          <li><a class="dropdown-item" href="../TRANSACCIONAL/Ingreso.php"><i class="bi bi-download"></i> Ingreso de Materiales</a></li>
          <li><a class="dropdown-item" href="../REPORTES/Stock.php"><i class="bi bi-graph-up"></i> Reportes de Stock</a></li>
        </ul>
      </div>

      <!-- Logo y título -->
      <a class="navbar-brand" href="#">
        <img src="../IMG/LOGODDE.png" alt="Logo" width="80" height="80" class="d-inline-block align-text-top">
        <span>D.D.E.</span>
      </a>

      <!-- Formulario de búsqueda responsivo -->
      <form class="d-flex flex-grow-1 mx-2" role="search">
        <input class="form-control me-2" type="search" placeholder="Buscar material..." aria-label="Search">
        <!-- <button class="btn btn-outline-success btn-responsive search-text" type="submit">Buscar</button> -->
        <button class="btn btn-outline-secondary d-none d-sm-block" type="submit">
          <i class="bi bi-search"></i>
        </button>
      </form>

      <!-- Botón del carrito -->
      <button type="button" class="btn btn-outline-light me-2 me-md-3 position-relative btn-responsive" data-bs-toggle="modal" data-bs-target="#modalCarrito">
        <i class="bi bi-cart"></i> <span class="d-none d-md-inline">Carrito</span>
        <span id="badgeCarrito" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.8em; display:none;">
          0
        </span>
      </button>

      <!-- Usuario responsivo -->
      <div class="dropdown">
        <a class="navbar-brand d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($nombreUsuario); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
        </ul>
      </div>

    </div>
  </nav>
</header>

<div class="container my-4">
    <h2 class="text-center mb-4">📦 Egreso de Materiales</h2>
    <ul class="list-unstyled">
        <?php foreach ($materialesPorCategoria as $categoria => $items): ?>
            <li class="mb-4">
                <div class="category-title"><?php echo htmlspecialchars($categoria); ?></div>
                <div class="row">
                    <?php foreach ($items as $material): ?>
                        <div class="col-12 col-sm-6 col-lg-4 mb-3">
                            <div class="card material-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($material['m_nombre']); ?></h5>
                                    <p class="card-text">Stock: <?php echo htmlspecialchars($material['stock_total'] . ' ' . $material['u_medida'] ); ?></p>
                                    <div class="mb-2">
                                        <label for="cantidad_<?php echo $material['id_material']; ?>" class="form-label">Cantidad a egresar:</label>
                                        <input type="number" min="1" max="<?php echo htmlspecialchars($material['stock_total']); ?>" 
                                            name="cantidad[<?php echo $material['id_material']; ?>]" 
                                            id="cantidad_<?php echo $material['id_material']; ?>" 
                                            class="form-control" placeholder="Cantidad">
                                    </div>
                                    <div class="mt-auto">
                                        <button type="button" class="btn btn-primary btn-sm agregar-egreso w-100 btn-responsive" 
                                            data-id="<?php echo $material['id_material']; ?>">
                                            Añadir al carrito
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Modal para el carrito de egreso -->
<div class="modal fade" id="modalCarrito" tabindex="-1" aria-labelledby="modalCarritoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modalCarritoLabel">Generar Egreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <form id="formEgreso" method="post" action="Egreso.php">
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
              <label class="form-label fw-bold">Usuario:</label>
              <input type="text" class="form-control" name="nombre_usuario" value="<?php echo htmlspecialchars($nombreUsuario); ?>" readonly>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold">Código de Solicitud:</label>
              <input type="text" class="form-control" name="codigo_solicitud" placeholder="Ingrese código de solicitud" required>
            </div>
          </div>
          
          <!-- Lista de materiales añadidos al carrito -->
          <div class="table-responsive">
            <div id="carrito-materiales">
              <!-- Aquí se agregan dinámicamente las filas de materiales -->
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col-12 col-md-6 fw-bold d-flex align-items-center justify-content-center justify-content-md-start mb-2 mb-md-0">
              Total de materiales añadidos:
            </div>
            <div class="col-12 col-md-6">
              <input type="text" id="totalMateriales" class="form-control" readonly value="0">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-responsive" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary btn-responsive">Generar Egreso</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Tu código JavaScript existente permanece igual
document.addEventListener('DOMContentLoaded', function() {
    const carrito = [];
    const carritoMateriales = document.getElementById('carrito-materiales');
    const totalMateriales = document.getElementById('totalMateriales');

    // Añadir material al carrito
    document.querySelectorAll('.agregar-egreso').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nombre = this.closest('.card-body').querySelector('.card-title').textContent;
            const categoria = this.closest('.card').parentElement.parentElement.parentElement.querySelector('.category-title').textContent;
            const stock = this.closest('.card-body').querySelector('input[type="number"]').max;
            const cantidadInput = document.getElementById('cantidad_' + id);
            const cantidad = cantidadInput ? cantidadInput.value : '';

            if (!cantidad || cantidad <= 0) {
                alert('Ingrese una cantidad válida para "' + nombre + '"');
                return;
            }
            if (parseInt(cantidad) > parseInt(stock)) {
                alert('No puede egresar más que el stock disponible.');
                return;
            }
            // Evitar duplicados
            if (carrito.find(item => item.id === id)) {
                alert('Este material ya está en el carrito.');
                return;
            }
            carrito.push({id, nombre, categoria, cantidad});
            // Efecto: botón rojo y desactivado
            this.classList.remove('btn-primary');
            this.classList.add('btn-danger');
            this.disabled = true;
            this.textContent = 'Agregado';
            renderCarrito();
            actualizarBadgeCarrito();
        });
    });

    // Renderizar el carrito en el modal
    function renderCarrito() {
        carritoMateriales.innerHTML = '';
        let sumaTotal = 0;
        carrito.forEach((item, idx) => {
            sumaTotal += parseInt(item.cantidad) || 0;
            const row = document.createElement('div');
            row.className = 'row align-items-center mb-2';
            row.innerHTML = `
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="hidden" name="materiales[${idx}][id]" value="${item.id}">
                    <input type="text" class="form-control" value="${item.nombre}" readonly>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="text" class="form-control" value="${item.categoria}" readonly>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="number" class="form-control" name="materiales[${idx}][cantidad]" value="${item.cantidad}" readonly>
                </div>
                <div class="col-12 col-md-2 text-center">
                    <button type="button" class="btn btn-danger btn-sm quitar-material btn-responsive" data-idx="${idx}">X</button>
                </div>
            `;
            carritoMateriales.appendChild(row);
        });
        totalMateriales.value = sumaTotal;

        // Botón para quitar material
        document.querySelectorAll('.quitar-material').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.dataset.idx;
                const id = carrito[idx].id;
                carrito.splice(idx, 1);
                // Reactivar el botón en la card
                const btnCard = document.querySelector('.agregar-egreso[data-id="' + id + '"]');
                if (btnCard) {
                    btnCard.classList.remove('btn-danger');
                    btnCard.classList.add('btn-primary');
                    btnCard.disabled = false;
                    btnCard.textContent = 'Añadir al carrito';
                }
                renderCarrito();
                actualizarBadgeCarrito();
            });
        });
        actualizarBadgeCarrito();
    }

    // Actualizar badge del carrito
    function actualizarBadgeCarrito() {
        const badge = document.getElementById('badgeCarrito');
        let sumaTotal = 0;
        carrito.forEach(item => sumaTotal += parseInt(item.cantidad) || 0);
        if (sumaTotal > 0) {
            badge.textContent = sumaTotal;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // Validación antes de enviar el egreso
    document.getElementById('formEgreso').addEventListener('submit', function(e) {
        if (carrito.length === 0) {
            alert('Debe añadir al menos un material al carrito.');
            e.preventDefault();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>