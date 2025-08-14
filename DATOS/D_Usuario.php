<?php
class D_Usuario {
    private $id_usuario;
    private $nombre;
    private $apellido;
    private $usuario;
    private $clave;
    private $correo;
    private $estado;
    private $fecha_registro;
    private $con;

    // Constructor
    public function __construct($id_usuario = 0, $nombre = "Default Name", $apellido = "Default LastName", $usuario = "Default User", $clave = "Default Password", $correo = "Default Email", $estado = "Default Stado",$fecha_registro="Default fecha") {
        $this->id_usuario = $id_usuario;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->usuario = $usuario;
        $this->clave = $clave;
        $this->correo = $correo;
        $this->estado = $estado;
        $this->fecha_registro = $fecha_registro;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_usuario() { return $this->id_usuario; }
    public function setId($id_usuario) { $this->id = $id_usuario; }

    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }

    public function getApellido() { return $this->apellido; }
    public function setApellido($apellido) { $this->apellido = $apellido; }

    public function getUsuario() { return $this->usuario; }
    public function setUsuario($usuario) { $this->usuario = $usuario; }

    public function getClave() { return $this->clave; }
    public function setClave($clave) { $this->clave = $clave; }

    public function getCorreo() { return $this->correo; }
    public function setCorreo($correo) { $this->correo = $correo; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

    public function getFecha() { return $this->fecha_registro; }
    public function setFecha($fecha_registro) { $this->fecha_registro = $fecha_registro; }


    
    // Método para adicionar un usuario
    public function Adicionar( $nombre,$apellido, $usuario, $clave, $correo) {
        $sql = "CALL AdicionarUsuario( ?, ?, ?, ?,?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([ $nombre, $apellido, $usuario, $clave, $correo]);
            echo "Usuario registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los usuarios
    public function BuscarTodo() {
        $sql = "CALL ObtenerUsuario()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un usuario
    public function Eliminar($id_usuario) {
        $sql = "CALL EliminarUsuario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_usuario]);
            echo "Usuario eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar usuarios por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarUsuario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
   // Método para modificar
public function modificar($id_usuario, $nombre, $apellido, $usuario, $clave, $correo, $estado) {
    $sql = "CALL CargarUsuario(?, ?, ?, ?, ?, ?, ?)";
    try {
        $ps = $this->con->prepare($sql);
        $ps->execute([$id_usuario, $nombre, $apellido, $usuario, $clave, $correo, $estado]);
        echo "Usuario actualizado correctamente.";
    } catch (PDOException $ex) {
        echo "Error al actualizar: " . $ex->getMessage();
    }
}

    // Método para buscar un usuario por ID
    public function buscarPorId($id_usuario) {
        $sql = "CALL buscarPorIdUsuario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_usuario]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }
    // Método para buscar un usuario por nombre de usuario
    public function buscarPorUsuario($usuario) {
        $sql = "CALL ObtenerUsuarioPorNombre(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$usuario]);
            return $ps->fetch(PDO::FETCH_ASSOC); // ← devuelve array asociativo
        } catch (PDOException $ex) {
            echo "Error al buscar usuario: " . $ex->getMessage();
            return null;
        }
    }
    
    
    
}
?>