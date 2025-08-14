<?php
class D_Material {
    private $id_material;
    private $m_nombre;
    private $m_fecha;
    private $m_descripcion;
    private $id_categoria;
    private $id_medida;
    private $stock;
    private $con;

    // Constructor
    public function __construct($id_material = 0, $m_nombre = "Default Name", $m_fecha = "Default fecha", $m_descripcion = "Default Descripcion", $id_categoria = "Default Categoria", $id_medida = "Default id_medida", $stock = 0) {
        $this->id_material = $id_material;
        $this->m_nombre = $m_nombre;
        $this->m_fecha = $m_fecha;
        $this->m_descripcion = $m_descripcion;
        $this->id_categoria = $id_categoria;
        $this->id_medida = $id_medida;
        $this->stock = $stock;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_material() { return $this->id_material; }
    public function setId_material($id_material) { $this->id_material = $id_material; }

    public function getNombre() { return $this->m_nombre; }
    public function setNombre($m_nombre) { $this->m_nombre = $m_nombre; }

    public function getFecha() { return $this->m_fecha; }
    public function setFecha($m_fecha) { $this->m_fecha = $m_fecha; }

    public function getDescripcion() { return $this->m_descripcion; }
    public function setDescripcion($m_descripcion) { $this->m_descripcion = $m_descripcion; }

    public function getCategoria() { return $this->id_categoria; }
    public function setCategoria($id_categoria) { $this->id_categoria = $id_categoria; }

    public function getm_uMedida() { return $this->id_medida; }
    public function setm_uMedida($id_medida) { $this->id_medida = $id_medida; }

    public function getStock() { return $this->stock; }
    public function setStock($stock) { $this->stock = $stock; }

    // Método para adicionar un material
    public function Adicionar( $m_nombre,$m_descripcion, $id_categoria, $id_medida) {
        $sql = "CALL AdicionarMaterial(?, ?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$m_nombre,$m_descripcion, $id_categoria, $id_medida]);
            echo "material registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los material
    public function BuscarTodo() {
        $sql = "CALL ObtenerMateriales()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un material
    public function Eliminar($id_material) {
        $sql = "CALL EliminarMaterial(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_material]);
            echo "material eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar material por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarMaterial(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un material
    public function modificar($id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida) {
        $sql = "CALL CargarMaterial( ?, ?, ?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_material, $m_nombre, $m_descripcion, $id_categoria, $id_medida]);
            echo "Material actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un material por ID
    public function buscarPorId($id_material) {
        $sql = "CALL buscarPorIdMaterial(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_material]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }

//otener las categorias  y las unidades para mi formulario atrves del select
    public function obtenerCategorias() {
        $query = "SELECT id_categoria, c_nombre FROM categoria";
        $stmt = $this->con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerMedidas() {
        $query = "SELECT id_medida, u_medida FROM u_medida";
        $stmt = $this->con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
