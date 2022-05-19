<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/loadGroupWishes', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $id_group = $request->getParam("id_group"); 
        $sql = "SELECT name, description, link, available, items.id_item FROM items INNER JOIN wish_list_groups ON wish_list_groups.id_item = items.id_item 
            where wish_list_groups.id_group = '{$id_group}'";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error con la consulta a la base de datos, intentelo más tarde.");
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

$app->post('/loadGroupUsers', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $id_group = $request->getParam("id_group"); 
        $sql = "SELECT name, last_name_1, last_name_2, users.id_user FROM users INNER JOIN users_groups 
        ON users.id_user = users_groups.id_user where users_groups.id_group = '{$id_group}'";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error con la consulta a la base de datos, intentelo más tarde.");
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

$app->post('/createWishGroup', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $name = $request->getParam("wish_name");
    $descr =  $request->getParam("wish_description");
    $link =  $request->getParam("wish_link");
    $id_group = $request->getParam("id_group");

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        //guardar el item nuevo en la base de datos
        $sqlSaveItem = "INSERT INTO `items` (`name`, `description`, `link`, `available`) 
            VALUES ('{$request->getParam("wish_name")}', '{$request->getParam("wish_description")}', '{$request->getParam("wish_link")}', 
            'yes')";

        $stmt = $cnn->query($sqlSaveItem);
        if(!$stmt) {
            throw new Exception("Ha habido un error guardando el item, intentelo más tarde.");
        }

        //coger el id del item que acabamos de guardar
        $sqlIdItem = "SELECT `id_item` FROM `items` WHERE `name` = '{$request->getParam("wish_name")}' AND `description` = '{$request->getParam("wish_description")}'
            AND `link` = '{$request->getParam("wish_link")}'";
    
        $stmt1 = $cnn->query($sqlIdItem);
    
        if(!$stmt1) {
            throw new Exception("Ha habido un error seleccionando el id, intentelo más tarde.");
        }
        
        $ret = [];
        
        while ($row = $stmt1->fetch_assoc())
            $ret[]= $row;    
        
        //guardar la relacion de item-usuario en la base de datos
        $sqlSaveRelationship = "INSERT INTO `wish_list_groups` (`id_group`, `id_item`) VALUES ('{$id_group}', '{$ret[0]["id_item"]}')";

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