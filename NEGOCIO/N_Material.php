<?php
require_once __DIR__ . '/../DATOS/D_Material.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';
class N_Material {

    // Método para adicionar m_descripcion
    public function adicionar( $m_nombre, $m_descripcion, $id_categoria,$id_medida) {
        $NMaterial = new D_Material(); 
        $NMaterial->Adicionar( $m_nombre, $m_descripcion, $id_categoria, $id_medida);  // Llamar al método de D_Material
    }

    // Método para buscar todos los funcionarios
    public function buscarTodo() {
        $NMaterial = new D_Material();
        return $NMaterial->BuscarTodo();
    }

    // Método para eliminar un material por ID
    public function eliminar($id_material) {
        $NMaterial = new D_Material();
        $NMaterial->Eliminar($id_material);  // Llamar al método Eliminar de D_Material
    }

    // Método para buscar materiales por similitud de término
    public function buscarPorSimilitud($termino) {
        $NMaterial = new D_Material();
        return $NMaterial->buscarPorSimilitud($termino);  // Llamar al método buscarPorSimilitud de D_Material
    }

    // Método para modificar un material
    public function modificar($id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida) {
        $NMaterial = new D_Material();
        $NMaterial->modificar($id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida);  // Llamar al método modificar de D_Material
    }

    // Método para buscar un material por ID
    public function buscarPorId($id_material) {
        $NMaterial = new D_Material();
        return $NMaterial->buscarPorId($id_material);
    }

    public function obtenerCategorias() {
        $NMaterial = new D_Material();
        return $NMaterial->obtenerCategorias();
    }
    public function obtenerMedidas() {
        $NMaterial = new D_Material();
        return $NMaterial->obtenerMedidas();
    }
}
?>
