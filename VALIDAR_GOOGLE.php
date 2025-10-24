<?php
session_start();
require_once 'NEGOCIO/N_Funcionario.php';
require_once 'NEGOCIO/N_Usuario.php';
require_once 'NEGOCIO/N_RolUsuario.php';

if (isset($_POST['credential'])) {
    $jwt = $_POST['credential'];

    // Decodificar el JWT (payload)
    $partes = explode('.', $jwt);
    if (count($partes) !== 3) {
        echo "Token inválido.";
        exit;
    }

    $payload = $partes[1];
    $payload = str_replace(['-', '_'], ['+', '/'], $payload);
    $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
    $data = json_decode(base64_decode($payload));

    $correoGoogle = $data->email ?? null;

    if ($correoGoogle) {
        $funcionarioService = new N_Funcionario();
        $funcionario = $funcionarioService->buscarPorCorreo($correoGoogle);

        if ($funcionario && isset($funcionario['id_funcionario'])) {
            // Buscar usuario asociado al funcionario
            $usuarioService = new N_Usuario();
            $usuarioValido = $usuarioService->buscarPorFuncionario($funcionario['id_funcionario']);

            if ($usuarioValido && isset($usuarioValido['id_usuario'])) {
                // Crear sesión igual que el login normal
                $_SESSION['id_usuario'] = $usuarioValido['id_usuario'];
                $_SESSION['id_funcionario'] = $funcionario['id_funcionario'];
                $_SESSION['nombre_usuario'] = $usuarioValido['usuario'];
                $_SESSION['nombre_completo'] = $funcionario['f_nombre'] . ' ' . $funcionario['f_apellido'];

                // Traer roles
                $rolUsuarioService = new N_RolUsuario();
                $rolesUsuario = $rolUsuarioService->obtenerRolUsuarioAsignado($usuarioValido['id_usuario']);
                $rolesUsuario = array_column($rolesUsuario, 'r_nombre');
                $_SESSION['rol_asignado'] = $rolesUsuario;

                // Redirección según rol
                $paginaInicio = 'PRESENTACION/Inicio.php';
                if (in_array('Administrador', $rolesUsuario)) {
                    $paginaInicio = 'PRESENTACION/ADM_Usuario.php';
                } elseif (in_array('Operador', $rolesUsuario)) {
                    $paginaInicio = 'PRESENTACION/ADM_Material.php';
                } elseif (in_array('Supervisor', $rolesUsuario) || in_array('Funcionario', $rolesUsuario)) {
                    $paginaInicio = 'TRANSACCIONAL/CATALOGO/Generar_Solicitud.php';
                }

                header("Location: $paginaInicio");
                exit();
            } else {
                echo "No existe un usuario asociado a este funcionario.";
            }
        } else {
            echo "El correo de Google no coincide con ningún funcionario registrado.";
        }
    } else {
        echo "No se pudo obtener el correo de Google.";
    }
} else {
    echo "Credencial no recibida.";
}
?>
