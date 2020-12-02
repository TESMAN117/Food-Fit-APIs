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
require __DIR__ . '/middlewares/Auth.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();


$allHeaders = getallheaders();

$conn = $db_connection->dbConnection();
$auth = new Auth($conn, $allHeaders);
$usuarioid = 0;
if ($auth->isAuth()) {
    $usuarioid = $auth->idusuario;
}

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if ($_SERVER["REQUEST_METHOD"] != "POST") :
    $returnData = msg(0, 404, 'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif (
    !isset($data->cantidad)
    || !isset($data->idplatillo)
    || empty(trim($data->cantidad))
    || empty(trim($data->idplatillo))
) :

    $fields = ['fields' => ['cantidad', 'idplatillo']];
    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :

    $cantidad = trim($data->cantidad);
    $idplatillo = trim($data->idplatillo);
    $precio_platillo = 0;



    try {

        $numero_aleatorio = rand(1, 1000);
        $num_pedido = 'pedido_' . $numero_aleatorio;

        $check_num_pedido = "SELECT pedidos.vch_Num_Venta FROM pedidos WHERE pedidos.vch_Num_Venta = :num_pedido;";
        $check_num_pedido_stmt = $conn->prepare($check_num_pedido);
        $check_num_pedido_stmt->bindValue(':num_pedido', $num_pedido, PDO::PARAM_STR);
        $check_num_pedido_stmt->execute();
        if ($check_num_pedido_stmt->rowCount()) {
            $row = $check_num_pedido_stmt->fetch(PDO::FETCH_ASSOC);
            $npedido = $row['vch_Num_Venta'];
            list($list_pedido, $list_num) = explode("_", $npedido);
            echo $list_pedido; // pedido_
            echo $list_num; // numero pedido
            $num = rand(1, 1000);
            while ($list_num != $num) {
                $num_pedido = 'pedido_' . $num;
            }
        }

        $Select_platillo = "SELECT * FROM platillos WHERE platillos.id =$idplatillo;";
        $Select_platillo_stmt = $conn->prepare($Select_platillo);
        $Select_platillo_stmt->execute();
        if ($Select_platillo_stmt->rowCount()) {
            $fila = $Select_platillo_stmt->fetch(PDO::FETCH_ASSOC);
            $precio_platillo = $cantidad * $fila['flt_Precio'];
            $flt_precio = $fila['flt_Precio'];
            $vch_nombre = $fila['vch_Nombre'];
            $vch_presentacion = $fila['vch_Presentacion'];
        }

        $fecha_pedido = date("Y") . date("m") . date("d");

        $insert_person_query = "INSERT INTO pedidos
                                                (vch_Num_Venta,
                                                flt_Total,
                                                vch_Estado,
                                                date_Fecha_Pedido,
                                                CLV_Usuario)
                                                values (
                                                '$num_pedido',
                                                $precio_platillo,
                                                'En proceso',
                                                '$fecha_pedido',
                                                $auth->idusuario);";
        $insert_person_stmt = $conn->prepare($insert_person_query);



        $insert_person_stmt->execute();
        $ulitmoID = $conn->lastInsertId();;

        $insert_detalle_query = "INSERT INTO detalle_pedidos
        (
         CLV_Pedido,
         CLV_Platillo,
         Vch_Nombre_P_d,
         Vch_Presentacion_P_d,
         int_Cantidad_d,
         flt_Precio_d,
         date_Fecha_Pedido_d)
VALUES (
    $ulitmoID,
    $idplatillo,
    '$vch_nombre',
    '$vch_presentacion',
    $cantidad,
    $flt_precio,
    '$fecha_pedido');";
        $insert_detalle_stmt = $conn->PREPARE($insert_detalle_query);
        $insert_detalle_stmt->EXECUTE();






        $returnData = msg(1, 201, 'Pedido Realizado');
    } catch (PDOException $e) {
        $returnData = msg(0, 500, 'hola' . $e->getMessage());
    }


endif;

echo json_encode($returnData);
