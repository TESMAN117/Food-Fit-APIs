<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function msg($success, $status, $message, $extra = [])
{
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/classes/JwtHandler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT EQUAL TO POST
if ($_SERVER["REQUEST_METHOD"] != "POST") :
    $returnData = msg(0, 404, 'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif (
    !isset($data->vch_Email)
    || !isset($data->vch_Password)
    || empty(trim($data->vch_Password))
    || empty(trim($data->vch_Email))

) :

    $fields = ['fields' => ['vch_Password', 'vch_Email']];

    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :

    $vch_Password = trim($data->vch_Password);
    $vch_Email = trim($data->vch_Email);

    // CHECKING THE EMAIL FORMAT (IF INVALID FORMAT)
    if (!filter_var($vch_Email, FILTER_VALIDATE_EMAIL)) :
        $returnData = msg(0, 422, 'Invalid Email Address!');

    // IF PASSWORD IS LESS THAN 8 THE SHOW THE ERROR

    // THE USER IS ABLE TO PERFORM THE LOGIN ACTION 
    else :
        try {

            $fetch_user_by_email = "SELECT * from usuarios where usuarios.vch_Email =:vch_Email";
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':vch_Email', $vch_Email, PDO::PARAM_STR);
            $query_stmt->execute();

            // IF THE USER IS FOUNDED BY EMAIL
            if ($query_stmt->rowCount()) :
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);

                // login.php
            



                
                $check_password = password_verify($vch_Password, $row['vch_Password']);
               

                // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
                // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
                if ($check_password) :

                    $jwt = new JwtHandler();
                    $token = $jwt->_jwt_encode_data(
                        'http://localhost/php_auth_api/',
                        array("user_id" => $row['id'])
                    );
                   
                    $returnData = [
                        'success' => 1,
                        'message' => 'You have successfully logged in.',
                        'token' => $token
                        
                        
                    ];

                // IF INVALID PASSWORD
                else :
                    $returnData = msg(0, 422, 'Invalid Password!');
                endif;

            // IF THE USER IS NOT FOUNDED BY EMAIL THEN SHOW THE FOLLOWING ERROR
            else :
                $returnData = msg(0, 422, 'Invalid Email Address!');
            endif;
        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }

    endif;

endif;

echo json_encode($returnData);
