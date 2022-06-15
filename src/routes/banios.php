<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../src/config/config.php';
require_once '../src/config/db.php';
require_once '../src/config/tokenGenerator.php';

//ver los baños que tenemos como usuario
$app->post('/banios', function (Request $request, Response $response, array $args) {
    $ret = null;
    $cnn = new DB();
    $resp = '';

    try {
        $cnn = $cnn->connect();

        if (!$cnn) {
            throw new Exception("Error al conectar con la base de datos.", 1);
        }
        /* variables de parametros */
        $nombre = $request->getParam('nombre');
        $pais = $request->getParam('pais');
        $provincia = $request->getParam('provincia');
        $cp = $request->getParam('cp');
        $ciudad = $request->getParam('ciudad');
        $calle =$request->getParam('calle');

        $sql = "SELECT * FROM `usuarios` WHERE nombre ='{$request->getParam("nombre")}'";
        $stmt = $cnn->query($sql);
        $cnn->close();

        if ($stmt->num_rows != 1) {
            throw new Exception("baños incorrectos", 5);
        } else {
            while ($row = $stmt->fetch_assoc())
                $ret[] = $row;

            $payload = [
                "id" => $ret[0]["id"],
                "tipo" =>  $ret[0]["tipo"]
            ];

            $token = JWT::createToken($payload, TOKEN_KEY);
        }
        /* array donde guardamos los datos del baño */
        $arr = array(
            "nombre" => $ret[0]["nombre"],
            "pais" => $ret[0]["pais"],
            "provincia" => $ret[0]["provincia"],
            "cp" => $ret[0]["cp"],
            "ciudad" => $ret[0]["ciudad"],
            "calle" => $ret[0]["calle"],
            "token" => $token
        );
        $resp = json_encode($arr);
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});


//registrarse en la base de datos:
$app->post('/register', function (Request $request, Response $response, array $args) {
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

                    $sql = "INSERT INTO `usuarios` (`nombre`, `apellido1`, `apellido2`, `email`, `contraseña`, `img`, `tipo`) 
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

//para cerrar sesion
$app->get('/cerrarSesion', function (Request $request, Response $response) {
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

    try {
        $cnn = $cnn->connect();

        if (!$cnn) {
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "SELECT email, nombre, apellido1, apellido2, tipo, img, id FROM users WHERE id = '{$data->id}'";
        $stmt = $cnn->query($sql);
        $cnn->close();

        while ($row = $stmt->fetch_assoc())
            $ret[] = $row;

        $arr = array(
            "email" => $ret[0]["email"],
            "nombre" => $ret[0]["nombre"],
            "apellido1" => $ret[0]["apellido1"],
            "apellido2" => $ret[0]["apellido2"],
            "tipo" => $ret[0]["tipo"],
            "img" => $ret[0]["img"],
            "id" => $ret[0]["id"]
        );
        $resp = json_encode($arr);
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/changecontraseña', function (Request $request, Response $response, array $args) {
    $cnn = new DB();

    $contraseña = $request->getParam("contraseña");

    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));

    try {
        $cnn = $cnn->connect();

        if (!$cnn) {
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql = "UPDATE users
      SET contraseña = '{$contraseña}' WHERE id = '{$data->id}'";

        $stmt = $cnn->query($sql);
        $cnn->close();

        if (!$stmt) {
            throw new Exception("Ha habido un error, intentelo más tarde.", 5);
        } else {
            $json = '{"text": "Contraseña cambiada correctamente"}';
        }

        $resp = $json;
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
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
    try {
        $cnn = $cnn->connect();

        if (!$cnn) {
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $ret = null;
        $resp = '';

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

                $sql = "UPDATE `users` SET `img` = '{$totalPath}' WHERE `users`.`id` = '{$data->id}';";


                $stmt = $cnn->query($sql);
                $cnn->close();

                if (!$stmt) {
                    throw new Exception("Ha habido un error, intentelo más tarde.", 5);
                } else {
                    $resp = '{"text": "Avatar cambiado correctamente."}';
                }
            } catch (Exception $e) {
                $response = $response->withStatus(400);
                $resp = '{"error": "' . $e->getMessage() . '"}';
            }
        }
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});
