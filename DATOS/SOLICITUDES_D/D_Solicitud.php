<?php
require_once __DIR__ . '/../D_Conepcion.php';
class D_Solicitud {
    private $id_solicitud;
    private $id_usuario;
    private $id_material;
    private $cantidad;
    private $detalle;
    private $con;

    // Constructor
    public function __construct($id_solicitud = 0, $id_usuario = 0, $id_material = 0, $cantidad = 0, $detalle = "Default detalle") {
        $this->id_solicitud = $id_solicitud;
        $this->id_usuario = $id_usuario;
        $this->id_material = $id_material;
        $this->cantidad = $cantidad;
        $this->detalle = $detalle;
        $this->con = (new D_coneccion())->Conectar(); // Inicializar conexión
    }

    // Getters y Setters
    public function getId_solicitud() { return $this->id_solicitud; }
    public function setId_solicitud($id_solicitud) { $this->id_solicitud = $id_solicitud; }

    public function getId_usuario() { return $this->id_usuario; }
    public function setId_usuario($id_usuario) { $this->id_usuario = $id_usuario; }

    public function getId_material() { return $this->id_material; }
    public function setId_material($id_material) { $this->id_material = $id_material; }

    public function getCantidad() { return $this->cantidad; }
    public function setCantidad($cantidad) { $this->cantidad = $cantidad; }

    public function getDetalle() { return $this->detalle; }
    public function setDetalle($detalle) { $this->detalle = $detalle; }

    public function getAfuncionario() { return $this->a_funcionarios; }
    public function setAfuncionario($a_funcionarios) { $this->a_funcionarios = $a_funcionarios; }

   public function RegistrarSolicitudConDetalles($id_usuario, $detalle, $detalles_solicitud) {
    try {
        // Iniciar transacción
        $this->con->beginTransaction();

        // 1️⃣ Registrar la solicitud y obtener el ID
        $stmtSolicitud = $this->con->prepare("CALL AdicionarSolicitud(?, ?)");
        $stmtSolicitud->execute([$id_usuario, $detalle]);

        $resultado = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);
        $stmtSolicitud->closeCursor(); // Muy importante para liberar el cursor
        $idSolicitud = $resultado['id'] ?? null;

        if (!$idSolicitud) {
            throw new Exception("No se pudo obtener el ID de la solicitud.");
        }

        // 2️⃣ Registrar el estado inicial (1 = Enviado, comentario por defecto)
        $stmtEstado = $this->con->prepare("CALL AdicionarEstadoSolicitud(?, ?, ?, ?)");
        $stmtEstado->execute([
            $idSolicitud,   // id_solicitud
            $id_usuario,    // id_usuario que registra
            1,              // estado inicial = Enviado
            'Solicitud registrada' // comentario por defecto
        ]);
        $stmtEstado->closeCursor();

        // 3️⃣ Registrar los detalles de la solicitud
        $stmtDetalle = $this->con->prepare("CALL AdicionarDetalleSolicitud(?, ?, ?)");
        foreach ($detalles_solicitud as $detalles) {
            $stmtDetalle->execute([
                $idSolicitud,
                $detalles['id_material'],
                $detalles['cantidad']
            ]);
        }
        $stmtDetalle->closeCursor();

        // Confirmar la transacción
        $this->con->commit();

        return "Solicitud, estado y detalles registrados correctamente.";

    } catch (PDOException $ex) {
        $this->con->rollBack();
        throw new Exception("Error en la transacción: " . $ex->getMessage());
    }
}


   // Método para obtener todas las solicitudes con sus detalles
    public function ObtenerSolicitudesCabecera() {
        $sql = "CALL ObtenerSolicitudesCabecera()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al obtener solicitudes: " . $ex->getMessage();
            return [];
        }
    }

    public function ObtenerDetallesSolicitudes($id_solicitud) {
        $sql = "CALL ObtenerDetalleSolicitud(?)"; // Asegúrate de que el procedimiento devuelva el campo 'total'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_solicitud]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }

    public function ObtenerEstadoSolicitud($id_solicitud) {
        $sql = "CALL ObtenerEstadoSolicitud(?)"; // Asegúrate de que el procedimiento devuelva el campo 'total'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_solicitud]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
    

    public function obtenerSolicitudesPorUsuario($id_usuario) {
        $sql = "CALL obtenerSolicitudesPorUsuario(?)"; // Asegúrate de que el procedimiento devuelva el campo 'total'
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_usuario]);
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar: " . $ex->getMessage();
            return [];
        }
    }
    
    // Método para eliminar un solicitud
    public function EliminarSolicitud($id_solicitud) {
        $sql = "CALL EliminarSolicitud(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_solicitud]);
            // Obtener el resultado del procedimiento
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            return $resultado['success']; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            throw new Exception("Error al eliminar solicitud: " . $ex->getMessage());
        }
    }

    // Método para buscar un area por ID
    public function BuscarPorIdSolicitud($id_solicitud) {
        $sql = "CALL BuscarPorIdSolicitud(?)";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute([$id_solicitud]);
            return $ps->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al buscar por ID: " . $ex->getMessage();
            return null;
        }
    }
    
    

}
?>
