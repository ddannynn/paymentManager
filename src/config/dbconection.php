<?php

Class Connection{
    
    private $server    = "mysql:host=localhost;dbname=pmBD;charset=utf8";
//    private $server    = "mysql:host=localhost;dbname=hormasmo_checkin;charset=utf8";
    private $username  = "root";
    private $password  = "";
//    private $username  = 'hormasmo_5s4m';
//    private $password  = 'r14bP45eR46';
    private $options   = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,);
    protected $conn;
 	
    public function open() {
        try{
            $this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Hubo un problema con la conexión: " . $e->getMessage();
        }
    }
 
    public function close(){
        $this->conn = null;
    }

    public function apiKey($session_uid) {
//        $key = md5(SITE_KEY.$session_uid);
        $key = md5($session_uid);
        return hash('sha256', $key);
    }
 
} 
?>