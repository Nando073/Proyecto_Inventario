<?php
class D_Ingreso {
    private $id_ingreso;
    private $id_i_detalle;
    private $id_material;
    private $id_Ncategoria;
    private $i_precio;
    private $i_cantidad;
    private $i_subtotal;
    private $id_proveedor;
    private $total;
    private $con;

    // Constructor
    public function __construct($id_ingreso = 0, $id_i_detalle = null, $i_precio = null, $id_material = null, $id_Ncategoria = null, $i_cantidad = null, $i_subtotal = null, $id_proveedor = null, $total = null) {
        $this->id_ingreso = $id_ingreso;
        $this->id_i_detalle = $id_i_detalle;
        $this->id_material = $id_material;
        $this->id_Ncategoria = $id_Ncategoria;
        $this->i_precio = $i_precio;
        $this->i_cantidad = $i_cantidad;
        $this->i_subtotal = $i_subtotal;
        $this->id_proveedor = $id_proveedor;
        $this->total = $total;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_ingreso() { return $this->id_ingreso; }
    public function setId_ingreso($id_ingreso) { $this->id_ingreso = $id_ingreso; }

    public function getId_i_detalle() { return $this->id_i_detalle; }
    public function setId_i_detalle($id_i_detalle) { $this->id_i_detalle = $id_i_detalle; }

    public function getPrecio() { return $this->i_precio; }
    public function setPrecio($i_precio) { $this->i_precio = $i_precio; }

    public function getId_Nmaterial() { return $this->id_material; }
    public function setId_Nmaterial($id_material) { $this->id_material = $id_material; }

    public function getId_Ncategoria() { return $this->id_Ncategoria; }
    public function setId_Ncategoria($id_Ncategoria) { $this->id_Ncategoria = $id_Ncategoria; }

    public function getDescripcion() { return $this->i_cantidad; }
    public function setDescripcion($i_cantidad) { $this->i_cantidad = $i_cantidad; }

    public function getAfuncionario() { return $this->i_subtotal; }
    public function setAfuncionario($i_subtotal) { $this->i_subtotal = $i_subtotal; }

    public function getId_proveedor() { return $this->id_proveedor; }
    public function setId_proveedor($id_proveedor) { $this->id_proveedor = $id_proveedor; }

    public function getTotal() { return $this->total; }
    public function setTotal($total) { $this->total = $total; }

    // Método para adicionar un igreso
    public function RegistrarIngresoConDetalles($id_proveedor, $total, $detalles) {
        try {
            // Iniciar la transacción
            $this->con->beginTransaction();
    
            // 1. Llamar al procedimiento AdicionarIngreso
            $stmtIngreso = $this->con->prepare("CALL AdicionarIngreso(?, ?)");
            $stmtIngreso->execute([$id_proveedor, $total]);
    
            // 2. Obtener el ID generado por el INSERT (gracias al SELECT en el procedimiento)
            $resultado = $stmtIngreso->fetch(PDO::FETCH_ASSOC);
            if (!$resultado || !isset($resultado['id'])) {
                throw new Exception("No se pudo obtener el ID del ingreso.");
            }
            $idIngreso = $resultado['id'];
            $stmtIngreso->closeCursor(); // Muy importante para liberar el resultado del procedimiento
    
            // 3. Preparar el procedimiento para los detalles
            $stmtDetalle = $this->con->prepare("CALL AdicionarDetalle(?, ?, ?, ?, ?)");
    
            foreach ($detalles as $detalle) {
                // Ejecutar para cada detalle
                $stmtDetalle->execute([
                    $idIngreso,
                    $detalle['id_material'],
                    $detalle['precio'],
                    $detalle['cantidad'],
                    $detalle['sub_total']
                ]);
                $stmtDetalle->closeCursor(); // Liberar cada cursor para evitar errores
            }
    
            // 4. Confirmar la transacción
            $this->con->commit();
    
            return "Ingreso y detalles registrados correctamente.";
    
        } catch (PDOException $ex) {
            $this->con->rollBack(); // Revertir en caso de error
            throw new Exception("Error en la transacción: " . $ex->getMessage());
        }
    }


    
      // Método para buscar todos los igreso
      public function ObtenerIngresosRegistrado() {
        $sql = "CALL ObtenerIngresosRegistrado()"; // Asegúrate de que el procedimiento devuelva el campo 'total'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
    public function ObtenerDetallesIngresos() {
        $sql = "CALL ObtenerDetallesIngresos()"; // Asegúrate de que el procedimiento devuelva el campo 'total'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
   

    // Método para eliminar un igreso
        public function eliminarIngreso($id_ingreso) {
            $sql = "CALL EliminarIngresoLogico(?)";
            try {
                $ps = $this->con->prepare($sql);
                $ps->execute([$id_ingreso]);
            } catch (PDOException $ex) {
                throw new Exception("Error al eliminar el ingreso: " . $ex->getMessage());
            }
        }
         // Método para buscar ingreso por similitud
        public function buscarPorSimilitud($termino) {
            $sql = "CALL BuscarIngreso(?)";
            try {
                $ps = $this->con->prepare($sql);
                $ps->execute([$termino]);
                return $ps->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                echo "Error al buscar: " . $ex->getMessage();
                return [];
            }
        }
        //otener a los proveedores  y los materiales para mi formulario atrves del select
        public function obtenerProveedores() {
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

       
    //--------------------      detalle     ------------------
        //otener a los materiales para mi formulario atrves del select
        
        public function obtenerMateriales() {
            $sql = "CALL ObtenerMateriales()"; 
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
        }

}

?>