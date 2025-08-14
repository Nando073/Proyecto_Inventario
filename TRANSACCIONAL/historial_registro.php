<?php 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <main class="container mt-5">
        <h3 class="mb-4">Historial de Ingresos</h3>

        <ul class="list-group">
            <?php foreach ($ingresosAgrupados as $id_ingreso => $detalles): ?>
                <li class="list-group-item mb-3">
                    <h5>ID Ingreso: <?php echo htmlspecialchars($id_ingreso); ?></h5>
                    <h5>Fecha: <?php echo htmlspecialchars($detalle['i_fecha']); ?></h5>

                    <table class="table table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>ID Detalle</th>
                                <th>Proveedor</th>
                                <th>Material</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalIngreso = 0;
                            foreach ($detalles as $detalle): 
                                $totalIngreso += $detalle['sub_total'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['id_d_ingreso']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['proveedor_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['material_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['precio']); ?> Bs.</td>
                                <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['sub_total']); ?> Bs.</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total del Ingreso</th>
                                <th colspan="2"><?php echo number_format($totalIngreso, 2); ?> Bs.</th>
                            </tr>
                        </tfoot>
                    </table>

                </li>
            <?php endforeach; ?>
        </ul>
        <a href="Ingreso.php"><button type="button" class="btn btn-info">Volver</button></a>
    </main>
</body>
</html>