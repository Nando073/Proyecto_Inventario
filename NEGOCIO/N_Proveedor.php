<?php
require_once __DIR__ . '/../DATOS/D_Proveedor.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Proveedor {

    // Método para adicionar un proveedor
    public function adicionar( $p_nombre, $p_direccion, $p_celular) {
        $NProveedor = new D_Proveedor(); 
        $NProveedor->Adicionar( $p_nombre, $p_direccion, $p_celular);  // Llamar al método de D_Proveedor
    }

    // Método para buscar todos los proveedores
    public function buscarTodo() {
        $NProveedor = new D_Proveedor();
        return $NProveedor->BuscarTodo();
    }

    // Método para eliminar un proveedor por ID
    public function eliminar($id_proveedor) {
        $NProveedor = new D_Proveedor();
        $NProveedor->Eliminar($id_proveedor);  // Llamar al método Eliminar de D_Proveedor
    }

    // Método para buscar ares por similitud de término
    public function buscarPorSimilitud($termino) {
        $NProveedor = new D_Proveedor();
        return $NProveedor->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Proveedor
    }

    // Método para modificar un proveedor
    public function modificar($id_proveedor, $p_nombre, $p_direccion, $p_celular) {
        $NProveedor = new D_Proveedor();
        $NProveedor->modificar($id_proveedor, $p_nombre, $p_direccion, $p_celular);  // Llamar al método modificar de D_Proveedor
    }

    // Método para buscar un proveedor por ID
    public function buscarPorId($id_proveedor) {
        $NProveedor = new D_Proveedor();
        return $NProveedor->buscarPorId($id_proveedor);
    }
}
?>
