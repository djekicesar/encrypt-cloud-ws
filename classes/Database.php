<?php
class Database{
    
    private $db_host = 'ec2-34-224-229-81.compute-1.amazonaws.com';
    private $db_name = 'd1cou21holpakh';
    private $db_username = 'jsdydixgxasdpu';
    private $db_password = '8bcfd0a836c26ac3de7cb1a321bbe8addd5058bcf414f56b46280ee02b8d75ba';
    private $db_port = 5432;
    
    public function dbConnection(){
        
        try{
            $conn = new PDO("pgsql:host=ec2-34-224-229-81.compute-1.amazonaws.com;port=5432;dbname=d1cou21holpakh", 'jsdydixgxasdpu', '8bcfd0a836c26ac3de7cb1a321bbe8addd5058bcf414f56b46280ee02b8d75ba');
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e){
            echo "Connection error ".$e->getMessage(); 
            exit;
        }
          
    }
}