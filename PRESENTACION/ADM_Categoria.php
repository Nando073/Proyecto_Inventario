<?php
require_once '../Seguridad.php';
require_once '../NEGOCIO/N_Categoria.php';
$categoriaService = new N_Categoria();

// Inicializar variable para edición
$categoria = null;

// Manejo de editar/eliminar vía GET
if (isset($_GET['id_categoria'])) {
    $id_categoria = filter_input(INPUT_GET, 'id_categoria', FILTER_VALIDATE_INT);
    if ($id_categoria) {
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $categoriaService->eliminar($id_categoria);
            header('Location: ADM_Categoria.php');
            exit();
        } else {
            $categoria = $categoriaService->buscarPorId($id_categoria);
            if (!$categoria) {
                echo "No se encontró la categoría.";
            }
        }
    } else {
        echo "ID inválido.";
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
            $categoriaService->adicionar($c_nombre, $c_descripcion);
            header('Location: ADM_Categoria.php');
            exit();
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    } elseif ($accion === 'guardar') {
        $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
        $c_nombre = trim(strip_tags($_POST['c_nombre'] ?? ''));
        $c_descripcion = trim(strip_tags($_POST['c_descripcion'] ?? ''));
       //$c_materiales = filter_input(INPUT_POST, 'c_materiales', FILTER_VALIDATE_INT);
        if ($id_categoria && $c_nombre && $c_descripcion !== false) {
            $existing = $categoriaService->buscarPorId($id_categoria);
            if ($existing) {
                $categoriaService->modificar($id_categoria, $c_nombre, $c_descripcion);
                header('Location: ADM_Categoria.php');
                exit();
            } else {
                echo "Error: No existe la categoría con ID $id_categoria.";
            }
        } else {
            echo "Error: Todos los campos son necesarios y válidos.";
        }
    }
}

// Listado y búsqueda
$categorias = $categoriaService->buscarTodo();
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
    <div class="card mb-4" style="max-width: 540px; margin-left: 60vh">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../IMG/categoria.jpeg" class="img-fluid rounded-start">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="card-title">CATEGORIAS</h4>
                    <h3 class="card-text"><small class="text-body-secondary">CRUD</small></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="categoriaModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialModalLabel">Crear o Editar Categoria</h5>
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
                            <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear Categoria</button>
                            <button type="submit" name="accion" value="guardar" class="btn btn-success" <?php echo isset($categoria) ? '' : 'disabled'; ?>>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Administrar Categorias</h3>
    <form class="d-flex justify-content-between align-items-center mt-3" action="ADM_Categoria.php" method="get">
        <div>
            <input type="text" name="search" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($searchTerm); ?>" />
            <button type="submit" class="btn btn-info">Buscar</button>
        </div>
        <button type="button" class="btn btn-success m-3" id="btnCrearcategoria" data-bs-toggle="modal" data-bs-target="#categoriaModal">
            Registrar Categoria
        </button>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Cantidad de materiales</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $cate): ?>
            <tr>
                <td><?php echo htmlspecialchars($cate['id_categoria']); ?></td>
                <td><?php echo htmlspecialchars($cate['c_nombre']); ?></td>
                <td><?php echo htmlspecialchars($cate['c_descripcion']); ?></td>
                <td><?php echo htmlspecialchars($cate['c_materiales']); ?></td>
                <td>
                    <a href="ADM_Categoria.php?id_categoria=<?php echo $cate['id_categoria']; ?>" class="btn btn-warning">Editar</a>
                    <a href="ADM_Categoria.php?id_categoria=<?php echo $cate['id_categoria']; ?>&action=delete" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoria?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../Acceso.php"><button type="button" class="btn btn-info">Acceso</button></a>
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
    if (btnGuardar) btnGuardar.disabled = true;

    // Activa el botón de crear
    const btnCrear = form.querySelector('button[name="accion"][value="crear"]');
    if (btnCrear) btnCrear.disabled = false;
});
</script>

</body>
</html>
