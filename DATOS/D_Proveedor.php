<?php
class D_Proveedor {
    private $id_proveedor;
    private $p_nombre;
    private $nit;
    private $departamento;
    private $p_direccion;
    private $p_celular;
    private $con;

    // Constructor
    public function __construct($id_proveedor = 0, $p_nombre = "Default nombre", $nit = "Default NIT", $departamento = "Default departamento", $p_direccion = "Default descri", $p_celular = "Default funcionario") {
        $this->id_proveedor = $id_proveedor;
        $this->p_nombre = $p_nombre;
        $this->nit = $nit;
        $this->departamento = $departamento;
        $this->p_direccion = $p_direccion;
        $this->p_celular = $p_celular;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_proveedor() { return $this->id_proveedor; }
    public function setId_proveedor($id_proveedor) { $this->id_proveedor = $id_proveedor; }

    public function getNombre() { return $this->p_nombre; }
    public function setNombre($p_nombre) { $this->p_nombre = $p_nombre; }

    public function getNit() { return $this->nit; }
    public function setNit($nit) { $this->nit = $nit; }

    public function getDepartamento() { return $this->departamento; }
    public function setDepartamento($departamento) { $this->departamento = $departamento; }

    public function getDireccion() { return $this->p_direccion; }
    public function setDireccion($p_direccion) { $this->p_direccion = $p_direccion; }

    public function getCelular() { return $this->p_celular; }
    public function setCelular($p_celular) { $this->p_celular = $p_celular; }

    //Método para adicionar un proveedor
    public function Adicionar( $p_nombre, $nit, $departamento, $p_direccion, $p_celular) {
        $sql = "CALL AdicionarProveedor(?, ?, ?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([ $p_nombre, $nit, $departamento, $p_direccion, $p_celular]);
            echo "material registrado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al registrar: " . $ex->getMessage();
        }
    }

    // Método para buscar todos los proveedores
    public function BuscarTodo() {
        $sql = "CALL ObtenerProveedores()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para eliminar un proveedor
    public function Eliminar($id_proveedor) {
        $sql = "CALL EliminarProveedor(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_proveedor]);
            echo "material eliminado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al eliminar: " . $ex->getMessage();
        }
    }

    // Método para buscar proveedor por similitud
    public function buscarPorSimilitud($termino) {
        $sql = "CALL BuscarProveedor(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$termino]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    // Método para modificar un proveedor
    public function modificar($id_proveedor, $p_nombre, $nit, $departamento, $p_direccion, $p_celular) {
        $sql = "CALL CargarProveedor(?, ?, ?, ?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_proveedor, $p_nombre, $nit, $departamento, $p_direccion, $p_celular]);
            echo "Funcionario actualizado correctamente.";
        } catch (PDOException $ex) {
            echo "Error al actualizar: " . $ex->getMessage();
        }
    }

    // Método para buscar un proveedor por ID
    public function buscarPorId($id_proveedor) {
        $sql = "CALL buscarPorIdProveedor(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_proveedor]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }

}
?>
