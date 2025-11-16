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

        // 2 Registrar los detalles de la solicitud
        $stmtDetalle = $this->con->prepare("CALL AdicionarDetalleSolicitud(?, ?, ?)");
        foreach ($detalles_solicitud as $detalles) {
            $stmtDetalle->execute([
                $idSolicitud,
                $detalles['id_material'],
                $detalles['cantidad']
            ]);
        }
        $stmtDetalle->closeCursor();
        
        // 3 Registrar el estado inicial (1 = Enviado, comentario por defecto)
        $stmtEstado = $this->con->prepare("CALL AdicionarEstadoSolicitud(?, ?, ?, ?)");
        $stmtEstado->execute([
            $idSolicitud,   // id_solicitud
            $id_usuario,    // id_usuario que registra
            1,              // estado inicial = Enviado
            'Solicitud registrada' // comentario por defecto
        ]);
        $stmtEstado->closeCursor();

        // Confirmar la transacción
        $this->con->commit();

        return "Solicitud, estado y detalles registrados correctamente.";

    } catch (PDOException $ex) {
        $this->con->rollBack();
        throw new Exception("Error en la transacción: " . $ex->getMessage());
    }
}

// Método para aprobar una solicitud con sus detalles
    public function AprobarSolicitudConDetalles($id_solicitud, $id_usuario, $detalle, $detalles_solicitud) {
        try {
            $this->con->beginTransaction();

            // 1 Aprobar la solicitud (UPDATE) - NO enviar id_usuario para preservar el solicitante original
            $stmtSolicitud = $this->con->prepare("CALL AprobarSolicitud(?)");
            $stmtSolicitud->execute([$id_solicitud]);
            $stmtSolicitud->closeCursor();

            // 2 Aprobar los detalles asociados y eliminar los que no están en la lista
            // Construir lista de IDs de materiales aprobados
            $ids_materiales_aprobados = array_map(function($item) {
                return intval($item['id_material']);
            }, $detalles_solicitud);
            
            $ids_string = implode(',', $ids_materiales_aprobados);
            error_log("IDs de materiales aprobados: " . $ids_string);
            
            // Llamar al procedimiento que actualiza/elimina según los IDs enviados
            $stmtDetalle = $this->con->prepare("CALL AprobarDetalleSolicitud(?, ?)");
            $stmtDetalle->execute([
                $id_solicitud,
                $ids_string  // Lista de IDs separados por coma: "1,3,5"
            ]);
            $stmtDetalle->closeCursor();
            
            // 2.1 Actualizar las cantidades de los materiales aprobados
            $stmtActualizar = $this->con->prepare("CALL ActualizarCantidadDetalleSolicitud(?, ?, ?)");
            foreach ($detalles_solicitud as $detalleItem) {
                $stmtActualizar->execute([
                    $id_solicitud,
                    $detalleItem['id_material'],
                    $detalleItem['cantidad']
                ]);
            }
            $stmtActualizar->closeCursor();

            // 3 Registrar el nuevo estado - SÍ enviar id_usuario para auditar quién aprobó
            $stmtEstado = $this->con->prepare("CALL AprobarEstadoSolicitud(?, ?, ?, ?)");
            $stmtEstado->execute([
                $id_solicitud,
                $id_usuario,    // Usuario que APRUEBA (supervisor)
                2,              // Estado aprobado
                $detalle        // Comentario del supervisor
            ]);
            $stmtEstado->closeCursor();

            $this->con->commit();
            return "Solicitud, estado y detalles actualizados correctamente.";

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
    
    // Método para eliminar un solicitud (eliminado lógico)
    public function EliminarSolicitud($id_solicitud, $id_usuario, $comentario) {
        $sql = "CALL RechazarSolicitud(?, ?, ?)";
        try {
            $ps = $this->con->prepare($sql);
            // El orden de parámetros según tu procedimiento: id_usuario, id_solicitud, comentario
            $ps->execute([$id_usuario, $id_solicitud, $comentario]);
            // Obtener el resultado del procedimiento
            $resultado = $ps->fetch(PDO::FETCH_ASSOC);
            $ps->closeCursor(); // Liberar el cursor
            return $resultado['success']; // Devuelve 1 o 0
        } catch (PDOException $ex) {
            throw new Exception("Error al eliminar solicitud: " . $ex->getMessage());
        }
    }

    // Método para obtener todas las solicitudes aprobadas
    public function ObtenerSolicitudesAprobadas() {
        $sql = "CALL obtenerSolicitudesAprobadas()";
        try {
            $ps = $this->con->prepare($sql);
            $ps->execute();
            return $ps->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            echo "Error al obtener solicitudes aprobadas: " . $ex->getMessage();
            return [];
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
