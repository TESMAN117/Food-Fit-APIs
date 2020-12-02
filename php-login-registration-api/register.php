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

// INCLUDING DATABASE AND MAKING OBJECT
require __DIR__ . '/classes/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if ($_SERVER["REQUEST_METHOD"] != "POST") :
    $returnData = msg(0, 404, 'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif (
    !isset($data->vch_Nombres)
    || !isset($data->vch_A_Paterno)
    || !isset($data->vch_A_Materno)
    || !isset($data->vch_Direccion)
    || !isset($data->vch_Telefono)
    || !isset($data->vch_Nick)
    || !isset($data->vch_Password)
    | !isset($data->vch_Password2)
    || !isset($data->vch_Email)
    || empty(trim($data->vch_Nombres))
    || empty(trim($data->vch_A_Paterno))
    || empty(trim($data->vch_A_Materno))
    || empty(trim($data->vch_Direccion))
    || empty(trim($data->vch_Telefono))
    || empty(trim($data->vch_Nick))
    || empty(trim($data->vch_Password))
    || empty(trim($data->vch_Password2))
    || empty(trim($data->vch_Email))
) :

    $fields = ['fields' => ['vch_Nombres', 'vch_A_Paterno', 'vch_A_Materno', 'vch_Direccion', 'vch_Telefono', 'vch_Nick', 'vch_Password', 'vch_Password2', 'vch_Email']];
    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :

    $vch_Nombres = trim($data->vch_Nombres);
    $vch_A_Paterno = trim($data->vch_A_Paterno);
    $vch_A_Materno = trim($data->vch_A_Materno);
    $vch_Direccion = trim($data->vch_Direccion);
    $vch_Telefono = trim($data->vch_Telefono);
    $vch_Nick = trim($data->vch_Nick);
    $vch_Password = trim($data->vch_Password);
    $vch_Password2 = trim($data->vch_Password2);
    $vch_Email = trim($data->vch_Email);


    if (!filter_var($vch_Email, FILTER_VALIDATE_EMAIL)) :
        $returnData = msg(0, 422, 'Invalid Email Address!');

    elseif (strlen($vch_Password) < 8) :
        $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');

    elseif (strlen($vch_Nombres) < 0) :
        $returnData = msg(0, 422, 'Campo vacio !!!!');
    elseif (strlen($vch_A_Paterno) < 0) :
        $returnData = msg(0, 422, 'Campo vacio !!!!');
    elseif (strlen($vch_A_Materno) < 0) :
        $returnData = msg(0, 422, 'Campo vacio !!!!');
    elseif (strlen($vch_Direccion) < 0) :
        $returnData = msg(0, 422, 'Campo vacio !!!!');
    elseif (strlen($vch_Telefono) < 0) :
        $returnData = msg(0, 422, 'Campo vacio !!!!');
    elseif (strlen($vch_Nick) < 0) :
        $returnData = msg(0, 422, 'Campo vacio !!!!');
    elseif (strlen($vch_Password) != strlen($vch_Password2)) :
        $returnData = msg(0, 422, 'La pas no es igual xd');


    else :
        try {

            $check_email = "SELECT usuarios.vch_Email from usuarios where usuarios.vch_Email =:vch_Email";
            $check_email_stmt = $conn->prepare($check_email);
            $check_email_stmt->bindValue(':vch_Email', $vch_Email, PDO::PARAM_STR);
            $check_email_stmt->execute();

            if ($check_email_stmt->rowCount()) :
                $returnData = msg(0, 422, 'Este correo ya esta en uso, prueba con otro');

            else :
                $insert_person_query = "INSERT INTO personas
                (vch_Nombres,
                 vch_A_Paterno,
                 vch_A_Materno,
                 vch_Direccion,
                 vch_Telefono)
                VALUES (:vch_Nombres,
                        :vch_A_Paterno,
                        :vch_A_Materno,
                        :vch_Direccion,
                        :vch_Telefono);";
                $insert_person_stmt = $conn->prepare($insert_person_query);
                // DATA BINDING
                $insert_person_stmt->bindValue(':vch_Nombres', htmlspecialchars(strip_tags($vch_Nombres)), PDO::PARAM_STR);
                $insert_person_stmt->bindValue(':vch_A_Paterno', htmlspecialchars(strip_tags($vch_A_Paterno)), PDO::PARAM_STR);
                $insert_person_stmt->bindValue(':vch_A_Materno', htmlspecialchars(strip_tags($vch_A_Materno)), PDO::PARAM_STR);
                $insert_person_stmt->bindValue(':vch_Direccion', htmlspecialchars(strip_tags($vch_Direccion)), PDO::PARAM_STR);
                $insert_person_stmt->bindValue(':vch_Telefono', htmlspecialchars(strip_tags($vch_Telefono)), PDO::PARAM_STR);


                $insert_person_stmt->execute();

                $ulitmoID = $conn->lastInsertId();

                $insert_user_query = "INSERT INTO usuarios
                                            (vch_Nick,
                                            vch_Password,
                                            vch_Email,
                                            CLV_Persona)
                                      VALUES (:vch_Nick,
                                            :vch_Password,
                                            :vch_Email,
                                            $ulitmoID);";

                $insert_user_query = $conn->prepare($insert_user_query);

                // DATA BINDING

                
                
                $insert_user_query->bindValue(':vch_Nick', htmlspecialchars(strip_tags($vch_Nick)), PDO::PARAM_STR);
                $insert_user_query->bindValue(':vch_Password',password_hash($vch_Password,PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_user_query->bindValue(':vch_Email', $vch_Email, PDO::PARAM_STR);

                $insert_user_query->execute();

                $returnData = msg(1, 201, 'You have successfully registered.');

            endif;
        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }
    endif;

endif;

echo json_encode($returnData);
