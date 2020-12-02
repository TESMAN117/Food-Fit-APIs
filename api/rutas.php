<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response, $args) {
  $response->getBody()->write("Hello world!");
  return $response;
});

$app->get('/categorias', function (Request $request, Response $response) {
  $sql = "SELECT * FROM categorias";
  try {
    $db = new db();
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0) {
      $categoria = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($categoria);
    } else {
      echo json_encode("No existen Categorias en la BBDD.");
    }
    $resultado = null;
    $db = null;
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }

  return $response;
});

$app->get('/categorias/{id}', function (Request $request, Response $response, array $args) {
  $id_categoria = $args['id'];
  $sql = "SELECT * FROM categorias WHERE id = $id_categoria";
  try {
    $db = new db();
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0) {
      $categoria = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($categoria);
    } else {
      echo json_encode("No existen cliente en la BBDD con este ID.");
    }
    $resultado = null;
    $db = null;
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
  return $response;
});

$app->get('/platillo_cat/{id}', function (Request $request, Response $response, array $args) {
  $id_platillo = $args['id'];
  $sql = "SELECT * FROM platillos WHERE CLV_Categoria = $id_platillo";
  try {
    $db = new db();
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0) {
      $platillo = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($platillo);
    } else {
      echo json_encode("No existen cliente en la BBDD con este ID.");
    }
    $resultado = null;
    $db = null;
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
  return $response;
});

$app->get('/platillo_pedido/{id}', function (Request $request, Response $response, array $args) {
  $id_platillo = $args['id'];
  $sql = "SELECT
    platillos.id
    ,platillos.vch_Nombre
    ,platillos.vch_Presentacion
    ,platillos.flt_Precio
    ,platillos.CLV_Categoria
    ,platillos.vch_Imagen
    ,categorias.id
    ,categorias.vch_Categoria
    
    FROM platillos INNER JOIN categorias ON
    platillos.`CLV_Categoria` = categorias.`id`
    WHERE platillos.`id` =  $id_platillo";
  try {
    $db = new db();
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0) {
      $platillo = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($platillo);
    } else {
      echo json_encode("No existen cliente en la BBDD con este ID.");
    }
    $resultado = null;
    $db = null;
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
  return $response;
});

$app->get('/orden/{id}', function (Request $request, Response $response, array $args) {
  $id_usuario = $args['id'];
  $sql = "SELECT
  pedidos.id
 ,pedidos.vch_Num_Venta
 ,pedidos.flt_Total
 ,pedidos.vch_Estado
 ,pedidos.date_Fecha_Pedido
 ,pedidos.CLV_Usuario
 ,usuarios.vch_Nick
 ,usuarios.CLV_Persona
 ,personas.vch_Nombres
 ,personas.vch_A_Paterno
 ,personas.vch_A_Materno
 ,personas.vch_Direccion
 ,personas.vch_Telefono
 FROM pedidos
 INNER JOIN usuarios ON pedidos.CLV_Usuario = usuarios.id
 INNER JOIN personas ON usuarios.CLV_Persona = personas.id
 WHERE usuarios.id =$id_usuario";
  try {
    $db = new db();
    $db = $db->conectDB();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0) {
      $platillo = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($platillo);
    } else {
      echo json_encode("No existen Pedidos en la BBDD con este ID.");
    }
    $resultado = null;
    $db = null;
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
  return $response;
});
