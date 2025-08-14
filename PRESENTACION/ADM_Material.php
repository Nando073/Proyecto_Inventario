<?php
require_once '../NEGOCIO/N_Material.php';
$materialService = new N_Material();

/// Verifica si se pasa un ID en la URL para editar o eliminar
$material = null;

if (isset($_GET['id_material'])) {
    $material_id = filter_input(INPUT_GET, 'id_material', FILTER_VALIDATE_INT);

    if ($material_id) {
        // Crear una instancia del servicio de negocio
        $materialService = new N_Material();
        
        // Verifica si se ha solicitado eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            // Llamada al método de negocio para eliminar al material
            $materialService->eliminar($material_id);
            // Redirigir al listado después de eliminar
            header('Location: ADM_Material.php');
            exit();
        } else {
            // Llamada al método de negocio para obtener los datos de los materiales
            $material = $materialService->buscarPorId($material_id);
            if (!$material) {
                echo "No se encontró el material.";
            }
        }
    } else {
        echo "ID inválido.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_material = filter_input(INPUT_POST, 'id_material', FILTER_VALIDATE_INT);
    $m_nombre = trim(filter_input(INPUT_POST, 'm_nombre', FILTER_SANITIZE_STRING));
    $m_descripcion = trim(filter_input(INPUT_POST, 'm_descripcion', FILTER_SANITIZE_STRING));
    $id_categoria = trim(filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT));
    $id_medida = trim(filter_input(INPUT_POST, 'id_medida', FILTER_VALIDATE_INT));
    //$stock = trim(filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_STRING));
    $accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING);

    //var_dump($m_nombre, $m_descripcion, $id_categoria, $id_medida, $accion);
    if ($m_nombre && $m_descripcion && $id_categoria && $id_medida && $accion) {
        $existingMaterial = $materialService->buscarPorId($id_material);

        if ($accion === 'crear') {
            if ($existingMaterial) {
                echo "Error: El material con el ID $id_material ya existe. No se puede crear.";
            } else {
                $materialService->adicionar( $m_nombre, $m_descripcion, $id_categoria, $id_medida);
                header('Location: ADM_Material.php');
                exit();
            }
        } elseif ($accion === 'guardar') {
            if ($existingMaterial) {
                $materialService->modificar($id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida);
                header('Location: ADM_Material.php');
                exit();
            } else {
                echo "Error: El material con el ID $id_material no existe. No se puede modificar.";
            }
        } else {
            echo "Error: Acción no válida.";
        }
    } else {
        echo "Error: Todos los campos son necesarios y deben ser válidos.";
    }
}

// Obtener la lista de materiales
$materiales = $materialService->buscarTodo();
//obtener la lista de categorías y medidas
$categorias = $materialService->obtenerCategorias();
$medidas = $materialService->obtenerMedidas();
// Buscar por término
$searchTerm = isset($_GET['search']) ? filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) : '';
if ($searchTerm) {
    $materiales = $materialService->buscarPorSimilitud($searchTerm);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../DEMO/styles.css?v=<?php echo(rand()); ?>"> 
    <script src="../DEMO/contrarer.js" defer></script>
    <title>Administrar Materiales</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <div class="card mb-4" style="max-width: 540px; margin-left: 60vh">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/material.jpg" class="img-fluid rounded-start">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title">MATERIALES</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Crear o Editar Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMaterial" action="ADM_Material.php" method="post">
                        <input type="hidden" name="id_material" id="id_material" value="<?php echo isset($material) ? $material['id_material'] : ''; ?>">

                        <div class="form-group">
                            <label for="m_nombre">Nombre</label>
                            <input type="text" class="form-control" id="m_nombre" name="m_nombre" value="<?php echo isset($material) ? htmlspecialchars($material['m_nombre']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="m_descripcion">Descripción</label>
                            <textarea class="form-control" id="m_descripcion" name="m_descripcion" required><?php echo isset($material) ? htmlspecialchars($material['m_descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="id_categoria">ID Categoría</label>
                            <select name="id_categoria" id="id_categoria" class="form-control" required>
                                <option value="">Seleccione una categoria</option>
                                <?php
                                    // Llenar el select con las categorías obtenidas
                                    foreach ($categorias as $categoria) {
                                        // Si estamos editando un material, seleccionamos la categoría previamente asignada
                                        $selected = (isset($material) && $material['id_categoria'] == $categoria['id_categoria']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($categoria['id_categoria']) . "' $selected>" . htmlspecialchars($categoria['c_nombre']) . "</option>";
                                    }
                                ?>
                            </select>                       
                        </div>

                        <div class="form-group">
                            <label for="id_medida">Unidad de Medida</label>
                            <select name="id_medida" id="id_medida" class="form-control" required>
                                <option value="">Seleccione una unidad de medida</option>
                                <?php
                                    // Llenar el select con las unidades de medida obtenidas
                                    foreach ($medidas as $medida) {
                                        // Si estamos editando un material, seleccionamos la unidad de medida previamente asignada
                                        $selected = (isset($material) && $material['id_medida'] == $medida['id_medida']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($medida['id_medida']) . "' $selected>" . htmlspecialchars($medida['u_medida']) . "</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Material</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($material) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Materiales</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Material.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearMaterial" data-bs-toggle="modal" data-bs-target="#materialModal">
            Registrar Material
        </button>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Unidad de Medida</th>
                <th>Stock</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($materiales as $mat): ?>
            <tr>
                <td><?php echo htmlspecialchars($mat['id_material']); ?></td>
                <td><?php echo htmlspecialchars($mat['m_nombre']); ?></td>
                <td><?php echo htmlspecialchars($mat['m_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($mat['c_nombre']); ?></td>
                <td><?php echo htmlspecialchars($mat['u_medida']); ?></td>
                <td><?php echo htmlspecialchars($mat['stock']); ?></td>
                <td><?php echo htmlspecialchars($mat['m_fecha']); ?></td>
                <td>
                    <a href="ADM_Material.php?id_material=<?php echo $mat['id_material']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_Material.php?id_material=<?php echo $mat['id_material']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este material?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php if (isset($material)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('materialModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearMaterial").addEventListener("click", function () {
    const form = document.getElementById("formMaterial");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_material");
    if (idInput) idInput.remove();

    // Desactiva el botón de guardar
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    if (btnGuardar) btnGuardar.disabled = true;

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.disabled = false;
});
</script>

</body>
</html>
