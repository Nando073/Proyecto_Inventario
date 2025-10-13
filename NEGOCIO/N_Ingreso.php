<?php
require_once __DIR__ . '/../DATOS/D_Ingreso.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Ingreso {

    // Método para adicionar un ingreso

    public function registrarIngresoCompleto($id_proveedor, $total, $detalles) {
        $NIngreso = new D_Ingreso();
        return $NIngreso->RegistrarIngresoConDetalles($id_proveedor, $total, $detalles);
    }

          
    // Método para buscar todos los ingresos
    public function ObtenerIngresosRegistrado() {
        $NIngreso = new D_Ingreso();
        return $NIngreso->ObtenerIngresosRegistrado();
    }
    public function ObtenerDetallesIngresos() {
        $NIngreso = new D_Ingreso();
        return $NIngreso->ObtenerDetallesIngresos();
    }

    // Método para buscar ingreso por similitud de término
    public function buscarPorSimilitud($termino) {
    $NIngreso = new D_Ingreso();
    return $NIngreso->buscarPorSimilitud($termino);
}

    // Método para eliminar un ingreso por ID
    public function eliminarIngreso($id_ingreso) {
        try {
            $NIngreso = new D_Ingreso();
            $resultado = $NIngreso->eliminarIngreso($id_ingreso);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function buscarHistorialIngreso($material, $proveedor, $fecha_inicio, $fecha_fin) {
        $NIngreso = new D_Ingreso();
        return $NIngreso->BuscarHistorialIngreso($material, $proveedor, $fecha_inicio, $fecha_fin);
    }
    
}
?>
