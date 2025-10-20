<?php
require_once '../Seguridad.php';
verificarAcceso(['Administrador', 'Operador']);
require_once '../NEGOCIO/N_Categoria.php';
$categoriaService = new N_Categoria();

// Inicializar variable para edición
$categoria = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_categoria'])) {
    $id_categoria = filter_input(INPUT_GET, 'id_categoria', FILTER_VALIDATE_INT);
    if ($id_categoria) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            try {
                $resultado = $categoriaService->eliminar($id_categoria);

                if ($resultado['success']) {
                    $_SESSION['mensaje'] = "Categoría eliminada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "No se puede eliminar la categoría porque tiene materiales asociados.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
                
                header('Location: ADM_Categoria.php');
                exit();

            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al eliminar categoría: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ADM_Categoria.php');
                exit();
            }
        } else {
            // Modo edición
            $categoria = $categoriaService->buscarPorId($id_categoria);
            if (!$categoria) {
                $_SESSION['mensaje'] = "No se encontró la categoría.";
                $_SESSION['tipo_mensaje'] = "warning";
            }
        }
    } else {
        $_SESSION['mensaje'] = "ID inválido.";
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// Manejo de creación/actualización vía POST
$accion = $_POST['accion'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'crear') {
        $c_nombre = trim(strip_tags($_POST['c_nombre'] ?? ''));
        $c_descripcion = trim(strip_tags($_POST['c_descripcion'] ?? ''));
        //$c_materiales = filter_input(INPUT_POST, 'c_materiales', FILTER_VALIDATE_INT);
        if ($c_nombre && $c_descripcion!== false) {
            try {
                $resultado = $categoriaService->adicionar($c_nombre, $c_descripcion);
                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Categoría creada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al registrar categoría. ya existe.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al crear categoría: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
            header('Location: ADM_Categoria.php');
            exit();
        } 
    } elseif ($accion === 'guardar') {
        $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
        $c_nombre = trim(strip_tags($_POST['c_nombre'] ?? ''));
        $c_descripcion = trim(strip_tags($_POST['c_descripcion'] ?? ''));
       //$c_materiales = filter_input(INPUT_POST, 'c_materiales', FILTER_VALIDATE_INT);
        if ($id_categoria && $c_nombre && $c_descripcion !== false) {
            try {
                $resultado = $categoriaService->modificar($id_categoria, $c_nombre, $c_descripcion);
                if (isset($resultado['success']) && $resultado['success'] == 1) {
                    $_SESSION['mensaje'] = "Categoría actualizada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } 
            } catch (Exception $e) {
                $_SESSION['mensaje'] = "Error al actualizar categoría: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
                header('Location: ADM_Categoria.php');
                exit();
        }
    }
}

// Listado y búsqueda
$categorias = $categoriaService->obtenerCategorias();
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $categorias = $categoriaService->buscarPorSimilitud($searchTerm);
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
    <title>Administrar Catedorias</title>
</head>
<body>
<?php include '../DEMO/index.php'; ?>

<main>
    <div class="card mb-4 mx-auto" style="max-width: 540px;">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/categoria.jpeg" class="img-fluid rounded-start w-100 h-auto">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title h5 h4-md">CATEGORIAS</h4>
                    <h3 class="card-text h6 h3-md"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="categoriaModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Formulario Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formMaterial" action="ADM_Categoria.php" method="post">
                        <input type="hidden" name="id_categoria" id="id_categoria" value="<?php echo isset($categoria) ? $categoria['id_categoria'] : ''; ?>">

                        <div class="form-group">
                            <label for="c_nombre">Nombre</label>
                            <input type="text" class="form-control" id="c_nombre" name="c_nombre" value="<?php echo isset($categoria) ? htmlspecialchars($categoria['c_nombre']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="c_descripcion">Descripción</label>
                            <textarea class="form-control" id="c_descripcion" name="c_descripcion" required><?php echo isset($categoria) ? htmlspecialchars($categoria['c_descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="mt-3">
                            <button type="submit" name="accion" value="crear" class="btn btn-primary" style="<?php echo isset($categoria) ? 'display:none;' : ''; ?>">Crear Categoria</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" style="<?php echo isset($categoria) ? '' : 'display:none;'; ?>">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Categorias</h3>
    <form class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mt-3 gap-2" action="ADM_Categoria.php" method="get">
        <div class="d-flex flex-grow-1 me-md-2">
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2"/>
            <button type="submit" class="btn btn-info flex-shrink-0">Buscar</button>
        </div>
        <button type="button" class="btn btn-success" id="btnCrearcategoria" data-bs-toggle="modal" data-bs-target="#categoriaModal">
            Registrar Categoria
        </button>
    </form>

<!-- Mensaje -->
<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> mt-3">
        <?= htmlspecialchars($_SESSION['mensaje']); ?>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
<?php endif; ?>
<div class="table-responsive">
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Cantidad de materiales</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $cate): ?>
            <tr>
                <td><?php echo htmlspecialchars($cate['c_nombre']); ?></td>
                <td><?php echo htmlspecialchars($cate['c_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($cate['c_materiales']); ?></td>
                <td>
                    <div class="d-flex flex-column flex-md-row gap-1">
                        <a href="ADM_Categoria.php?id_categoria=<?php echo $cate['id_categoria']; ?>" class="btn btn-warning">Editar</a>
                        <a href="ADM_Categoria.php?id_categoria=<?php echo $cate['id_categoria']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoria?');">Eliminar</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</main>

<?php if (isset($categoria)): ?>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('categoriaModal'));
    window.addEventListener('load', () => {
        myModal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById("btnCrearcategoria").addEventListener("click", function () {
    const form = document.getElementById("formMaterial");

    // Limpiar todos los inputs
    form.querySelectorAll("input, textarea").forEach(input => {
        input.value = "";
    });

    // Eliminar campo oculto de id si existe
    const idInput = document.getElementById("id_categoria");
    if (idInput) idInput.remove();

    // Desactiva el botón de guardar
    const btnGuardar = form.querySelector('button[name="accion"][value="guardar"]');
    if (btnGuardar) btnGuardar.style.display = "none";

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.style.display = "inline-block";
});
</script>

</body>
</html>
