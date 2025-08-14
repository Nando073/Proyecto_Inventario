<?php 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <main class="container mt-5">
        <h3 class="mb-4">Historial de Egresos</h3>

        <ul class="list-group">
            <?php foreach ($egresosAgrupados as $id_egreso => $detalles): ?>
                <li class="list-group-item mb-3">
                    <h5>ID Egreso: <?php echo htmlspecialchars($id_egreso); ?></h5>
                    <h5>Fecha: <?php echo htmlspecialchars($detalles[0]['e_fecha']); ?></h5>
                    <h5>Funcionario: <?php echo htmlspecialchars($detalles[0]['funcionario_nombre']); ?></h5>
                    <h5>Código Solicitud: <?php echo htmlspecialchars($detalles[0]['e_solicitud']); ?></h5>

                    <table class="table table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>ID Detalle</th>
                                <th>Material</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalEgreso = 0;
                            foreach ($detalles as $detalle): 
                                $totalEgreso += $detalle['e_stock'];
                            ?>
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
                                <th colspan="3">Total del Egreso</th>
                                <th><?php echo number_format($totalEgreso); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="Egreso.php"><button type="button" class="btn btn-info">Volver</button></a>
    </main>
</body>
</html>