<?php
class D_RolUsuario {
    private $id_RolUsuario;
    private $id_rol;
    private $id_usuario;
    private $con;

    // Constructor
    public function __construct($id_RolUsuario = 0, $id_rol = "Default descri", $id_usuario = "Default nombre") {
        $this->id_RolUsuario = $id_RolUsuario;
        $this->id_rol = $id_rol;
        $this->id_usuario = $id_usuario;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_asignar() { return $this->id_RolUsuario; }
    public function setId_asignar($id_RolUsuario) { $this->id_RolUsuario = $id_RolUsuario; }

    public function getRol() { return $this->id_rol; }
    public function setRol($id_rol) { $this->id_rol = $id_rol; }

    public function getusuario() { return $this->id_usuario; }
    public function setusuario($id_usuario) { $this->id_usuario = $id_usuario; }

    //Método para adicionar rol_usuario
    public function Adicionar($id_rol, $id_usuario) {
        $sql = "CALL AdicionarRolUsuario(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_rol, $id_usuario]);
            echo "RolUsuario registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los RolUsuario
    public function BuscarTodo() {
        $sql = "CALL ObtenerRolUsuario()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un RolUsuario
    public function Eliminar($id_RolUsuario) {
        $sql = "CALL EliminarRolUsuario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_RolUsuario]);
            echo "RolUsuario eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar RolUsuario por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarRolUsuario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un RolUsuario
    public function modificar($id_RolUsuario,$id_rol, $id_usuario) {
        $sql = "CALL CargarRolUsuario(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_RolUsuario, $id_rol, $id_usuario]);
            echo "RolUsuario actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un RolUsuario por ID
    public function buscarPorId($id_RolUsuario) {
        $sql = "CALL buscarPorIdRolUsuario(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_RolUsuario]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }
    
    
    //otener las usuarios  y roles para mi formulario atrves del select
    public function obtenerUsuario() {
        $sql = "CALL obtenerUsuario()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return null;
        }
    }

    public function obtenerRol() {
        $sql = "CALL obtenerRol()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return null;
        }
    }

    public function obtenerRolesPorUsuario() {
        $sql = "CALL ObtenerRolUsuario()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return null;
        }
    }

        // D_RolUsuario.php
//     public function obtenerRolesPorUsuario($id_usuario) {
//     $sql = "SELECT r.r_nombre, f.f_nombre, f.f_apellido
//             FROM rol_usuario ru
//             INNER JOIN usuario u ON ru.id_usuario = u.id_usuario
//             INNER JOIN funcionario f ON u.id_funcionario = f.id_funcionario
//             INNER JOIN rol r ON ru.id_rol = r.id_rol
//             WHERE ru.id_usuario = ?";
//     $ps = $this->con->prepare($sql);
//     $ps->execute([$id_usuario]);
//     return $ps->fetchAll(PDO::FETCH_ASSOC);
// }
}
?>
