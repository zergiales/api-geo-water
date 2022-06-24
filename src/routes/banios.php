<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//registrarse en la base de datos:
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

        /*metemos los parametros una vez pasados los filtros en variables */
        $id_usuario = $request->getParam('id_usuario');
        $nombre = $request->getParam('nombre');
        $pais = $request->getParam('pais');
        $provincia = $request->getParam('provincia');
        $cp = $request->getParam('cp');
        $ciudad = $request->getParam('ciudad');
        $calle = $request->getParam('calle');
        $descripcion = $request->getParam('descripcion');
        $img = $request->getParam('img');
        $latitud = $request->getParam('latitud');
        $coorX = $request->getParam('coorX');
        $coorY = $request->getParam('coorY');

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

                    $sql = "INSERT INTO `baños` (`id_usuario`, `nombre`, `pais`, `provincia`, `cp`, `ciudad`, `calle`, `descripcion`,
                    `img`, `latitud`, `coorX`, `coorY`) VALUES ('{$id_usuario}', '{$nombre}', '{$pais}',
                    '{$provincia}',, '{$cp}', '{$ciudad}', '{$calle}',
                    '{$descripcion}', '{$img}', '{$latitud}',
                    '{$coorX}', '{$coorY}')";

                    $stmt = $cnn->query($sql);
                    $cnn->close();

                    if (!$stmt) {
                        throw new Exception("Ha habido un error, intentelo más tarde.", 5);
                    } else {
                        $json = '{"text": "baño registrado correctamente."}';
                    }
                } catch (Exception $e) {
                    $response = $response->withStatus(400);
                    $resp = '{"error": "' . $e->getMessage() . '"}';
                }
            } else {
                $sql = "INSERT INTO `baños` (`id_usuario`, `nombre`, `pais`, `provincia`, `cp`, `ciudad`, `calle`, `descripcion`, `img`, `latitud`, `coorX`, `coorY`) 
              VALUES ('{$id_usuario}', '{$nombre}', '{$pais}', '{$provincia}', '{$cp}', '{$ciudad}', '{$calle}', '{$descripcion}', '{$img}', '{$latitud}', '{$coorX}', '{$coorY}')";


                $stmt = $cnn->query($sql);
                $cnn->close();

                if (!$stmt) {
                    throw new Exception("Ha habido un error, intentelo más tarde.", 5);
                } else {
                    $json = '{"text": "baño registrado correctamente."}';
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
