<?php

class D_coneccion {
    private $bdnombre;
    private $servidor;
    private $puerto;
    private $usuario;
    private $clave;
    private $con = null;

    public function __construct() {
        $this->bdnombre = "dde";
        $this->servidor = "localhost";
        $this->puerto = "3310";
        $this->usuario = "root";
        $this->clave = "";
    }

    public function Conectar() {
        $dsn = "mysql:host={$this->servidor};port={$this->puerto};dbname={$this->bdnombre}";
        try {
            $this->con = new PDO($dsn, $this->usuario, $this->clave);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch (PDOException $ex) {
            echo "Error en la conexión: " . $ex->getMessage();
        }
        return $this->con;
    }
   
}
