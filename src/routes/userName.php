<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
// para cargar el nombre del usuario
$app->get('/home', function (Request $request, Response $response) {
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
        $user = $request->getParam('email');
        $pw = $request->getParam('contraseña');
        $sql = "SELECT * FROM `usuarios` WHERE email ='{$request->getParam("email")}' and contraseña='{$request->getParam("contraseña")}'"; 
        $stmt = $cnn->query($sql);
        $cnn->close();

        if ($stmt) {
            $ret = [];
            while ($row = $stmt->fetch_assoc())
                $ret[] = $row;

            $resp = json_encode($ret);
        } else {
            $resp = "";
        }
    } catch (Exception $e) {
        $resp = '{"error":{"text":"' . $e->getMessage() . '"}}';
    }

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});