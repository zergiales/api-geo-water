<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/loadInfoGroup', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_group = $request->getParam("id_group");
    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT description, route_image, name FROM `groups` 
            where id_group = '{$id_group}'";

        $stmt = $cnn->query($sql);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            
        }
        $ret=[];

        while ($row = $stmt->fetch_assoc())
            $ret[]= $row; 
            
        $json = json_encode($ret);

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $resp = $json;

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->get('/loadGroups', function (Request $request, Response $response, array $args) {
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

        $sql = "SELECT name, groups.id_group, description, route_image FROM `users_groups` 
            INNER join groups on users_groups.id_group = groups.id_group where id_user = '{$data->id_user}'";

        $stmt = $cnn->query($sql);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
            
        }
        $ret=[];

        while ($row = $stmt->fetch_assoc())
            $ret[]= $row; 
            
        $json = json_encode($ret);

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $resp = $json;

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});


$app->post('/loadOwnGroup', function (Request $request, Response $response, array $args) {
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

        $sql = "SELECT name, description, route_image, id_user_owner, id_group FROM `groups` 
            where id_user_owner = '{$data->id_user}' and id_group = '{$request->getParam("id_group")}'";

            $stmt = $cnn->query($sql);
            $cnn->close();

            if(!$stmt) {
                throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");
                
            }
            $ret=[];

            while ($row = $stmt->fetch_assoc())
                $ret[]= $row; 
                
            $json = json_encode($ret);

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $json = '{"error": "'.$e-> getMessage().'"}';
    }

    $resp = $json;

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->get('/loadOwnGroups', function (Request $request, Response $response, array $args) {
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

        $sql = "SELECT id_group, description,route_image, name from groups where id_user_owner = {$data->id_user}";

        $stmt = $cnn->query($sql);
        $cnn-> close();

        if(!$stmt){
            throw new Exception("Ha habido un error, intentelo más tarde.", 5);
        } else{
            $ret = [];
    
            while ($row = $stmt->fetch_assoc())
                $ret[]= $row; 
                
            $json = json_encode($ret);
            
        }

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $resp = $json;

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;

});

$app->post('/createGroup', function (Request $request, Response $response, array $args) {
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

        //se registra el grupo description, name, route_image, id_user_owner

        if (isset($_FILES['image']['name'])) {
            $path = $_FILES['image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $restado='';
              try {
            
                if($ext != 'jpg' && $ext !='png' && $ext != 'PNG' && $ext != 'JPG'){
                  throw new Exception("Formato de imagen no válido", 1);
                }
                $location = 'upload/';
            
                $now = new DateTime();
                $now = $now->getTimeStamp();
            
                $filename = $now . $path;
                $totalPath = $location.$filename;
    
                move_uploaded_file($_FILES['image']['tmp_name'],$totalPath);
    
            
                $arr = array("imagen"=>$filename);
                $restado = json_encode($arr);
    
                $sqlGroup = "INSERT INTO `groups` (`name`, `description`, `route_image`, `id_user_owner`) 
                    VALUES ('{$request->getParam("group_name")}', '{$request->getParam("group_description")}', '{$totalPath}', 
                   '{$data->id_user}')";

              
    
                $stmt = $cnn->query($sqlGroup);
                $cnn-> close();
          
                if(!$stmt){
                  throw new Exception("Ha habido un error, intentelo más tarde.", 5);
                } else{
                  $resp = '{"text": "Avatar cambiado correctamente."}';
                }
            
              } catch (Exception $e) {
                $response = $response->withStatus(400);
                $resp = '{"error": "'.$e-> getMessage().'"}';
              }
            
        } else {
            $sqlGroup = "INSERT INTO `groups` (`name`, `description`, `route_image`, `id_user_owner`) 
            VALUES ('{$request->getParam("group_name")}', '{$request->getParam("group_description")}', 'upload/group_anon.png', 
            '{$data->id_user}')";

            $stmt = $cnn->query($sqlGroup);
            $cnn-> close();
    
            if(!$stmt){
                throw new Exception("Ha habido un error, intentelo más tarde.", 5);
            } else{
                $resp = '{"text": "Grupo registrado correctamente."}';
            }
        }
    }
    catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;

});

$app->post('/deleteGroup', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_group = $request->getParam('id_group');
    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sqlItem = "DELETE FROM groups WHERE id_group = '{$id_group}'";
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