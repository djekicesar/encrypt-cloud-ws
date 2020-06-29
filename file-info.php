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
    || empty(trim($data->userid))
    || empty(trim($data->filename))
    ):

    $fields = ['fields' => ['userid','filename']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $userid = trim($data->userid);
    $filename = trim($data->filename);

    if(strlen($userid) == 0):
        $returnData = msg(0,422,'Invalid User Id!');
    
    elseif(strlen($filename) == 0):
        $returnData = msg(0,422,'Inavlid Filename!');

    else:
        try{

            $fetch_file_by_filename = "SELECT filename,contenthash,encrypted,integritycheck 
            FROM filemetadata WHERE filename=:filename AND userid =:userid";
            $query_stmt = $conn->prepare($fetch_file_by_filename);
            $query_stmt->bindValue(':filename', $filename,PDO::PARAM_STR);
            $query_stmt->bindValue(':userid', $userid,PDO::PARAM_INT);
            $query_stmt->execute();

            if($query_stmt->rowCount()):
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $returnData = msg(1,200,$row);
            else:
                $returnData = msg(1,201,''.$filename.' not found');
            endif;
        }catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
        
    endif;
    
endif;

echo json_encode($returnData);