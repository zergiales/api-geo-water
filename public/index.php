<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../vendor/autoload.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config = ['settings' => [
  'addContentLengthHeader' => true,
  'displayErrorDetails' => true
]]; 

$app = new \Slim\App($config);

/**
 * New routes
 */
$app->get('/hey/{nombre}/{apellido}' , function (Request $request , Response $response, array $args) {
  $name=$args['nombre'];
  $surname=$args['apellido'];
  $response->getBody()->write("hello, $name $surname");
  return $response;
});


//login, registro, cerrar sesión, renovar datos, cambiar contraseña, cambiar la imagen
require "../src/routes/login.php";

//para mostrar informacion del usuario en home
require "../src/routes/home.php";

// insertar baños, cambiar baños y eliminar baños
require "../src/routes/banios.php";

//insertar reseñas, cambiar reseñas y eliminar reseñas
require "../src/routes/resenias.php";

$app->run();