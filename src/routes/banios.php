<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//registrar baños

$app->post('/banios', function (Request $request, Response $response, array $args) {
    $cnn = new DB();
    try {
        $cnn = $cnn->connect();

        if (!$cnn) {
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $ret = null;
        $resp = '';
        $err = [];
        $regexnombre = "/(^[a-záéíóúñ]+)([a-z áéíóúñ]+)?$/i";
        $regexLastnombres = "/(^[a-záéíóúñ]+)$/i";


        if (!filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)) {
            $err['email'] = "El email no es válido.";
        }

        if ($request->getParam('contraseña') !== $request->getParam('contraseña2')) {
            $err['contraseña'] = "Las contraseñas no coinciden";
        }

        if (preg_match($regexnombre, $request->getParam('nombre')) !== 1) {
            $err['nombre'] = "Nombre en formato inválido.";
        }

        if (preg_match($regexLastnombres, $request->getParam('apellido1')) !== 1) {
            $err['apellido1'] = "Primer apellido inválido.";
        }

        if (preg_match($regexLastnombres, $request->getParam('apellido2')) !== 1) {
            $err['apellido2'] = "Segundo apellido inválido.";
        }

        if (count($err) === 0) {

            if (isset($_FILES['image']['nombre'])) {
                $path = $_FILES['image']['nombre'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $restado = '';
                try {

                    if ($ext != 'jpg' && $ext != 'png' && $ext != 'PNG' && $ext != 'JPG') {
                        throw new Exception("Formato de imagen no válido", 1);
                    }
                    $location = 'upload/';

                    $now = new DateTime();
                    $now = $now->getTimeStamp();

                    $filenombre = $now . $path;
                    $totalPath = $location . $filenombre;

                    move_uploaded_file($_FILES['image']['tmp_nombre'], $totalPath);


                    $arr = array("imagen" => $filenombre);
                    $restado = json_encode($arr);

                    $sql = "INSERT INTO `baños` (`nombre`, `pais`, `provincia`, `cp`, `ciudad`, `calle`, `descripcion`, `imagen`) 
                    VALUES ('{$request->getParam("nombre")}', '{$request->getParam("apellido1")}', '{$request->getParam("apellido2")}', 
                    '{$request->getParam("email")}', '{$request->getParam("contraseña")}', 'wisher', '{$totalPath}')";

                    $stmt = $cnn->query($sql);
                    $cnn->close();

                    if (!$stmt) {
                        throw new Exception("Ha habido un error, intentelo más tarde.", 5);
                    } else {
                        $json = '{"text": "Usuario registrado correctamente."}';
                    }
                } catch (Exception $e) {
                    $response = $response->withStatus(400);
                    $resp = '{"error": "' . $e->getMessage() . '"}';
                }
            } else {
                $sql = "INSERT INTO `usuarios` (`nombre`, `apellido1`, `apellido2`, `email`, `contraseña`, `img`, `tipo`, `activo` ) 
        VALUES ('{$request->getParam("nombre")}', '{$request->getParam("apellido1")}', '{$request->getParam("apellido2")}', 
        '{$request->getParam("email")}', '{$request->getParam("contraseña")}', 'public/upload/user_anon.png', 0,0)";

                $stmt = $cnn->query($sql);
                $cnn->close();

                if (!$stmt) {
                    throw new Exception("Ha habido un error, intentelo más tarde.", 5);
                } else {
                    $json = '{"text": "Usuario registrado correctamente."}';
                }
            }
        } else {
            $json = json_encode($err);
        }
        $resp = $json;
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});
