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


//login, registro, cerrar sesión, renovar datos, cambiar contraseña, cambiar avatar
require "../src/routes/login.php";

//crear baño, cargar todos los baños, borrar baños, mostrar los baños de tu alrededor
//cargar los baños de un usuario
require "../src/routes/banios.php";

//crear grupo, borrar grupo, cargar la info de un grupo,
//cargar todos los grupos de un usuario, cargar la info de un grupo del que eres propietario
//cargar todos tus grupos
require "../src/routes/groups.php";

//cargar deseos de un grupo concreto, cargar miembros de un grupo concreto
//crear la vinculacion entre un grupo y un deseo
require "../src/routes/wish_groups.php";

//cargar los contactos de un usuario concreto
require "../src/routes/users_actions.php";

//eliminar contacto, ver detalles de un contacto
//cargar los deseos de un contacto en concreto
//cargar todos los contactos
//cargar los contactos q no pertenecen a un grupo
//cargar los contactos que pertenecen a un grupo
//añadir un usuario a un grupo
//borrar a un usuario de un grupo
require "../src/routes/contacts.php";

//mandar notificacion de amistad, aceptar amigo, leer notificacion
//mandar notificacion de que te han añadido a un grupo
//cargar las notificaciones de amistad
//cargar las notificaciones de grupo
require "../src/routes/notif.php";
//cargar el nombre del usuario
require"../src/routes/userName.php";

require "../src/routes/admision.php";

$app->run();