<?php
require_once __DIR__ . '/../DATOS/D_Proveedor.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Proveedor {

    // Método para adicionar un proveedor
    public function adicionar( $p_nombre, $nit, $departamento, $p_direccion, $p_celular) {
        try {
            $NProveedor = new D_Proveedor(); 
            $resultado = $NProveedor->Adicionar( $p_nombre, $nit, $departamento, $p_direccion, $p_celular);  // Llamar al método de D_Proveedor
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
            }

    // Método para buscar todos los proveedores
    public function obtenerProveedores() {
        $NProveedor = new D_Proveedor();
        return $NProveedor->ObtenerProveedores();
    }

    // Método para obtener proveedores activos
    public function obtenerProveedoresActivos() {
        $proveedores = $this->ObtenerProveedores(); // Llama al método original
        $proveedoresActivos = array_filter($proveedores, function($proveedor) {
            return $proveedor['p_estado'] === 1;
        });
        return array_values($proveedoresActivos); // Reindexar el array
    }

    // Método para eliminar un proveedor por ID
    public function eliminar($id_proveedor) {
        try {
            $NProveedor = new D_Proveedor();
            $resultado =$NProveedor->Eliminar($id_proveedor);  // Llamar al método Eliminar de D_Proveedor
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        }catch (Exception $e){
            throw $e;
        }
    }

    // Método para buscar proveedores por similitud de término
    public function buscarPorSimilitud($termino) {
        $NProveedor = new D_Proveedor();
        return $NProveedor->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Proveedor
    }

    // Método para modificar un proveedor
    public function modificar($id_proveedor, $p_nombre, $nit, $departamento, $p_direccion, $p_celular) {
        try {
            $NProveedor = new D_Proveedor();
            $resultado = $NProveedor->modificar($id_proveedor, $p_nombre, $nit, $departamento, $p_direccion, $p_celular);  // Llamar al método modificar de D_Proveedor
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
        
    }

    // Método para buscar un proveedor por ID
    public function buscarPorId($id_proveedor) {
        $NProveedor = new D_Proveedor();
        return $NProveedor->buscarPorId($id_proveedor);
    }
    public function activarProveedor($id_proveedor) {
        $DProveedor = new D_Proveedor();
        return $DProveedor->activarProveedor($id_proveedor);
    }
}
?>
