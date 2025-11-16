<?php
class D_Distrito {
    private $id_distrito;
    private $d_nombre;
    private $d_descripcion;
    private $con;

    // Constructor
    public function __construct($id_distrito = 0, $d_nombre = "Default nombre", $d_descripcion = "Default descri") {
        $this->id_distrito = $id_distrito;
        $this->d_nombre = $d_nombre;
        $this->d_descripcion = $d_descripcion;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_distrito() { return $this->id_distrito; }
    public function setId_distrito($id_distrito) { $this->id_distrito = $id_distrito; }

    public function getNombre() { return $this->d_nombre; }
    public function setNombre($d_nombre) { $this->d_nombre = $d_nombre; }

    public function getDescripcion() { return $this->d_descripcion; }
    public function setDescripcion($d_descripcion) { $this->d_descripcion = $d_descripcion; }

    //Método para adicionar un area de trabajo
    public function Adicionar( $d_nombre,$d_descripcion) {
        $sql = "CALL AdicionarDistrito(?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([ $d_nombre, $d_descripcion]);
             // Obtener el resultado del procedimiento
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return $resultado['success']; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos las distrito
    public function ObtenerDistritos() {
        $sql = "CALL ObtenerDistritos()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un distrito
    public function Eliminar($id_distrito) {
        $sql = "CALL EliminarDistrito(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_distrito]);
            // Obtener el resultado del procedimiento
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return $resultado['success']; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            throw new Exception("Error al eliminar distrito: " . $ex->getMessage());
        }
    }

    // Método para buscar distrito por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarDistrito(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un distrito
    public function modificar($id_distrito, $d_nombre,$d_descripcion) {
        $sql = "CALL CargarDistrito(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_distrito, $d_nombre, $d_descripcion]);
            echo "area actualizado correctamente.";
            // Obtener el resultado del procedimiento
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un distrito por ID
    public function buscarPorId($id_distrito) {
        $sql = "CALL buscarPorIdDistrito(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_distrito]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }

    // Método para obtener distritos disponibles (sin distrital activo asignado)
    public function ObtenerDistritosDisponibles($id_distrito_actual = null) {
        $sql = "CALL ObtenerDistritosDisponibles(:id_distrito)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->bindValue(':id_distrito', $id_distrito_actual, PDO::PARAM_INT);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar distritos disponibles: " . $ex->getMessage();
            return [];
        }
    }

}
?>
