<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/checkOwnership', function (Request $request, Response $response, array $args) {
    $id_group = $request->getParam('id_group');
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

        $sql1 = "SELECT name FROM `groups` where id_user_owner = '{$data->id_user}' and id_group = '{$id_group}'";

        $stmt = $cnn->query($sql1);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
        }else{
            if($stmt->num_rows === 1) {
                $resp = '{"text": "Propietario correcto"}';
            } else {
                throw new Exception("No es el propietario del grupo");
                
            }
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }


    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/checkFriendship', function (Request $request, Response $response, array $args) {
    $id_user = $request->getParam('id_user');
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

        $sql1 = "SELECT id_user_2 from added_users where id_user_1 = '{$data->id_user}' and id_user_2 = '{$id_user}'";

        $stmt = $cnn->query($sql1);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
        }else{
            if($stmt->num_rows === 1) {
                $resp = '{"text": "Son amigos"}';
            } else {
                throw new Exception("No son amigos");
                
            }
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }


    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/checkGroup', function (Request $request, Response $response, array $args) {
    $id_group = $request->getParam('id_group');
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

        $sql1 = "SELECT id_group from users_groups where id_group = '{$id_group}' and id_user = '{$data->id_user}'";

        $stmt = $cnn->query($sql1);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
        }else{
            if($stmt->num_rows === 1) {
                $resp = '{"text": "Pertenece al grupo"}';
            } else {
                throw new Exception("No pertenece al grupo");
                
            }
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }


    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->get('/loadAllGroupsAdmin', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try{
        if($data->rol === 'admin'){
            $cnn = $cnn->connect();
          
            if(!$cnn){
                throw new Exception("Error al conectar con la base de datos.", 1);
            }
    
            $sql1 = "SELECT groups.id_group, groups.id_user_owner, groups.route_image, groups.name as group_name, users.name, users.last_name_1, users.last_name_2 FROM `groups` 
                inner join users on groups.id_user_owner = users.id_user";
    
            $stmt = $cnn->query($sql1);
            $cnn->close();
    
            if(!$stmt) {
                throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            }else{
                while ($row = $stmt->fetch_assoc())
                    $ret[]= $row;
    
                $resp = json_encode($ret);
            }
    
        } else {
            throw new Exception("Usuario sin autorizar.");            
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->get('/loadAllUsersAdmin', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try{
        if($data->rol === 'admin'){
            $cnn = $cnn->connect();
          
            if(!$cnn){
                throw new Exception("Error al conectar con la base de datos.", 1);
            }
    
            $sql1 = "SELECT users.name, users.last_name_1, users.last_name_2, id_user, email, route_image, rol FROM `users`";
    
            $stmt = $cnn->query($sql1);
            $cnn->close();
    
            if(!$stmt) {
                throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            }else{
                while ($row = $stmt->fetch_assoc())
                    $ret[]= $row;
    
                $resp = json_encode($ret);
            }
    
        } else {
            throw new Exception("Usuario sin autorizar.");            
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/deleteUser', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_user = $request->getParam('id_user');

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try{
        if($data->rol === 'admin'){
            $cnn = $cnn->connect();
          
            if(!$cnn){
                throw new Exception("Error al conectar con la base de datos.", 1);
            }
    
            $sql1 = "DELETE from users where id_user = '{$id_user}'";
    
            $stmt = $cnn->query($sql1);
            $cnn->close();
    
            if(!$stmt) {
                throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            }else{
                $resp = '{"text": "Usuario eliminado"}';
            }
    
        } else {
            throw new Exception("Usuario sin autorizar.");            
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/adminUser', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_user = $request->getParam('id_user');

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try{
        if($data->rol === 'admin'){
            $cnn = $cnn->connect();
          
            if(!$cnn){
                throw new Exception("Error al conectar con la base de datos.", 1);
            }
    
            $sql1 = "UPDATE `users` SET `rol`= 'admin' WHERE id_user = '{$id_user}'";
    
            $stmt = $cnn->query($sql1);
            $cnn->close();
    
            if(!$stmt) {
                throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            }else{
                $resp = '{"text": "Usuario eliminado"}';
            }
    
        } else {
            throw new Exception("Usuario sin autorizar.");            
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/changePassAdmin', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_user = $request->getParam('id_user');
    $pass1 = $request->getParam('pass1');
    $pass2 = $request->getParam('pass2');

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try{
        if($data->rol === 'admin'){
            $cnn = $cnn->connect();
          
            if(!$cnn){
                throw new Exception("Error al conectar con la base de datos.", 1);
            }
            
            if($pass1 === $pass2) {
                $sql1 = "UPDATE `users` SET `password`= '{$pass1}' WHERE id_user = '{$id_user}'";
        
                $stmt = $cnn->query($sql1);
                $cnn->close();
        
                if(!$stmt) {
                    throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
                }else{
                    $resp = '{"text": "Contraseña cambiada"}';
                }

            } else {
                throw new Exception("Las contraseñas no coindicen");
            }
    
    
        } else {
            throw new Exception("Usuario sin autorizar.");            
        }

    }catch (Exception $e) {      
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});