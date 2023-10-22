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
    $contraseña = $request->getParam('contraseña');
    $sql= "SELECT * FROM `usuarios` WHERE email = '{$user}' and password = '{$contraseña}'"; 
    $stmt = $cnn->query($sql);
    $cnn-> close();
    
    if($stmt->num_rows!=1){
      throw new Exception("Usuario o contraseña incorrectos.", 5);
    } else{
      while ($row = $stmt->fetch_assoc())
        $ret[]= $row;

      $payload = [
        "id" => $ret[0]["id"],
        "tipo" =>  $ret[0]["tipo"]
      ];
      
      $token = JWT::createToken($payload, TOKEN_KEY);
    }  
    $arr = array(
      "id" =>$ret[0]["id"],
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
    $regexnombre = "/(^[a-záéíóúñ]+)([a-z áéíóúñ]+)?$/i";
    $regexLastnombres = "/(^[a-záéíóúñ]+)$/i";
    

    if (!filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)){
      $err['email'] = "El email no es válido.";
    }
    
    if ($request->getParam('contraseña') !== $request->getParam('contraseña2')){
      $err['contraseña'] = "Las contraseñas no coinciden";
    }
    
    if (preg_match($regexnombre, $request->getParam('nombre'))!== 1) {
      $err['nombre'] = "Nombre en formato inválido.";
    }
    
    if (preg_match($regexLastnombres, $request->getParam('apellido1')) !== 1){
      $err['apellido1'] = "Primer apellido inválido.";
    }
    
    if (preg_match($regexLastnombres, $request->getParam('apellido2')) !== 1){
      $err['apellido2'] = "Segundo apellido inválido.";
    }
    /*metemos los parametros una vez pasados los filtros en variables */
    $id = $request->getParam('id');
    $nombre = $request->getParam('nombre');
    $apellido1 = $request->getParam('apellido1');
    $apellido2 = $request->getParam('apellido2');
    $email = $request->getParam('email');
    $contraseña = $request->getParam('contraseña');
    /*hasheamos la contraseña p*/
    $contraseña_cifrada = password_hash($contraseña, PASSWORD_DEFAULT);
    // $contador_baños = "SELECT COUNT(*) FROM BAÑOS P1, USUARIOS P2 WHERE P1.ID_USUARIO = P2.ID AND P2.ID =$id ;";
    if (count($err) === 0) {

      if (isset($_FILES['image']['nombre'])) {
        $path = $_FILES['image']['nombre'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $restado='';
          try {
        
            if($ext != 'jpg' && $ext !='png' && $ext != 'PNG' && $ext != 'JPG'){
              throw new Exception("Formato de imagen no válido", 1);
            }
            $location = 'upload/';
        
            $now = new DateTime();
            $now = $now->getTimeStamp();
        
            $filenombre = $now . $path;
            $totalPath = $location.$filenombre;

            move_uploaded_file($_FILES['image']['tmp_nombre'],$totalPath);

        
            $arr = array("imagen"=>$filenombre);
            $restado = json_encode($arr);

            $sql = "INSERT INTO `usuarios` (`nombre`, `apellido1`, `apellido2`, `email`, `contraseña`, `img`, `tipo`) 
              VALUES ('{$nombre}', '{$apellido1}', '{$apellido2}', 
              '{$email}', '{$contraseña_cifrada}', '{$totalPath}',0,0)";

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
        $sql = "INSERT INTO `usuarios` (`nombre`, `apellido1`, `apellido2`, `email`, `contraseña`, `img`, `tipo`, `activo` ) 
        VALUES ('{$nombre}', '{$apellido1}', '{$apellido2}', 
        '{$email}', '{$contraseña}', 'public/upload/user_anon.png', 0,0)";

        $stmt = $cnn->query($sql);
        $cnn-> close();
  
        if(!$stmt){
          throw new Exception("Ha habido un error, intentelo más tarde.", 5);
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

<script>
  console.log($payload);
</script>