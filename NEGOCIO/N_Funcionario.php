<?php
require_once __DIR__ . '/../DATOS/D_Funcionario.php';
require_once __DIR__ . '/../DATOS/D_Conepcion.php';

class N_Funcionario {

    // Método para adicionar funcionario
    public function adicionar($f_nombre, $f_apellido, $f_correo, $area , $id_cargo, $CI, $complemento) {
        $Nfuncionario = new D_Funcionario(); 
        $Nfuncionario->Adicionar($f_nombre, $f_apellido, $f_correo, $area , $id_cargo, $CI, $complemento);  // Llamar al método de D_Funcionario
    }
    

    // Método para buscar todos los funcionarios
    public function obtenerFuncionarios() {
        $Nfuncionario = new D_Funcionario();
        return $Nfuncionario->ObtenerFuncionarios();
    }

    // Método para eliminar un funcionario por ID
    public function eliminar($id_funcionario) {
        $Nfuncionario = new D_Funcionario();
        $Nfuncionario->Eliminar($id_funcionario);
    }

    // Método para buscar funcionarios por similitud de término
    public function buscarPorSimilitud($termino) {
        $Nfuncionario = new D_Funcionario();
        return $Nfuncionario->buscarPorSimilitud($termino);
    }

    // Método para modificar un funcionario
    public function modificar($id_funcionario, $f_nombre, $f_apellido, $f_correo, $area, $id_cargo, $CI, $complemento) {
        $Dfuncionario = new D_Funcionario();
        $funcionarioExistente = $Dfuncionario->buscarPorId($id_funcionario);
        $Dfuncionario->modificar($id_funcionario, $f_nombre, $f_apellido, $f_correo, $area,$id_cargo, $CI, $complemento);
    }

    // Método para buscar un funcionario por ID
    public function buscarPorId($id_funcionario) {
        $Nfuncionario = new D_Funcionario();
        return $Nfuncionario->buscarPorId($id_funcionario);
    }
    
    public function obtenerAreas() {
        $Dfuncionario = new D_Funcionario();
        return $Dfuncionario->obtenerAreas();
    }
    public function obtenerCargos() {
        $Dfuncionario = new D_Funcionario();
        return $Dfuncionario->obtenerCargos();
    }
    
}
?>
