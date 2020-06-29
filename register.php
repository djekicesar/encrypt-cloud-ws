<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

// INCLUDING DATABASE AND MAKING OBJECT
require __DIR__.'/classes/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0,404,'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif(!isset($data->username) 
    || !isset($data->email) 
    || !isset($data->password)
    || !isset($data->csp)
    || !isset($data->encryptionkey) 
    || !isset($data->accestoken)
    || !isset($data->suid)
    || empty(trim($data->username))
    || empty(trim($data->email))
    || empty(trim($data->password))
    || empty(trim($data->csp))
    || empty(trim($data->encryptionkey))
    || empty(trim($data->accestoken))
    || empty(trim($data->suid))
    ):

    $fields = ['fields' => ['username','email','password', 'csp', 'encryptionkey', 'accestoken', 'suid']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $username = trim($data->username);
    $email = trim($data->email);
    $password = trim($data->password);
    $csp = trim($data->csp);
    $encryptionkey = trim($data->encryptionkey);
    $accestoken = trim($data->accestoken);
    $suid = trim($data->suid);

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0,422,'Invalid Email Address!');
    
    elseif(strlen($password) < 8):
        $returnData = msg(0,422,'Your password must be at least 8 characters long!');

    elseif(strlen($username) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    else:
        try{

            $check_username = "SELECT username FROM users WHERE username=:username";
            $check_username_stmt = $conn->prepare($check_username);
            $check_username_stmt->bindValue(':username', $username,PDO::PARAM_STR);
            $check_username_stmt->execute();

            if($check_username_stmt->rowCount()):
                $returnData = msg(0,422, 'This Username is already in use!');
            
            else:
                $insert_query = "INSERT INTO users(username,passwword,email,csp,encryptionkey,accestoken,suid) 
                VALUES(:username,:password,:email,:csp,:encryptionkey,:accestoken,:suid)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':username', htmlspecialchars(strip_tags($username)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT),PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email,PDO::PARAM_STR);
                $insert_stmt->bindValue(':csp', htmlspecialchars(strip_tags($csp)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':encryptionkey', htmlspecialchars(strip_tags($encryptionkey)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':accestoken', htmlspecialchars(strip_tags($accestoken)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':suid', htmlspecialchars(strip_tags($suid)),PDO::PARAM_STR);

                $insert_stmt->execute();

                $returnData = msg(1,201,'You have successfully registered.');

            endif;

        }
        catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
    endif;
    
endif;

echo json_encode($returnData);