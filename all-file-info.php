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
$files = [];
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0,404,'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif(!isset($data->userid) 
    || empty(trim($data->userid))
    ):

    $fields = ['fields' => ['userid']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $userid = trim($data->userid);

    if(strlen($userid) == 0):
        $returnData = msg(0,422,'Invalid User Id!');
    else:
        try{

            $fetch_file_by_filename = "SELECT `filename`,`contenthash`,`encrypted`,`integritycheck` 
            FROM `filemetadata` WHERE `userid` =:userid";
            $query_stmt = $conn->prepare($fetch_file_by_filename);
            $query_stmt->bindValue(':userid', $userid,PDO::PARAM_INT);
            $query_stmt->execute();

            if($query_stmt->rowCount()):
                $i=0;
                while($row = $query_stmt->fetch(PDO::FETCH_ASSOC)){
                    $files[$i]=$row;
                    $i++;
                }
                
                $returnData = msg(1,200,$files);
            else:
                $returnData = msg(1,201,''.$filename.' not found');
            endif;
        }catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
        
    endif;
    
endif;

echo json_encode($returnData);