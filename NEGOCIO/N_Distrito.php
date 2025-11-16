<?php
require_once __DIR__ . '/../DATOS/D_Distrito.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Distrito {

    // Método para adicionar d_descripcion
    public function adicionar( $d_nombre, $d_descripcion) {
        try {
            $NDistrito = new D_Distrito();
            $resultado = $NDistrito->Adicionar( $d_nombre, $d_descripcion);  // Llamar al método de D_Distrito
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar todos las areas
    public function obtenerDistritos() {
        $NDistrito = new D_Distrito();
        return $NDistrito->ObtenerDistritos();
    }

    // Método para eliminar un área por ID
    public function eliminar($id_distrito) {
        try {
            $NDistrito = new D_Distrito();
            $resultado = $NDistrito->Eliminar($id_distrito);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar áreas por similitud de término
    public function buscarPorSimilitud($termino) {
        $NDistrito = new D_Distrito();
        return $NDistrito->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Area
    }

    // Método para modificar un area
    public function modificar($id_distrito, $d_nombre, $d_descripcion) {
        try {
            $NDistrito = new D_Distrito();
            $resultado = $NDistrito->modificar($id_distrito, $d_nombre, $d_descripcion);  // Llamar al método modificar de D_Area
            return ['success' => (bool)$resultado['success']]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_distrito) {
        $NDistrito = new D_Distrito();
        return $NDistrito->buscarPorId($id_distrito);
    }
    
    // Método para obtener distritos disponibles y poder ser usado en distritales
    public function obtenerDistritosDisponibles($id_distrito_actual = null) {
        $NDistrito = new D_Distrito();
        return $NDistrito->ObtenerDistritosDisponibles($id_distrito_actual);
    }
    
    
    
   
    
}
?>
