<?php
require_once __DIR__ . '/../DATOS/D_Distrital.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';

class N_Distrital {

    // Método para adicionar distrital
    public function adicionar($di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento) {
        try {
            $Ndistrital = new D_Distrital(); 
            $resultado = $Ndistrital->Adicionar($di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento);  // Llamar al método de D_Distrital
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
        
    }
    

    // Método para buscar todos los distritales
    public function obtenerdistritales() {
        $Ndistrital = new D_Distrital();
        return $Ndistrital->ObtenerDistritales();
    }

    // Método para eliminar un distrital por ID
    public function eliminar($id_distrital) {
        try {
            $Ndistrital = new D_Distrital();
            $resultado = $Ndistrital->Eliminar($id_distrital);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        }catch (Exception $e){
            throw $e;
        }
    }

    // Método para buscar distritales por similitud de término
    public function buscarPorSimilitud($termino) {
        $Ndistrital = new D_Distrital();
        return $Ndistrital->buscarPorSimilitud($termino);
    }

    // Método para modificar un distrital
    public function modificar($id_distrital, $di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento) {
        try {
            $Ndistrital = new D_Distrital();
            $funcionarioExistente = $Ndistrital->buscarPorId($id_distrital);
            $resultado = $Ndistrital->modificar($id_distrital, $di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
        
    }

    // Método para buscar un distrital por ID
    public function buscarPorId($id_distrital) {
        $Ndistrital = new D_Distrital();
        return $Ndistrital->buscarPorId($id_distrital);
    }
    // Método para activar un distrital por ID
    public function activarDistrital($id_distrital) {
        $Ndistrital = new D_Distrital();
        return $Ndistrital->activarDistrital($id_distrital);
    }

    // public function obtenerDistritalesDisponibles($id_distrital = null) {
    //     $d = new D_Distrital();
    //     return $d->ObtenerDistritalesDisponibles($id_distrital);
    // }

    // public function obtenerDistritalDisp(){
    //     $d = new D_Distrital();
    //     return $d->ObtenerDistritalDisp();
    // }


}
?>
