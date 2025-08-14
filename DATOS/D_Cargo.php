<?php
class D_Cargo {
    private $id_cargo;
    private $nombre_c;
    private $descripcion_c;
    private $funcionarios_c;
    private $con;

    // Constructor
    public function __construct($id_cargo = 0, $nombre_c = "Default nombre", $descripcion_c = "Default descri", $funcionarios_c = "Default funcionario") {
        $this->id_cargo = $id_cargo;
        $this->nombre_c = $nombre_c;
        $this->descripcion_c = $descripcion_c;
        $this->funcionarios_c = $funcionarios_c;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_cargo() { return $this->id_cargo; }
    public function setId_cargo($id_cargo) { $this->id_cargo = $id_cargo; }

    public function getNombre() { return $this->nombre_c; }
    public function setNombre($nombre_c) { $this->nombre_c = $nombre_c; }

    public function getDescripcion() { return $this->descripcion_c; }
    public function setDescripcion($descripcion_c) { $this->descripcion_c = $descripcion_c; }

    public function getAfuncionario() { return $this->funcionarios_c; }
    public function setAfuncionario($funcionarios_c) { $this->funcionarios_c = $funcionarios_c; }

    //Método para adicionar un cargo de trabajo
    public function Adicionar( $nombre_c,$descripcion_c) {
        $sql = "CALL AdicionarCargo(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([ $nombre_c, $descripcion_c]);
            echo "cargo registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos las cargos
    public function BuscarTodo() {
        $sql = "CALL ObtenerCargos()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un cargo
    public function Eliminar($id_cargo) {
        $sql = "CALL EliminarCargo(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_cargo]);
            echo "cargo eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar cargo por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarCargo(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un cargo
    public function modificar($id_cargo, $nombre_c,$descripcion_c) {
        $sql = "CALL CargarCargo(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_cargo, $nombre_c, $descripcion_c]);
            echo "cargo actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un cargo por ID
    public function buscarPorId($id_cargo) {
        $sql = "CALL buscarPorIdCargo(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_cargo]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }

    // Método para buscar un cargo por nombre de cargo
    // public function buscarPorCargo($nombre_c) {
    //     $sql = "CALL ObtenerCargoPorNombre(?)";
    //     try {
    //         $ps = $this->con->prepare($sql);
    //         $ps->execute([$nombre_c]);
    //         return $ps->fetch(PDO::FETCH_ASSOC);
    //     } catch (PDOException $ex) {
    //         echo "Error al buscar el cargo por nombre: " . $ex->getMessage();
    //         return null;
    //     }
    // }

    // Actualizar la cantidad de funcionarios por cargo
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
