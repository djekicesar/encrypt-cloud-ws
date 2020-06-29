<?php
class Database{
    
    private $db_host = 'ec2-34-224-229-81.compute-1.amazonaws.com';
    private $db_name = 'd1cou21holpakh';
    private $db_username = 'jsdydixgxasdpu';
    private $db_password = '8bcfd0a836c26ac3de7cb1a321bbe8addd5058bcf414f56b46280ee02b8d75ba';
    private $db_port = 5432;
    
    public function dbConnection(){
        
        try{
            // $conn = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name,$this->db_username,$this->db_password);
            // $conStr = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s;sslmode=require", 
            // $db_host, 
            // $db_port, 
            // $db_name, 
            // $db_username, 
            // $db_password);

            // $conn = new \PDO($conStr);
            $dbUrl = getenv('postgres://jsdydixgxasdpu:8bcfd0a836c26ac3de7cb1a321bbe8addd5058bcf414f56b46280ee02b8d75ba@ec2-34-224-229-81.compute-1.amazonaws.com:5432/d1cou21holpakh');

            $dbopts = parse_url($dbUrl);

            $dbHost = $dbopts["host"];
            $dbPort = $dbopts["port"];
            $dbUser = $dbopts["user"];
            $dbPassword = $dbopts["pass"];
            $dbName = ltrim($dbopts["path"],'/');

            $conn = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e){
            echo "Connection error ".$e->getMessage(); 
            exit;
        }
          
    }
}