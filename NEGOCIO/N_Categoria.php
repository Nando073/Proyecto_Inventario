<?php
require_once __DIR__ . '/../DATOS/D_Categoria.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';

class N_Categoria {

    // Método para adicionar una categoría
    public function adicionar($c_nombre, $c_descripcion) {
        $NCategoria = new D_Categoria(); 
        $NCategoria->Adicionar($c_nombre, $c_descripcion);
    }

    // Método para buscar todas las categorías
    public function buscarTodo() {
        $NCategoria = new D_Categoria();
        return $NCategoria->BuscarTodo();
    }

    // Método para eliminar una categoría por ID
    public function eliminar($id_categoria) {
        $NCategoria = new D_Categoria();
        $NCategoria->Eliminar($id_categoria);
    }

    // Método para buscar categorías por similitud de término
    public function buscarPorSimilitud($termino) {
        $NCategoria = new D_Categoria();
        return $NCategoria->buscarPorSimilitud($termino);
    }

    // Método para modificar una categoría
    public function modificar($id_categoria, $c_nombre, $c_descripcion) {
        $NCategoria = new D_Categoria();
        $NCategoria->modificar($id_categoria, $c_nombre, $c_descripcion);
    }

    // Método para buscar una categoría por ID
    public function buscarPorId($id_categoria) {
        $NCategoria = new D_Categoria();
        return $NCategoria->buscarPorId($id_categoria);
    }

    // Método para actualizar la cantidad de categorías
    public function actualizarCantidadCategorias() {
        $NCategoria = new D_Categoria();
        $NCategoria->actualizarCantidadCategorias();
    }
}
?>
