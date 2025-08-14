<?php
require_once __DIR__ . '/../DATOS/D_RolUsuario.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_RolUsuario {

    // Método para adicionar id_rolUsuario
    public function adicionar($id_rol, $id_usuario) {
        $NRolUsuario = new D_RolUsuario();
        $NRolUsuario->Adicionar($id_rol, $id_usuario);  // Llamar al método de D_RolUsuario
    }

    // Método para buscar todos los rolUsuario
    public function buscarTodo() {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->BuscarTodo();
    }
    // Método para eliminar un rolUsuario por ID
    public function eliminar($id_RolUsuario) {
        $NRolUsuario = new D_RolUsuario();
        $NRolUsuario->Eliminar($id_RolUsuario);  // Llamar al método Eliminar de D_RolUsuario
    }

    // Método para buscar rolUsuario por similitud de término
    public function buscarPorSimilitud($termino) {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_RolUsuario
    }

    // Método para modificar un rolUsuario
    public function modificar($id_RolUsuario, $id_rol, $id_usuario) {
        $NRolUsuario = new D_RolUsuario();
        $NRolUsuario->modificar($id_RolUsuario, $id_rol, $id_usuario);  // Llamar al método modificar de D_RolUsuario
    }

    // Método para buscar un rolUsuario por ID
    public function buscarPorId($id_RolUsuario) {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->buscarPorId($id_RolUsuario);
    }

    public function obtenerUsuario() {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->obtenerUsuario();
    }
    public function obtenerRol() {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->obtenerRol();
    }
    // N_RolUsuario.php
    public function obtenerRolesPorUsuario($id_usuario) {
        $NRolUsuario = new D_RolUsuario();
        return $NRolUsuario->obtenerRolesPorUsuario($id_usuario);
    }
}
?>
