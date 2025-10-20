<?php
require_once __DIR__ . '/../DATOS/D_Cargo.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Cargo {

    // Método para adicionar descripcion
    public function adicionar( $nombre_c, $descripcion_c) {
        try {
            $NCargo = new D_Cargo();
            $resultado = $NCargo->Adicionar($nombre_c, $descripcion_c);  // Llamar al método de D_Cargo
            return ['success' => (bool) $resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
            
        }
       
    }

    // Método para buscar todos las cargos
    public function obtenerCargos() {
        $NCargo = new D_Cargo();
        return $NCargo->ObtenerCargos();
    }

    // Método para eliminar un cargo por ID
    public function eliminar($id_cargo) {
        try {
            $NCargo = new D_Cargo();
            $resultado = $NCargo->Eliminar($id_cargo);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }
    // Método para buscar cargos por similitud de término
    public function buscarPorSimilitud($termino) {
        $NCargo = new D_Cargo();
        return $NCargo->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Cargo
    }

    // Método para modificar un area
    public function modificar($id_cargo, $nombre_c, $descripcion_c) {
        try {
            $NCargo = new D_Cargo();
            $resultado = $NCargo->modificar($id_cargo, $nombre_c, $descripcion_c);  // Llamar al método modificar de D_Cargo
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_cargo) {
        $NCargo = new D_Cargo();
        return $NCargo->buscarPorId($id_cargo);
    }
    

    
   
    
}
?>
