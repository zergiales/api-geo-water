<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//loguear en el sistema
$app->post('/login', function (Request $request, Response $response, array $args) {  
  $ret = null;
  $cnn = new DB();
  $resp = '';
  
  try{
    $cnn = $cnn->connect();
    
    if(!$cnn){
      throw new Exception("Error al conectar con la base de datos.", 1);
    }
    
    $user = $request->getParam('email');
    $pw= $request->getParam('contraseña'); 
    $sql= "SELECT * FROM `usuarios` WHERE email = 'aAAABBBB@a.es'"; 
    $stmt = $cnn->query($sql);
    $cnn-> close();
    
    if($stmt->num_rows!=1){
      throw new Exception("Usuario o contraseña incorrectos.", 5);
    } else{
      while ($row = $stmt->fetch_assoc())
        $ret[]= $row;

      $payload = [
        "id_user" => $ret[0]["id_user"],
        "rol" =>  $ret[0]["rol"]
      ];
      
      $token = JWT::createToken($payload, TOKEN_KEY);
    }  
    $arr = array(
      "email" => $ret[0]["email"],
      "nombre" => $ret[0]["nombre"],
      "apellido1" => $ret[0]["apellido1"],
      "apellido2" => $ret[0]["apellido2"],
      "img" => $ret[0]["img"],
      "tipo" => $ret[0]["tipo"],
      "token" => $token
    );  
    $resp = json_encode($arr);
    
    
  }catch (Exception $e){             
    $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
  }
  
  $response->getBody()->write($resp);
  $response->withHeader('Content-Type', 'application/json');
  return $response;  
});


//registrarse en la base de datos:
$app->post('/register', function (Request $request, Response $response, array $args) {
  $cnn = new DB();
  try{
    $cnn = $cnn->connect();
    
    if(!$cnn){
      throw new Exception("Error al conectar con la base de datos.", 1);
    }
    
    $ret = null;
    $resp = '';
    $err = [];
    $regexName = "/(^[a-záéíóúñ]+)([a-z áéíóúñ]+)?$/i";
    $regexLastNames = "/(^[a-záéíóúñ]+)$/i";
    

    if (!filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)){
      $err['email'] = "El email no es válido.";
    }
    
    if ($request->getParam('contraseña') !== $request->getParam('contraseña2')){
      $err['contraseña'] = "Las contraseñas no coinciden";
    }
    
    if (preg_match($regexName, $request->getParam('nombre'))!== 1) {
      $err['nombre'] = "Nombre en formato inválido.";
    }
    
    if (preg_match($regexLastNames, $request->getParam('apellido1')) !== 1){
      $err['apellido1'] = "Primer apellido inválido.";
    }
    
    if (preg_match($regexLastNames, $request->getParam('apellido2')) !== 1){
      $err['apellido2'] = "Segundo apellido inválido.";
    }

    if (count($err) === 0) {

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

            $sql = "INSERT INTO `users` (`name`, `last_name_1`, `last_name_2`, `email`, `password`, `rol`, `route_image`) 
              VALUES ('{$request->getParam("name")}', '{$request->getParam("last_name_1")}', '{$request->getParam("last_name_2")}', 
              '{$request->getParam("email")}', '{$request->getParam("password1")}', 'wisher', '{$totalPath}')";

            $stmt = $cnn->query($sql);
            $cnn-> close();
      
            if(!$stmt){
              throw new Exception("Ha habido un error, intentelo más tarde.", 5);
            } else{
              $json = '{"text": "Usuario registrado correctamente."}';
            }
        
          } catch (Exception $e) {
            $response = $response->withStatus(400);
            $resp = '{"error": "'.$e-> getMessage().'"}';
          }


      } else {
        $sql = "INSERT INTO `usuarios` (`nombre`, `apellido1`, `apellido2`, `email`, `contraseña`, `img`, `tipo` ) 
        VALUES ('{$request->getParam("nombre")}', '{$request->getParam("apellido1")}', '{$request->getParam("apellido2")}', 
        '{$request->getParam("email")}', '{$request->getParam("contraseña")}', 'public/upload/user_anon.png', 0)";

        $stmt = $cnn->query($sql);
        $cnn-> close();
  
        if(!$stmt){
          throw new Exception("Ha habiwdo un error, intentelo más tarde.", 5);
        } else{
          $json = '{"text": "Usuario registrado correctamente."}';
        }
        
      }
    } else {
      $json = json_encode($err);
    }    
    $resp = $json;

 
  } catch (Exception $e){             
    $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
  }
  $response->getBody()->write($resp);
  $response->withHeader('Content-Type', 'application/json');
  return $response;  
});


$app->get('/signoff', function (Request $request, Response $response) {
  $path = $request->getUri()->getPath();
  $response->withStatus(200);
  $response->getBody()->write($path);
  $response->withHeader('Content-Type', 'application/json');
  return $response; 
});

$app->get('/renewcredentials', function (Request $request, Response $response) {
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

    $sql= "SELECT email, name, last_name_1, last_name_2, rol, route_image, id_user FROM users WHERE id_user = '{$data->id_user}'"; 
    $stmt = $cnn->query($sql);
    $cnn-> close();

    while ($row = $stmt->fetch_assoc())
        $ret[]= $row;

    $arr = array(
      "email" => $ret[0]["email"],
      "name" => $ret[0]["name"],
      "last_name_1" => $ret[0]["last_name_1"],
      "last_name_2" => $ret[0]["last_name_2"],
      "rol" => $ret[0]["rol"],
      "route_image" => $ret[0]["route_image"],
      "id_user" => $ret[0]["id_user"]
    );
    $resp = json_encode($arr);

  }catch (Exception $e){             
    $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
  }

  $response->getBody()->write($resp);
  $response->withHeader('Content-Type', 'application/json');
  return $response;  
});

$app->post('/changePassword', function (Request $request, Response $response, array $args) {
  $cnn = new DB();
  
  $password = $request->getParam("password");

  $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

  try{
    $cnn = $cnn->connect();
    
    if(!$cnn){
      throw new Exception("Error al conectar con la base de datos.", 1);
    }

    $sql = "UPDATE users
      SET password = '{$password}' WHERE id_user = '{$data->id_user}'";

    $stmt = $cnn->query($sql);
    $cnn-> close();

    if(!$stmt){
      throw new Exception("Ha habiwdo un error, intentelo más tarde.", 5);
    } else{
      $json = '{"text": "Contraseña cambiada correctamente"}';
    }

    $resp = $json;

  } catch (Exception $e){             
    $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
  }
  $response->getBody()->write($resp);
  $response->withHeader('Content-Type', 'application/json');
  return $response;  
});

$app->post('/changeAvatar', function (Request $request, Response $response, array $args) {
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
    
    $ret = null;
    $resp = '';    

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

            $sql= "UPDATE `users` SET `route_image` = '{$totalPath}' WHERE `users`.`id_user` = '{$data->id_user}';";
          

            $stmt = $cnn->query($sql);
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
        }
  } catch (Exception $e){             
    $resp = '{"error":{"text":"'.$e->getMessage().'"}}';
  }
  $response->getBody()->write($resp);
  $response->withHeader('Content-Type', 'application/json');
  return $response;  
});