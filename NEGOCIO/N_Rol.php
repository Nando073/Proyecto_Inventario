<?php
require_once __DIR__ . '/../DATOS/D_Rol.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Rol {

    // Método para adicionar r_descripcion
    public function adicionar( $r_nombre, $r_descripcion) {
        $NRol = new D_Rol(); 
        $NRol->Adicionar( $r_nombre, $r_descripcion);  // Llamar al método de D_Rol
    }

    // Método para buscar todos las areas
    public function buscarTodo() {
        $NRol = new D_Rol();
        return $NRol->BuscarTodo();
    }

    // Método para eliminar un rol por ID
    public function eliminar($id_rol) {
        $NRol = new D_Rol();
        $NRol->Eliminar($id_rol);  // Llamar al método Eliminar de D_Rol
    }

    // Método para buscar areas por similitud de término
    public function buscarPorSimilitud($termino) {
        $NRol = new D_Rol();
        return $NRol->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Rol
    }

    // Método para modificar un area
    public function modificar($id_rol, $r_nombre, $r_descripcion) {
        $NRol = new D_Rol();
        $NRol->modificar($id_rol, $r_nombre, $r_descripcion);  // Llamar al método modificar de D_Rol
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_rol) {
        $NRol = new D_Rol();
        return $NRol->buscarPorId($id_rol);
    }
    
    
   
    
}
?>
