<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/sendFriendship', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_user_notif = $request->getParam("id_user_notif");

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "INSERT INTO `notifications`(`recibed`, `user_notif`, `adding_user`, `id_group`, `kind`) 
            VALUES ('no','{$id_user_notif}','{$data->id_user}',NULL,'friendship')";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
        }


        $resp = '{"text": "Notificación mandada."}';

    } catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/sendGroupNotif', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    $id_group = $request->getParam('id_group');
    $id_user_notif = $request->getParam("id_user_member");

    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "INSERT INTO `notifications`(`recibed`, `user_notif`, `adding_user`, `id_group`, `kind`) 
        VALUES ('no','{$id_user_notif}',NULL,'{$id_group}','group')";

        $stmt1 = $cnn->query($sql);
        $cnn-> close(); 

        if(!$stmt1) {
            throw new Exception("Ha habido un error, intentelo más tarde.");
        }


        $resp = '{"text": "Notificación mandada."}';

    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $resp = '{"error": "'.$e-> getMessage().'"}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->get('/loadNotifUsers', function (Request $request, Response $response) {
    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);
  
    $data = json_decode(base64_decode($partsToken[1], true));
  
    $cnn = new DB();
    $resp = '';
    
    try{
      $cnn = $cnn->connect();
      
      if(!$cnn){
        throw new Exception("Error al conectar con la base de datos.", 1);
      }
  
      $sql = "SELECT id_notif, adding_user, kind, name, last_name_1, last_name_2, recibed, id_user FROM `notifications` 
        inner join users on users.id_user = notifications.adding_user 
        WHERE user_notif = '{$data->id_user}' and adding_user is not null and recibed = 'no'";

      $stmt = $cnn->query($sql);
      $cnn-> close();
  
      if($stmt){
        $ret = [];
        while ($row = $stmt->fetch_assoc())
            $ret[]= $row;
  
        $resp = json_encode($ret);
      } else {
        $resp = "";
      }
  
    }catch (Exception $e){             
      $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
    }
  
    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;  
  });

  $app->get('/loadNotifGroup', function (Request $request, Response $response) {
    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);
  
    $data = json_decode(base64_decode($partsToken[1], true));
  
    $cnn = new DB();
    $resp = '';
    
    try{
      $cnn = $cnn->connect();
      
      if(!$cnn){
        throw new Exception("Error al conectar con la base de datos.", 1);
      }
  
      $sql= "SELECT id_notif, recibed, notifications.id_group, kind, name FROM `notifications` 
        inner join `groups` on groups.id_group = notifications.id_group 
        WHERE user_notif = '{$data->id_user}' and notifications.id_group is not null and recibed = 'no'"; 
      $stmt = $cnn->query($sql);
      $cnn-> close();
  
      if($stmt){
        $ret = [];
        while ($row = $stmt->fetch_assoc())
            $ret[]= $row;
  
        $resp = json_encode($ret);
      } else {
        $resp = "";
      }
  
    }catch (Exception $e){             
      $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
    }
  
    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;  
  });

  $app->post('/readNotif', function (Request $request, Response $response) {
    $id_notif = $request->getParam('id_notif');
  
    $cnn = new DB();
    $resp = '';
    
    try{
      $cnn = $cnn->connect();
      
      if(!$cnn){
        throw new Exception("Error al conectar con la base de datos.", 1);
      }
  
      $sql= "UPDATE `notifications` SET `recibed` = 'yes' WHERE `notifications`.`id_notif` = '{$id_notif}'"; 
      $stmt = $cnn->query($sql);
      $cnn-> close();
  
      if(!$stmt){
          throw new Exception("Error en la conuslta");          
      }

      $resp = '{"text":"Notificación recibida"}';
  
    }catch (Exception $e){             
      $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
    }
  
    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;  
  });

  $app->post('/acceptFriendship', function (Request $request, Response $response) {
    $id_notif = $request->getParam('id_notif');
    $id_user_notif= $request->getParam('id_user_notif');

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);
  
    $data = json_decode(base64_decode($partsToken[1], true));
  
    $cnn = new DB();
    $resp = '';
    
    try{
      $cnn = $cnn->connect();
      
      if(!$cnn){
        throw new Exception("Error al conectar con la base de datos.", 1);
      }
  
      $sql = "UPDATE `notifications` SET `recibed` = 'yes' WHERE `notifications`.`id_notif` = '{$id_notif}'"; 
      $sql1 = "INSERT INTO `added_users` (`id_user_1`, `id_user_2`) VALUES ('{$data->id_user}', '{$id_user_notif}'), ('{$id_user_notif}', '{$data->id_user}');";

      $stmt = $cnn->query($sql);
      $stmt1 = $cnn->query($sql1);
      $cnn-> close();
  
      if(!$stmt || !$stmt1){
          throw new Exception("Error en la conuslta");          
      }

      $resp = '{"text":"Amistad aceptada"}';
  
    }catch (Exception $e){             
      $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
    }
  
    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;  
  });

  