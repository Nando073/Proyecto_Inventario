<?php
class D_Rol {
    private $id_rol;
    private $r_nombre;
    private $r_descripcion;
    private $con;

    // Constructor
    public function __construct($id_rol = 0, $r_nombre = "Default nombre", $r_descripcion = "Default descri") {
        $this->id_rol = $id_rol;
        $this->r_nombre = $r_nombre;
        $this->r_descripcion = $r_descripcion;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_rol() { return $this->id_rol; }
    public function setId_rol($id_rol) { $this->id_rol = $id_rol; }

    public function getNombre() { return $this->r_nombre; }
    public function setNombre($r_nombre) { $this->r_nombre = $r_nombre; }

    public function getDescripcion() { return $this->r_descripcion; }
    public function setDescripcion($r_descripcion) { $this->r_descripcion = $r_descripcion; }

    //Método para adicionar un rol de trabajo
    public function Adicionar( $r_nombre,$r_descripcion) {
        $sql = "CALL AdicionarRol(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([ $r_nombre, $r_descripcion]);
            echo "rol registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los roles
    public function BuscarTodo() {
        $sql = "CALL ObtenerRol()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un rol
    public function Eliminar($id_rol) {
        $sql = "CALL EliminarRol(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_rol]);
            echo "rol eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar rol por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarRol(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un rol
    public function modificar($id_rol, $r_nombre,$r_descripcion) {
        $sql = "CALL CargarRol(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_rol, $r_nombre, $r_descripcion]);
            echo "rol actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un rol por ID
    public function buscarPorId($id_rol) {
        $sql = "CALL buscarPorIdRol(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_rol]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }
    
    }

?>
