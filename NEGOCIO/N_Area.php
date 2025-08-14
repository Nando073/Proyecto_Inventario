<?php
require_once __DIR__ . '/../DATOS/D_Area.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Area {

    // Método para adicionar a_descripcion
    public function adicionar( $a_nombre, $a_descripcion) {
        $NArea = new D_Area(); 
        $NArea->Adicionar( $a_nombre, $a_descripcion);  // Llamar al método de D_Area
    }

    // Método para buscar todos las areas
    public function buscarTodo() {
        $NArea = new D_Area();
        return $NArea->BuscarTodo();
    }

    // Método para eliminar un area por ID
    public function eliminar($id_area) {
        $NArea = new D_Area();
        $NArea->Eliminar($id_area);  // Llamar al método Eliminar de D_Area
    }

    // Método para buscar ares por similitud de término
    public function buscarPorSimilitud($termino) {
        $NArea = new D_Area();
        return $NArea->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Area
    }

    // Método para modificar un area
    public function modificar($id_area, $a_nombre, $a_descripcion) {
        $NArea = new D_Area();
        $NArea->modificar($id_area, $a_nombre, $a_descripcion);  // Llamar al método modificar de D_Area
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_area) {
        $NArea = new D_Area();
        return $NArea->buscarPorId($id_area);
    }
    
    public function actualizarCantidadFuncionarios() {
        $NArea = new D_Area();
        $NArea->actualizarCantidadFuncionarios();
    }
    
   
    
}
?>
