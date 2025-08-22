<?php
require_once __DIR__ . '/../DATOS/D_Usuario.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';

class N_Usuario {

    // Método para adicionar usuario
    public function adicionar($usuario, $clave, $id_funcionario) {
        $Nusuario = new D_Usuario();
        $Nusuario->Adicionar($usuario, $clave, $id_funcionario);
    }

    // Método para buscar todos los usuarios
    public function buscarTodo() {
        $Nusuario = new D_Usuario();
        return $Nusuario->BuscarTodo();
    }

    // Método para eliminar un usuario por ID
    public function eliminar($id_usuario) {
        $Nusuario = new D_Usuario();
        $Nusuario->Eliminar($id_usuario);
    }

    // Método para buscar usuarios por similitud de término
    public function buscarPorSimilitud($termino) {
        $Nusuario = new D_Usuario();
        return $Nusuario->buscarPorSimilitud($termino);
    }

    // Método para modificar un usuario
    public function modificar($id_usuario, $usuario, $clave, $id_funcionario) {
        $Dusuario = new D_Usuario();
        $Dusuario->modificar($id_usuario, $usuario, $clave, $id_funcionario);
    }

    // Método para buscar un usuario por ID
    public function buscarPorId($id_usuario) {
        $Nusuario = new D_Usuario();
        return $Nusuario->buscarPorId($id_usuario);
    }

    public function obtenerFuncionarios() {
        $Nusuario = new D_Usuario();
        return $Nusuario->obtenerFuncionario();
    }

    // Método para buscar un usuario por nombre
public function loguear($usuario, $clave) {
    $d = new D_Usuario();
    $usuarioDb = $d->buscarPorUsuario($usuario);

    if ($usuarioDb && isset($usuarioDb['estado'])) {
        if ($usuarioDb['estado'] != 1) {
            echo "<script>alert('El usuario no está activo. Por favor, contacte al administrador.');</script>";
            return false;
        }
        if (password_verify($clave , $usuarioDb['clave'])) {
            return $usuarioDb; // <-- Devuelve los datos del usuario
        } else {
            echo "<script>alert('Contraseña incorrecta.');</script>";
            return false;
        }
    }

    echo "<script>alert('Usuario o clave incorrectos.');</script>";
    return false;
}
    
}
?>
