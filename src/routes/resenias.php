<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//registrar resenias
$app->post('/resenias', function (Request $request, Response $response, array $args) {
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
        
            $sql = "INSERT INTO `reseñas` (`id_baño`, `id_usuario`, `titulo`, `fecha`, `descripcion`) 
                    VALUES ('{$request->getParam("id_baño")}', '{$request->getParam("id_usuario")}', '{$request->getParam("titulo")}', 
                    '{$request->getParam("fecha")}', '{$request->getParam("descripcion")}', 'wisher', '{$totalPath}')";

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
