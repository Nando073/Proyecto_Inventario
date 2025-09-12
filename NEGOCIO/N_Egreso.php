<?php
require_once __DIR__ . '/../DATOS/D_Egreso.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Egreso {

    // Método para adicionar un egreso
    public function registrarEgresoCompleto($id_funcionario,$e_solicitud, $e_total_cantidad, $detalles) {
        $NEgreso = new D_Egreso();
        return $NEgreso->RegistrarEgresoConDetalles($id_funcionario,$e_solicitud, $e_total_cantidad, $detalles);
    }
    
    // Método para buscar todos los egresos
    public function ObtenerEgresosRegistrado() {
        $NEgreso = new D_Egreso();
        return $NEgreso->ObtenerEgresosRegistrado();
    }
    public function ObtenerDetallesEgresos() {
        $NEgreso = new D_Egreso();
        return $NEgreso->ObtenerDetallesEgresos();
    }

    public function buscarPorSimilitud($termino) {
    $NEgreso = new D_Egreso();
    return $NEgreso->buscarPorSimilitud($termino);
}

    
    public function eliminarEgreso($id_egreso) {
        $NEgreso = new D_Egreso();
        $NEgreso->eliminarEgreso($id_egreso);
    }  
    
    public function obtenerStockPorLote() {
        $NEgreso = new D_Egreso();
        return $NEgreso->ObtenerStockPorLote();
    }
    
    public function obtenerStockTotalPorMaterial() {
        $NEgreso = new D_Egreso();
        return $NEgreso->ObtenerStockTotalPorMaterial();
    }
    
    public function buscarStockPorLote($material, $proveedor, $fecha_inicio, $fecha_fin) {
        $NEgreso = new D_Egreso();
        return $NEgreso->BuscarStockPorLote($material, $proveedor, $fecha_inicio, $fecha_fin);
    }
}
?>
