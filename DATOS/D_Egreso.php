<?php
class D_Egreso {
    private $id_egreso;
    private $id_e_detalle;
    private $id_material_e;
    private $id_categoria_e;
    private $e_cantidad;
    private $e_total_cantidad;
    private $id_funcionario;
    private $e_solicitud;
    private $con;

    // Constructor


    public function __construct($id_egreso = null, $id_e_detalle = null, $id_material_e = null, $id_categoria_e = null, $e_cantidad = null, $e_total_cantidad = null, $id_funcionario = null, $e_solicitud = null) {
        $this->id_egreso = $id_egreso;
        $this->id_e_detalle = $id_e_detalle;
        $this->id_material_e = $id_material_e;
        $this->id_categoria_e = $id_categoria_e;
        $this->e_cantidad = $e_cantidad;
        $this->e_total_cantidad = $e_total_cantidad;
        $this->id_funcionario = $id_funcionario;
        $this->e_solicitud = $e_solicitud;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_ingreso() { return $this->id_egreso; }
    public function setId_ingreso($id_egreso) { $this->id_egreso = $id_egreso; }

    public function getId_e_detalle() { return $this->id_e_detalle; }
    public function setId_e_detalle($id_e_detalle) { $this->id_e_detalle = $id_e_detalle; }

    public function getId_Nmaterial() { return $this->id_material_e; }
    public function setId_Nmaterial($id_material_e) { $this->id_material_e = $id_material_e; }

    public function getId_Ncategoria() { return $this->id_categoria_e; }
    public function setId_Ncategoria($id_categoria_e) { $this->id_categoria_e = $id_categoria_e; }

    public function getCantidad() { return $this->e_cantidad; }
    public function setCantidad($e_cantidad) { $this->e_cantidad = $e_cantidad; }

    public function getTotalCantidad() { return $this->e_total_cantidad; }
    public function setTotalCantidad($e_total_cantidad) { $this->e_total_cantidad = $e_total_cantidad; }

    public function getId_funcionario() { return $this->id_funcionario; }
    public function setId_funcionario($id_funcionario) { $this->id_funcionario = $id_funcionario; }

    public function getE_solicitud() { return $this->e_solicitud; }
    public function setE_solicitud($e_solicitud) { $this->e_solicitud = $e_solicitud; }


    // Método para adicionar un egreso
    public function RegistrarEgresoConDetalles($id_funcionario, $e_solicitud, $e_total_cantidad, $detalles) {
        try {
            // Iniciar la transacción
            $this->con->beginTransaction();
    
            // 1. Llamar al procedimiento Adicionaregreso
            $stmtEgreso = $this->con->prepare("CALL AdicionarEgreso(?, ?, ?)");
            $stmtEgreso->execute([$id_funcionario, $e_solicitud, $e_total_cantidad]);
    
            // 2. Obtener el ID generado por el INSERT (gracias al SELECT en el procedimiento)
            $resultado = $stmtEgreso->fetch(PDO::FETCH_ASSOC);
            if (!$resultado || !isset($resultado['id'])) {
                throw new Exception("No se pudo obtener el ID del egreso.");
            }
            $idEgreso = $resultado['id'];
            $stmtEgreso->closeCursor(); // Muy importante para liberar el resultado del procedimiento
    
            // 3. Preparar el procedimiento para los detalles
            $stmtDetalle = $this->con->prepare("CALL AdicionarDetalleEgreso(?, ?, ?)");
    
            foreach ($detalles as $detalle) {
                // Ejecutar para cada detalle
                $stmtDetalle->execute([
                    $idEgreso,
                    $detalle['id_material_e'],
                    $detalle['e_stock'],
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


    
      // Método para buscar todos los egreso
      public function ObtenerEgresosRegistrado() {
        $sql = "CALL ObtenerEgresosRegistrado()"; // Asegúrate de que el procedimiento devuelva el campo 'e_total_cantidad'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
    public function ObtenerDetallesEgresos() {
        $sql = "CALL ObtenerDetallesEgresos()"; // Asegúrate de que el procedimiento devuelva el campo 'e_total_cantidad'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
   

    // Método para eliminar un egreso
        public function eliminarEgreso($id_egreso) {
            $sql = "CALL EliminarEgresoLogico(?)";
            try {
                $ps = $this->con->prepare($sql);
                $ps->execute([$id_egreso]);
            } catch (PDOException $ex) {
                throw new Exception("Error al eliminar el ingreso: " . $ex->getMessage());
            }
        }
         // Método para buscar ingreso por similitud
        public function buscarPorSimilitud($termino) {
            $sql = "CALL BuscarEngreso(?)";
            try {
                $ps = $this->con->prepare($sql);
                $ps->execute([$termino]);
                return $ps->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                echo "Error al buscar: " . $ex->getMessage();
                return [];
            }
        }
     
       
    //--------------------      detalle     ------------------
        //otener a los materiales para mi formulario atrves del select
        
        public function obtenerMateriales() {
            $query = "SELECT 
                m.id_material, 
                m.m_nombre, 
                m.stock, 
                c.id_categoria, 
                c.c_nombre AS categoria_nombre
            FROM material m
            JOIN categoria c ON m.id_categoria = c.id_categoria
            WHERE m.m_estado = 1
            AND m.stock > 0
            ORDER BY c.c_nombre, m.m_nombre;";
            $stmt = $this->con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
         //otener a los funcionario  y los materiales para mi formulario atrves del select
        public function obtenerFuncionarios() {
            $query = "SELECT 
                f.id_funcionario, 
                f.f_nombre, 
                a.id_area,
                a.a_nombre AS area_nombre
            FROM funcionario f
            JOIN area a ON f.area = a.id_area
            WHERE f.f_estado = 1
            AND a.a_funcionarios > 0
            ORDER BY a.a_nombre, f.f_nombre;";
            $stmt = $this->con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
}

?>