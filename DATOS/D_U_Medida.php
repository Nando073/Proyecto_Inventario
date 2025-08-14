<?php
class D_U_Medida {
    private $id_medida;
    private $u_medida;
    private $u_descripcion;
    private $u_materiales;
    private $con;

    // Constructor
    public function __construct($id_medida = 0, $u_medida = "Nombre por defecto", $u_descripcion = "Descripción por defecto", $u_materiales = 0) {
        $this->id_medida = $id_medida;
        $this->u_medida = $u_medida;
        $this->u_descripcion = $u_descripcion;
        $this->u_materiales = 0; // Inicializar a 0 o el valor que desees
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_medida() { return $this->id_medida; }
    public function setId_medida($id_medida) { $this->id_medida = $id_medida; }

    public function getNombre() { return $this->u_medida; }
    public function setNombre($u_medida) { $this->u_medida = $u_medida; }

    public function getDescripcion() { return $this->u_descripcion; }
    public function setDescripcion($u_descripcion) { $this->u_descripcion = $u_descripcion; }

    public function getMateriales() { return $this->u_materiales; }
    public function setMateriales($u_materiales) { $this->u_materiales = $u_materiales; }

    // Método para adicionar una medida
    public function Adicionar($u_medida, $u_descripcion) {
        $sql = "CALL AdicionarMedida(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$u_medida, $u_descripcion]);
            echo "Unidad de Medida registrada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todas las unidades de medida
    public function BuscarTodo() {
        $sql = "CALL ObtenerMedidas()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar una medida
    public function Eliminar($id_medida) {
        $sql = "CALL EliminarMedida(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_medida]);
            echo "Categoría eliminada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar medida por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL 	BuscarMedida(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar una medida
    public function modificar($id_medida, $u_medida, $u_descripcion) {
        $sql = "CALL CargarMedida(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_medida, $u_medida, $u_descripcion]);
            echo "Unidad de medida actualizada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar una medida por ID
    public function buscarPorId($id_medida) {
        $sql = "CALL buscarPorIdMedida(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_medida]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }


     // Actualizar la cantidad de medidas por material
     public function actualizarCantidadMedidas() {
        try {
            $stmt = $this->con->prepare("CALL ActualizarCantidadMedidas()");
            $stmt->execute();
            echo "Cantidad de medidas actualizada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar cantidad de medidas: " . $ex->getMessage();
        }
    }
}
?>
