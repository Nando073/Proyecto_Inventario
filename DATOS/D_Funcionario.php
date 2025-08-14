<?php
class D_Funcionario {
    private $id_funcionario;
    private $f_nombre;
    private $f_apellido;
    private $f_correo;
    private $area;
    private $id_cargo;
    private $f_estado;
    private $con;

    // Constructor
    public function __construct($id_funcionario = 0, $f_nombre = "Default Name", $f_apellido = "Default LastName", $f_correo = "Default Email", $area = "Default Area",$id_cargo="default cargo", $f_estado = "Default Estado") {
        $this->id_funcionario = $id_funcionario;
        $this->f_nombre = $f_nombre;
        $this->f_apellido = $f_apellido;
        $this->f_correo = $f_correo;
        $this->area = $area;
        $this->id_cargo = $id_cargo;
        $this->f_estado = $f_estado;
        $this->con = (new D_coneccion())->Conectar();
    }

    // Getters y Setters
    public function getId_funcionario() { return $this->id_funcionario; }
    public function setId($id_funcionario) { $this->id_funcionario = $id_funcionario; }

    public function getNombre() { return $this->f_nombre; }
    public function setNombre($f_nombre) { $this->f_nombre = $f_nombre; }

    public function getApellido() { return $this->f_apellido; }
    public function setApellido($f_apellido) { $this->f_apellido = $f_apellido; }

    public function getCorreo() { return $this->f_correo; }
    public function setCorreo($f_correo) { $this->f_correo = $f_correo; }

    public function getArea() { return $this->area; }
    public function setArea($area) { $this->area = $area; }

    public function getId_cargo() { return $this->id_cargo; }
    public function setId_cargo($id_cargo) { $this->id_cargo = $id_cargo; }

    public function getEstado() { return $this->f_estado; }
    public function setEstado($f_estado) { $this->f_estado = $f_estado; }

    // Método para adicionar un funcionario
    public function Adicionar($f_nombre, $f_apellido, $f_correo, $area, $id_cargo) {
        $sql = "CALL AdicionarFuncionario(?, ?, ?, ? , ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$f_nombre, $f_apellido, $f_correo, $area, $id_cargo]);
            echo "Funcionario registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los funcionarios
    public function ObtenerFuncionarios() {
        $sql = "CALL ObtenerFuncionariosConArea_Cargo()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
    

    // Método para eliminar un funcionario
    public function Eliminar($id_funcionario) {
        $sql = "CALL EliminarFuncionario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_funcionario]);
            echo "Funcionario eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar funcionarios por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarFuncionario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar funcionario
    public function modificar($id_funcionario, $f_nombre, $f_apellido, $f_correo, $area, $id_cargo, $f_estado) {
        $sql = "CALL CargarFuncionario(?, ?, ?, ?, ?, ? , ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_funcionario, $f_nombre, $f_apellido, $f_correo, $area, $id_cargo, $f_estado]);
            echo "Funcionario actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un funcionario por ID
    public function buscarPorId($id_funcionario) {
        $sql = "CALL buscarPorIdFuncionario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_funcionario]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }

    public function obtenerAreas() {
        $sql = "CALL obtenerAreas()";
       try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return null;
        }
    }
    public function obtenerCargos() {
         $sql = "CALL obtenerCargos()";
       try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return null;
        }
    }
    
}
?>
