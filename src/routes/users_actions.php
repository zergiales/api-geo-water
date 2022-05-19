<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//comprobar los usuarios que X usuario tiene agregado como amigo
$app->get('/loadContacts', function (Request $request, Response $response, array $args) { 
    $cnn = new DB();
    $auth = apache_request_headers();
    $token = $auth['Authorization'];
    $partsToken =  explode('.', $token);

    $data = json_decode(base64_decode($partsToken[1], true));


    try{
        $cnn = $cnn->connect();
      
        if(!$cnn){
            throw new Exception("Error al conectar con la base de datos.", 1);
        }

        $sql ="SELECT name, last_name_1, last_name_2, email, id_user, route_image
        FROM `added_users` inner join users on id_user_2 = id_user 
        where id_user_1 = '{$data->id_user}'";

        $stmt = $cnn->query($sql);
        $cnn->close();

        if(!$stmt) {
            throw new Exception("Ha ocurrido un error en la consulta, intentelo más tarde.");            
        }

        $ret=[];

        while ($row = $stmt->fetch_assoc())
            $ret[]= $row; 
            
        $json = json_encode($ret);


    }catch (Exception $e) {
        $response = $response->withStatus(400);        
        $json = '{"error": "'.$e-> getMessage().'"}';
    }

    $resp = $json;

    $response->getBody()->write($resp);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
 });