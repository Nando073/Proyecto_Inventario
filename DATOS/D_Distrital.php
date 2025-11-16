<?php
class D_Distrital {
    private $id_distrital;
    private $di_nombre;
    private $di_apellido;
    private $di_correo;
    private $id_distrito;
    private $di_estado;
    private $di_ci;
    private $ci_complemento;
    private $con;

    // Constructor
    public function __construct($id_distrital = 0, $di_nombre = "Default Name", $di_apellido = "Default LastName", $di_correo = "Default Email", $id_distrito="default cargo", $di_estado = "Default Estado", $di_ci = "Default di_ci", $ci_complemento = "Default Complemento") {
        $this->id_distrital = $id_distrital;
        $this->di_nombre = $di_nombre;
        $this->di_apellido = $di_apellido;
        $this->di_correo = $di_correo;
        $this->id_distrito = $id_distrito;
        $this->di_estado = $di_estado;
        $this->di_ci = $di_ci;
        $this->ci_complemento = $ci_complemento;
        $this->con = (new D_coneccion())->Conectar();
    }

    // Getters y Setters
    public function getId_distrital() { return $this->id_distrital; }
    public function setId($id_distrital) { $this->id_distrital = $id_distrital; }

    public function getNombre() { return $this->di_nombre; }
    public function setNombre($di_nombre) { $this->di_nombre = $di_nombre; }

    public function getApellido() { return $this->di_apellido; }
    public function setApellido($di_apellido) { $this->di_apellido = $di_apellido; }

    public function getCorreo() { return $this->di_correo; }
    public function setCorreo($di_correo) { $this->di_correo = $di_correo; }

    public function getId_distrito() { return $this->id_distrito; }
    public function setId_distrito($id_distrito) { $this->id_distrito = $id_distrito; }

    public function getEstado() { return $this->di_estado; }
    public function setEstado($di_estado) { $this->di_estado = $di_estado; }

    public function getCI() { return $this->di_ci; }
    public function setCI($di_ci) { $this->di_ci = $di_ci; }

    public function getComplemento() { return $this->ci_complemento; }
    public function setComplemento($ci_complemento) { $this->ci_complemento = $ci_complemento; }

    // Método para adicionar un distrital
    public function Adicionar($di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento) {
        $sql = "CALL AdicionarDistrital(?, ?, ?, ?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento]);
            echo "Distrital registrado correctamente.";
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return $resultado['success']; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los distritales
    public function ObtenerDistritales() {
        $sql = "CALL ObtenerDistritales()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
    

    // Método para eliminar un distrital
    public function Eliminar($id_distrital) {
        $sql = "CALL EliminarDistrital(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_distrital]);
            echo "Distrital eliminado correctamente.";
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return $resultado['success']; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar distritales por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarDistrital(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar distrital
    public function modificar($id_distrital, $di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento) {
        $sql = "CALL CargarDistrital(?, ?, ?, ?, ?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_distrital, $di_nombre, $di_apellido, $di_correo, $id_distrito, $di_ci, $ci_complemento]);
            echo "Distrital actualizado correctamente.";
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return ['success' => $resultado['success']]; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un distrital por ID
    public function buscarPorId($id_distrital) {
        $sql = "CALL buscarPorIdDistrital(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_distrital]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }


    public function activarDistrital($id_distrital) {
    $query = "CALL ActivarDistrital(:id_distrital)";
    $stmt = $this->con->prepare($query);
    $stmt->execute([':id_distrital' => $id_distrital]);
    return $stmt->rowCount() > 0; // Retorna true si se actualizó al menos una fila
}

// public function ObtenerDistritalesDisponibles($id_distrital = null) {
//     $sql = "CALL ObtenerDistritalesDisponibles(:id_distrital)";
//     try {
//         $ps = $this->con->prepare($sql);
//         $ps->bindValue(':id_distrital', $id_distrital, PDO::PARAM_INT);
//         $ps->execute();
//         return $ps->fetchAll(PDO::FETCH_ASSOC);
//     } catch (PDOException $ex) {
//         echo "Error al buscar: " . $ex->getMessage();
//         return null;
//     }
// }

//  public function ObtenerDistritalDisp() {
//         $sql = "CALL ObtenerDistritalDisp()";
//         try {
//             $ps = $this->con->prepare($sql);
//             $ps->execute();
//             return $ps->fetchAll(PDO::FETCH_ASSOC);
//         } catch (PDOException $ex) {
//             echo "Error al buscar: " . $ex->getMessage();
//             return [];
//         }
//     }
    
 }
?>
