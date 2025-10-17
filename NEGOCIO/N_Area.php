<?php
require_once __DIR__ . '/../DATOS/D_Area.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Area {

    // Método para adicionar a_descripcion
    public function adicionar( $a_nombre, $a_descripcion) {
        try {
            $NArea = new D_Area();
            $resultado = $NArea->Adicionar( $a_nombre, $a_descripcion);  // Llamar al método de D_Area
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar todos las areas
    public function obtenerAreas() {
        $NArea = new D_Area();
        return $NArea->ObtenerAreas();
    }

    // Método para eliminar un área por ID
    public function eliminar($id_area) {
        try {
            $NArea = new D_Area();
            $resultado = $NArea->Eliminar($id_area);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar áreas por similitud de término
    public function buscarPorSimilitud($termino) {
        $NArea = new D_Area();
        return $NArea->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Area
    }

    // Método para modificar un area
    public function modificar($id_area, $a_nombre, $a_descripcion) {
        try {
            $NArea = new D_Area();
            $resultado = $NArea->modificar($id_area, $a_nombre, $a_descripcion);  // Llamar al método modificar de D_Area
            return ['success' => (bool)$resultado['success']]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_area) {
        $NArea = new D_Area();
        return $NArea->buscarPorId($id_area);
    }
    
    
    
   
    
}
?>
