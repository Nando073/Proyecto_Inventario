<?php
require_once __DIR__ . '/../DATOS/D_Funcionario.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';

class N_Funcionario {

    // Método para adicionar funcionario
    public function adicionar($f_nombre, $f_apellido, $f_correo, $area , $id_cargo, $CI, $complemento) {
        try {
            $Nfuncionario = new D_Funcionario(); 
            $resultado = $Nfuncionario->Adicionar($f_nombre, $f_apellido, $f_correo, $area , $id_cargo, $CI, $complemento);  // Llamar al método de D_Funcionario
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
        
    }
    

    // Método para buscar todos los funcionarios
    public function obtenerFuncionarios() {
        $Nfuncionario = new D_Funcionario();
        return $Nfuncionario->ObtenerFuncionarios();
    }

    // Método para eliminar un funcionario por ID
    public function eliminar($id_funcionario) {
        try {
            $Nfuncionario = new D_Funcionario();
            $resultado = $Nfuncionario->Eliminar($id_funcionario);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        }catch (Exception $e){
            throw $e;
        }
    }

    // Método para buscar funcionarios por similitud de término
    public function buscarPorSimilitud($termino) {
        $Nfuncionario = new D_Funcionario();
        return $Nfuncionario->buscarPorSimilitud($termino);
    }

    // Método para modificar un funcionario
    public function modificar($id_funcionario, $f_nombre, $f_apellido, $f_correo, $area, $id_cargo, $CI, $complemento) {
        try {
            $Dfuncionario = new D_Funcionario();
            $funcionarioExistente = $Dfuncionario->buscarPorId($id_funcionario);
            $resultado = $Dfuncionario->modificar($id_funcionario, $f_nombre, $f_apellido, $f_correo, $area,$id_cargo, $CI, $complemento);
            return ['success' => (bool)$resultado]; // Convierte 1/0 a true/false
        } catch (Exception $e) {
            throw $e;
        }
        
    }

    // Método para buscar un funcionario por ID
    public function buscarPorId($id_funcionario) {
        $Nfuncionario = new D_Funcionario();
        return $Nfuncionario->buscarPorId($id_funcionario);
    }
    // Método para activar un funcionario por ID
    public function activarFuncionario($id_funcionario) {
        $Dfuncionario = new D_Funcionario();
        return $Dfuncionario->activarFuncionario($id_funcionario);
    }

    public function ObtenerFuncionariosDisponibles($id_funcionario = null) {
        $d = new D_Funcionario();
        return $d->ObtenerFuncionariosDisponibles($id_funcionario);
    }

    // public function obtenerFuncionarioD(){
    //     $d = new D_Funcionario();
    //     return $d->ObtenerFuncionarioD();
    // }

    public function buscarPorCorreo($f_correo) {
        $d = new D_Funcionario();
        return $d->buscarPorCorreo($f_correo);
    }

}
?>
