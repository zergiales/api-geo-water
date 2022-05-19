<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->post('/selectWish', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_item = $request->getParam("id_item");

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "UPDATE items SET available = 'no' WHERE id_item = '{$id_item}'";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
        }


        $resp = '{"text": "Deseo seleccionado."}';

    } catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});


$app->get('/loadUserWishes', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));
    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT items.name, items.description, items.available, items.link, items.id_item
            FROM items INNER JOIN wish_list_users ON wish_list_users.id_item = items.id_item 
            WHERE wish_list_users.id_user = '{$data->id_user}'";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("2Ha habido un error, intentelo más tarde.");
        }

        $ret= [];
        
        while ($row = $stmt1->fetch_assoc())
            $ret[]= $row;  

            $resp = json_encode($ret);

    } catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/createWish', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
  try{
    $cnn = $cnn->connect();
    
    if(!$cnn){
      throw new Exception("Error al conectar con la base de datos.", 1);
    }

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));
    // var_dump($data->id_user);

    $name = $request->getParam("wish_name");
    $descr =  $request->getParam("wish_description");
    $link =  $request->getParam("wish_link");

    //guardar el item nuevo en la base de datos
    $sqlSaveItem = "INSERT INTO `items` (`name`, `description`, `link`, `available`) 
        VALUES ('{$request->getParam("wish_name")}', '{$request->getParam("wish_description")}', '{$request->getParam("wish_link")}', 
        'yes')";

    $stmt = $cnn->query($sqlSaveItem);
    if(!$stmt) {
        throw new Exception("Ha habido un error, intentelo más tarde.");
    }

    //coger el id del item que acabamos de guardar
    $sqlIdItem = "SELECT `id_item` FROM `items` WHERE `name` = '{$request->getParam("wish_name")}' AND `description` = '{$request->getParam("wish_description")}'
        AND `link` = '{$request->getParam("wish_link")}'";

    $stmt1 = $cnn->query($sqlIdItem);

    if(!$stmt1) {
        throw new Exception("1Ha habido un error, intentelo más tarde.");
    }
    
    $ret = [];
    
    while ($row = $stmt1->fetch_assoc())
        $ret[]= $row;    
    
    //guardar la relacion de item-usuario en la base de datos
    $sqlSaveRelationship = "INSERT INTO `wish_list_users` (`id_user`, `id_item`) VALUES ('{$data->id_user}', '{$ret[0]["id_item"]}')";

    $stmt2 = $cnn->query($sqlSaveRelationship);
    $cnn-> close(); 

    if(!$stmt2) {
        throw new Exception("3Ha habido un error, intentelo más tarde.");
    }



    $arr = [
        "text" => "Deseo guardado correctamente."
    ];

    $resp = json_encode($arr);

    } catch (Exception $e) {
        $response = $response->withStatus(400);
        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/deleteWish', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));
    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $id_item = $request->getParam("id_item");
        $id_user = $data->id_user;

        $sqlItem = "DELETE FROM items WHERE id_item = '{$id_item}'";
        $stmt2 = $cnn->query($sqlItem);

        if (!$stmt2) {
            throw new Exception("Ha ocurrido un error inexperado, por favor intentelo más tarde.");
        }

        $cnn->close();

        $resp = '{"text": "Datos eliminados correctamente"}';

    } catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;

});