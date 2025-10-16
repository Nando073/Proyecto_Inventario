<?php
require_once __DIR__ . '/../../DATOS/SOLICITUDES_D/D_Solicitud.php';
require_once __DIR__ . '/../../DATOS/D_Conepcion.php';
class N_Solicitud {

    // Método para adicionar a_descripcion
    public function registrarSolicitudConDetalles( $id_usuario, $detalle, $detalles_solicitud) {
        $NSolicitud = new D_Solicitud(); 
        $NSolicitud->RegistrarSolicitudConDetalles( $id_usuario, $detalle, $detalles_solicitud);  // Llamar al método de D_Solicitud
    }

    // Método para obtener todas las solicitudes con sus detalles
    public function obtenerSolicitudesCabecera() {
        $NSolicitud = new D_Solicitud();
        return $NSolicitud->ObtenerSolicitudesCabecera();
    }
    // Método para obtener los detalles de una solicitud por ID
    public function obtenerDetallesSolicitudes($id_solicitud) {
        $NSolicitud = new D_Solicitud();
        return $NSolicitud->ObtenerDetallesSolicitudes($id_solicitud);
    }

    //metodo para obtener todos los estados de las solicitudes
    public function obtenerEstadoSolicitud($id_solicitud) {
        $NSolicitud = new D_Solicitud();
        return $NSolicitud->ObtenerEstadoSolicitud($id_solicitud);
    }

    // Método para obtener las solicitudes de un usuario específico
    public function obtenerSolicitudesPorUsuario($id_usuario) {
        $NSolicitud = new D_Solicitud();
        return $NSolicitud->ObtenerSolicitudesPorUsuario($id_usuario);
    }


    // Método para eliminar un área por ID
    public function eliminarSolicitud($id_solicitud) {
        try {
            $NSolicitud = new D_Solicitud();
            $resultado = $NSolicitud->EliminarSolicitud($id_solicitud);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
    }


    // Método para buscar un area por ID
    public function buscarPorIdSolicitud($id_solicitud) {
        $NSolicitud = new D_Solicitud();
        return $NSolicitud->BuscarPorIdSolicitud($id_solicitud);
    }
    
    
    
   
    
}
?>
