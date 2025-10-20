<?php
require_once __DIR__ . '/../DATOS/D_U_Medida.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';

class N_U_Medida {

    // Método para adicionar una unidad de medida
    public function adicionar($u_medida, $u_descripcion) {
        try{
            $NMedida = new D_U_Medida(); 
            $resultado = $NMedida->Adicionar($u_medida, $u_descripcion);
            return ['success' => (bool) $resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar todas las unidades de medida
    public function obtenerMedidas() {
        $NMedida = new D_U_Medida();
        return $NMedida->ObtenerMedidas();
    }

   // Método para eliminar una unidad de medida por ID
public function eliminar($id_medida) {
    try {
        $NMedida = new D_U_Medida();
        $resultado = $NMedida->Eliminar($id_medida);
        return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
    } catch (Exception $e) {
        throw $e;
    }
}

    // Método para buscar unidades de medida por similitud de término
    public function buscarPorSimilitud($termino) {
        $NMedida = new D_U_Medida();
        return $NMedida->buscarPorSimilitud($termino);
    }

    // Método para modificar una unidad de medida
    public function modificar($id_medida, $u_medida, $u_descripcion) {
        try {
            $NMedida = new D_U_Medida();
            $resultado = $NMedida->modificar($id_medida, $u_medida, $u_descripcion);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar una unidad de medida por ID
    public function buscarPorId($id_medida) {
        $NMedida = new D_U_Medida();
        return $NMedida->buscarPorId($id_medida);
    }

}
?>
