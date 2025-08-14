<?php
class D_Categoria {
    private $id_categoria;
    private $c_nombre;
    private $c_descripcion;
    private $c_materiales;
    private $con;

    // Constructor
    public function __construct($id_categoria = 0, $c_nombre = "Nombre por defecto", $c_descripcion = "Descripción por defecto", $c_materiales = 0) {
        $this->id_categoria = $id_categoria;
        $this->c_nombre = $c_nombre;
        $this->c_descripcion = $c_descripcion;
        $this->c_materiales = $c_materiales;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_categoria() { return $this->id_categoria; }
    public function setId_categoria($id_categoria) { $this->id_categoria = $id_categoria; }

    public function getNombre() { return $this->c_nombre; }
    public function setNombre($c_nombre) { $this->c_nombre = $c_nombre; }

    public function getDescripcion() { return $this->c_descripcion; }
    public function setDescripcion($c_descripcion) { $this->c_descripcion = $c_descripcion; }

    public function getMateriales() { return $this->c_materiales; }
    public function setMateriales($c_materiales) { $this->c_materiales = $c_materiales; }

    // Método para adicionar una categoría
    public function Adicionar($c_nombre, $c_descripcion) {
        $sql = "CALL AdicionarCategoria(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$c_nombre, $c_descripcion]);
            echo "Categoría registrada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todas las categorías
    public function BuscarTodo() {
        $sql = "CALL ObtenerCategorias()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar una categoría
    public function Eliminar($id_categoria) {
        $sql = "CALL EliminarCategoria(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_categoria]);
            echo "Categoría eliminada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar categoría por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL 	BuscarCategoria(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar una categoría
    public function modificar($id_categoria, $c_nombre, $c_descripcion) {
        $sql = "CALL CargarCategoria(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_categoria, $c_nombre, $c_descripcion]);
            echo "Categoría actualizada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar una categoría por ID
    public function buscarPorId($id_categoria) {
        $sql = "CALL buscarPorIdCategoria(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_categoria]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }
    
    // Método para actualizar la cantidad de categorías
    public function actualizarCantidadCategorias() {
        $sql = "CALL ActualizarCantidadCategorias()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            echo "Cantidad de categorías actualizada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar la cantidad de categorías: " . $ex->getMessage();
        }
    }
}
?>
