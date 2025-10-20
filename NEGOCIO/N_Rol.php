<?php
require_once __DIR__ . '/../DATOS/D_Rol.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Rol {

    // Método para adicionar r_descripcion
    public function adicionar( $r_nombre, $r_descripcion) {
        try {
            $NRol = new D_Rol(); 
            $resultado = $NRol->Adicionar( $r_nombre, $r_descripcion);  // Llamar al método de D_Rol
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar todos las areas
    public function obtenerRoles() {
        $NRol = new D_Rol();
        return $NRol->ObtenerRoles();
    }

   // Método para eliminar un rol por ID
    public function eliminar($id_rol) {
        try {
            $NRol = new D_Rol();
            $resultado = $NRol->Eliminar($id_rol);
            return $resultado; // Devuelve el array completo
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar areas por similitud de término
    public function buscarPorSimilitud($termino) {
        $NRol = new D_Rol();
        return $NRol->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Rol
    }

    // Método para modificar un area
    public function modificar($id_rol, $r_nombre, $r_descripcion) {
        try {
            $NRol = new D_Rol();
            $resultado = $NRol->modificar($id_rol, $r_nombre, $r_descripcion);  // Llamar al método modificar de D_Rol
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
        
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_rol) {
        $NRol = new D_Rol();
        return $NRol->buscarPorId($id_rol);
    }
    
    
   
    
}
?>
