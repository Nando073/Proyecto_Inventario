<?php
require_once __DIR__ . '/../DATOS/D_Cargo.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Cargo {

    // Método para adicionar descripcion
    public function adicionar( $nombre_c, $descripcion_c) {
        $NCargo = new D_Cargo(); 
        $NCargo->Adicionar( $nombre_c, $descripcion_c);  // Llamar al método de D_Cargo
    }

    // Método para buscar todos las cargos
    public function buscarTodo() {
        $NCargo = new D_Cargo();
        return $NCargo->BuscarTodo();
    }

    // Método para eliminar un area por ID
    public function eliminar($id_cargo) {
        $NCargo = new D_Cargo();
        $NCargo->Eliminar($id_cargo);  // Llamar al método Eliminar de D_Cargo
    }

    // Método para buscar cargos por similitud de término
    public function buscarPorSimilitud($termino) {
        $NCargo = new D_Cargo();
        return $NCargo->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Cargo
    }

    // Método para modificar un area
    public function modificar($id_cargo, $nombre_c, $descripcion_c) {
        $NCargo = new D_Cargo();
        $NCargo->modificar($id_cargo, $nombre_c, $descripcion_c);  // Llamar al método modificar de D_Cargo
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_cargo) {
        $NCargo = new D_Cargo();
        return $NCargo->buscarPorId($id_cargo);
    }
    
    public function actualizarCantidadFuncionarios() {
        $NCargo = new D_Cargo();
        $NCargo->actualizarCantidadFuncionarios();
    }
    
   
    
}
?>
