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
elseif(!isset($data->userid) 
    || !isset($data->filename) 
    || !isset($data->contenthash)
    || !isset($data->encrypted)
    || !isset($data->integritycheck) 
    || empty(trim($data->userid))
    || empty(trim($data->filename))
    || empty(trim($data->contenthash))
    || empty(trim($data->encrypted))
    || empty(trim($data->integritycheck))
    ):

    $fields = ['fields' => ['userid','filename','contenthash', 'encrypted', 'integritycheck']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $userid = trim($data->userid);
    $filename = trim($data->filename);
    $contenthash = trim($data->contenthash);
    $encrypted = trim($data->encrypted);
    $integritycheck = trim($data->integritycheck);

    if(strlen($userid) < 0):
        $returnData = msg(0,422,'Invalid User Id!');
    
    elseif(strlen($contenthash) < 8):
        $returnData = msg(0,422,'Your Contenthash must be at least 8 characters long!');

    else:
        try{

            $check_filename = "SELECT `filename` FROM `filemetadata` WHERE `filename`=:filename";
            $check_username_stmt = $conn->prepare($check_filename);
            $check_username_stmt->bindValue(':filename', $filename,PDO::PARAM_STR);
            $check_username_stmt->execute();

            if($check_username_stmt->rowCount()):
                $returnData = msg(0,422, ''.$filename.' already existe!');
            
            else:
                $insert_query = "INSERT INTO `filemetadata`(`userid`,`filename`,`contenthash`,`encrypted`,`integritycheck`) 
                VALUES(:userid,:filename,:contenthash,:encrypted,:integritycheck)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':userid', htmlspecialchars(strip_tags($userid)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':filename', htmlspecialchars(strip_tags($filename)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':contenthash', htmlspecialchars(strip_tags($contenthash)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':encrypted', htmlspecialchars(strip_tags($encrypted)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':integritycheck', htmlspecialchars(strip_tags($integritycheck)),PDO::PARAM_STR);

                $insert_stmt->execute();

                $returnData = msg(1,201,''.$filename.' successfully recorded.');

            endif;

        }
        catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
    endif;
    
endif;

echo json_encode($returnData);