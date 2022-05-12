<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/deleteContact', function (Request $request, Response $response, array $args) {
    $id_contact = $request->getParam('id_contact');
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

        $sql1 = "DELETE FROM `added_users` WHERE id_user_1 = '{$data->id_user}' and id_user_2 = '{$id_contact}'";

        $sql2 = "DELETE FROM `added_users` WHERE id_user_1 = '{$id_contact}' and id_user_2 = '{$data->id_user}'"; 

        $stmt = $cnn->query($sql1);
        $stmt2 = $cnn->query($sql2);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            
        }elseif (!$stmt2) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
        }else{
            $resp = '{"text": "Usuario borrado correctamente."}';
        }

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }


    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/loadContact', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_contact = $request->getParam('id_contact');

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT name, last_name_1, last_name_2, email, route_image FROM `users` WHERE id_user = '{$id_contact}'";

        $stmt = $cnn->query($sql);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
        }

        $ret=[];

        while ($row = $stmt->fetch_assoc())
            $ret[]= $row;

        $resp = json_encode($ret);

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }


    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/loadContactWishes', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_contact = $request->getParam('id_contact');

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT items.name, items.description, items.available, items.link, items.id_item
            FROM items INNER JOIN wish_list_users ON wish_list_users.id_item = items.id_item 
            WHERE wish_list_users.id_user = '{$id_contact}'";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
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

$app->post('/loadAllContacts', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    $search = $request->getParam('search');


    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT name, last_name_1, last_name_2, email, route_image, id_user
            FROM `users` where id_user != '{$data->id_user}' and id_user not in 
            (select id_user_2 from added_users where id_user_1 = '{$data->id_user}') 
            and id_user in (select id_user from users 
            where name like '%{$search}%' or last_name_1 like '%{$search}%' or last_name_2 like '%{$search}%' or email like '%{$search}%')
            and id_user not in 
            (select user_notif from notifications where   adding_user = '{$data->id_user}')";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
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


$app->post('/loadnoMembers', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    $id_group = $request->getParam('id_group');


    try{
        $cnn = $cnn->connect();
        
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT name, last_name_1, last_name_2, id_user from added_users INNER join users on id_user_2 = id_user 
            where id_user_1 = '{$data->id_user}' and id_user not in 
            (select id_user from users_groups where id_group = '{$id_group}')";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
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

$app->post('/addUserGroup', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $id_group = $request->getParam('id_group');
    $id_user_member = $request->getParam('id_user_member');

    try{
        $cnn = $cnn->connect();
        
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "INSERT INTO `users_groups` (`id_group`, `id_user`) VALUES ('{$id_group}', '{$id_user_member}')";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
        }

        $resp = '{"text": "Usuario agregado correctamente"}';

    } catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/deleteUserGroup', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $id_group = $request->getParam('id_group');
    $id_user_member = $request->getParam('id_user_member');

    try{
        $cnn = $cnn->connect();
        
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "DELETE FROM `users_groups` WHERE `users_groups`.`id_group` = '{$id_group}' AND `users_groups`.`id_user` = '{$id_user_member}'";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
        }

        $resp = '{"text": "Usuario eliminado correctamente"}';

    } catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});