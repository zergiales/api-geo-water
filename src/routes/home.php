<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//ver el nombre de usuario y los datos de los baños
$app->get('/home/{nombre}', function (Request $request, Response $response, array $args) {
    $ret = null;
    $cnn = new DB();
    $name = $args['nombre'];
    $response->getBody()->write("$name");
    return $response;

    try {
        $cnn = $cnn->connect();

        if (!$cnn) {
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $user = $request->getParam('email');
        $pw = $request->getParam('contraseña');
        $sql = "SELECT * FROM `usuarios` WHERE email ='{$request->getParam("email")}' and contraseña='{$request->getParam("contraseña")}'";
        $stmt = $cnn->query($sql);
        $cnn->close();

        if ($stmt->num_rows != 1) {
            throw new Exception("Usuario o contraseña incorrectos.", 5);
        } else {
            while ($row = $stmt->fetch_assoc())
                $ret[] = $row;

            $payload = [
                "id" => $ret[0]["id"],
                "tipo" =>  $ret[0]["tipo"]
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
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

