<?php
require_once __DIR__ . '/../DATOS/D_RolUsuario.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_RolUsuario {

    // Método para adicionar id_rolUsuario
    public function adicionar($id_rol, $id_usuario) {
        try {
            $NRolUsuario = new D_RolUsuario();
            $resultado = $NRolUsuario->Adicionar($id_rol, $id_usuario);  // Llamar al método de D_RolUsuario
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar todos los rolUsuario
    public function ObtenerRolUsuario() {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->obtenerRolUsuario();
    }
    // Método para eliminar un rolUsuario por ID
    public function eliminar($id_RolUsuario) {
        try {
        $NRolUsuario = new D_RolUsuario();
        $resultado = $NRolUsuario->Eliminar($id_RolUsuario);  // Llamar al método Eliminar de D_RolUsuario
        return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        }catch (Exception $e){
            throw $e;
        }
    }

    // Método para buscar rolUsuario por similitud de término
    public function buscarPorSimilitud($termino) {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_RolUsuario
    }

    // Método para modificar un rolUsuario
    public function modificar($id_RolUsuario, $id_rol, $id_usuario) {
        try {
            $NRolUsuario = new D_RolUsuario();
            $resultado = $NRolUsuario->modificar($id_RolUsuario, $id_rol, $id_usuario);  // Llamar al método modificar de D_RolUsuario
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar un rolUsuario por ID
    public function buscarPorId($id_RolUsuario) {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->buscarPorId($id_RolUsuario);
    }

  
    public function activarRolUsuario($id_RolUsuario) {
        $RolUsuario = new D_RolUsuario();
        return $RolUsuario->activarRolUsuario($id_RolUsuario);
    }
    public function obtenerRolUsuarioAsignado($id_usuario) {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->ObtenerRolUsuarioPorUsuarioAsignado($id_usuario);
    }
}
?>