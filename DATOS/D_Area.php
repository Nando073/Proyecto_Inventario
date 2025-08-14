<?php
class D_Area {
    private $id_area;
    private $a_nombre;
    private $a_descripcion;
    private $a_funcionarios;
    private $con;

    // Constructor
    public function __construct($id_area = 0, $a_nombre = "Default nombre", $a_descripcion = "Default descri", $a_funcionarios = "Default funcionario") {
        $this->id_area = $id_area;
        $this->a_nombre = $a_nombre;
        $this->a_descripcion = $a_descripcion;
        $this->a_funcionarios = $a_funcionarios;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_area() { return $this->id_area; }
    public function setId_area($id_area) { $this->id_area = $id_area; }

    public function getNombre() { return $this->a_nombre; }
    public function setNombre($a_nombre) { $this->a_nombre = $a_nombre; }

    public function getDescripcion() { return $this->a_descripcion; }
    public function setDescripcion($a_descripcion) { $this->a_descripcion = $a_descripcion; }

    public function getAfuncionario() { return $this->a_funcionarios; }
    public function setAfuncionario($a_funcionarios) { $this->a_funcionarios = $a_funcionarios; }

    //Método para adicionar un area de trabajo
    public function Adicionar( $a_nombre,$a_descripcion) {
        $sql = "CALL AdicionarArea(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([ $a_nombre, $a_descripcion]);
            echo "material registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos las areas
    public function BuscarTodo() {
        $sql = "CALL ObtenerAreas()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un area
    public function Eliminar($id_area) {
        $sql = "CALL EliminarArea(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_area]);
            echo "material eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar area por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarArea(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un area
    public function modificar($id_area, $a_nombre,$a_descripcion) {
        $sql = "CALL CargarArea(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_area, $a_nombre, $a_descripcion]);
            echo "area actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un area por ID
    public function buscarPorId($id_area) {
        $sql = "CALL buscarPorIdArea(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_area]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }

    // Actualizar la cantidad de funcionarios por área
    public function actualizarCantidadFuncionarios() {
        try {
            $stmt = $this->con->prepare("CALL ActualizarCantidadFuncionarios()");
            $stmt->execute();
            echo "Cantidad de funcionarios actualizada correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar cantidad de funcionarios: " . $ex->getMessage();
        }
    }
    
    

}
?>
