<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//registrarse en la base de datos:
$app->post('/resenias', function (Request $request, Response $response, array $args) {
  $cnn = new DB();
  try{
    $cnn = $cnn->connect();
    
    if(!$cnn){
      throw new Exception("Error al conectar con la base de datos.", 1);
    }
    
    $ret = null;
    $resp = '';
    $err = [];

    /*metemos los parametros una vez pasados los filtros en variables */
    $id_baño = $request->getParam('id_baño');
    $id_usuario = $request->getParam('id_usuario');
    $titulo = $request->getParam('titulo');
    $fecha = $request->getParam('fecha');
    $descripcion = $request->getParam('descripcion');

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

            $sql = "INSERT INTO `reseñas` (`id_baño`, `id_usuario`, `titulo`, `fecha`, `descripcion`) 
              VALUES ('{$id_baño}', '{$id_usuario}', '{$titulo}', 
              '{$fecha}', '{$descripcion}')";

            $stmt = $cnn->query($sql);
            $cnn-> close();
      
            if(!$stmt){
              throw new Exception("Ha habido un error, intentelo más tarde.", 5);
            } else{
              $json = '{"text": "reseña registrada correctamente."}';
            }
        
          } catch (Exception $e) {
            $response = $response->withStatus(400);
            $resp = '{"error": "'.$e-> getMessage().'"}';
          }


      } else {
        $sql = "INSERT INTO `reseñas` (`id_baño`, `id_usuario`, `titulo`, `fecha`, `descripcion`) 
            VALUES ('{$id_baño}', '{$id_usuario}', '{$titulo}', 
            '{$fecha}', '{$descripcion}')";

        $stmt = $cnn->query($sql);
        $cnn-> close();
  
        if(!$stmt){
          throw new Exception("Ha habido un error, intentelo más tarde.", 5);
        } else{
          $json = '{"text": "reseña registrada correctamente."}';
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
