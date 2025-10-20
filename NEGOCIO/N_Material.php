<?php
require_once __DIR__ . '/../DATOS/D_Material.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Material {

    // Método para adicionar m_descripcion
    public function adicionar( $m_nombre, $m_descripcion, $id_categoria,$id_medida) {
        try {
            $NMaterial = new D_Material(); 
            $resultado = $NMaterial->Adicionar( $m_nombre, $m_descripcion, $id_categoria, $id_medida);  // Llamar al método de D_Material
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar todos los materiales
    public function obtenerMateriales() {
        $NMaterial = new D_Material();
        return $NMaterial->ObtenerMateriales();
    }
     public function obtenerMaterialesConStock() {
        // Llama al procedimiento original
        $materiales = $this->ObtenerMateriales(); // Método que llama a ObtenerMateriales
        // Filtrar solo los materiales con stock > 0
        $materialesConStock = array_filter($materiales, function($material) {
            return $material['stock'] > 0;
        });
        return array_values($materialesConStock); // Reindexar el array
    }

    // Método para eliminar un material por ID
public function eliminar($id_material) {
    try {
        $NMaterial = new D_Material();
        $resultado = $NMaterial->Eliminar($id_material);  // Capturar el resultado     
        return ['success' => (bool)$resultado]; // Convertir 1/0 a true/false
    } catch (Exception $e) {
        throw $e;
    }
}

    // Método para buscar materiales por similitud de término
    public function buscarPorSimilitud($termino) {
        $NMaterial = new D_Material();
        return $NMaterial->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Material
    }

    // Método para modificar un material
    public function modificar($id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida) {
        try {
            $NMaterial = new D_Material();
            $resultado = $NMaterial->modificar($id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida);  // Llamar al método modificar de D_Material
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Método para buscar un material por ID
    public function buscarPorId($id_material) {
        $NMaterial = new D_Material();
        return $NMaterial->buscarPorId($id_material);
    }

}
?>
