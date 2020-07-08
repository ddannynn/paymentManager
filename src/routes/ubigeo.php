<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Departamentos
$app->get('/v1/ubigeo/departamento', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT SUBSTRING(ubi_cod, 1, 2) AS ubi_dep_cod, ubi_dep '
                . 'FROM ubigeo '
                . 'WHERE SUBSTRING(ubi_cod, 3, 4) = \'0000\' '
                . 'ORDER BY ubi_id ';
            $stmt = $cn->prepare($sql);
            $stmt->execute();
            $ubigeoData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"ubigeoData": ' . json_encode($ubigeoData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Provincias
$app->get('/v1/ubigeo/provincia', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_ubi_dep_cod = $request->getParam('ubi_dep_cod');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT SUBSTRING(ubi_cod, 3, 2) AS ubi_pro_cod, ubi_pro '
                . 'FROM ubigeo '
                . 'WHERE SUBSTRING(ubi_cod, 1, 2) = :ubi_dep_cod AND SUBSTRING(ubi_cod, 3, 2) <> \'00\' AND SUBSTRING(ubi_cod, 5, 2) = \'00\' '
                . 'ORDER BY ubi_id ';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':ubi_dep_cod', $param_ubi_dep_cod, PDO::PARAM_STR);
            $stmt->execute();
            $ubigeoData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"ubigeoData": ' . json_encode($ubigeoData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Distritos
$app->get('/v1/ubigeo/distrito', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_ubi_dep_cod = $request->getParam('ubi_dep_cod');
    $param_ubi_pro_cod = $request->getParam('ubi_pro_cod');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT SUBSTRING(ubi_cod, 5, 2) AS ubi_dis_cod, ubi_dis '
                . 'FROM ubigeo '
                . 'WHERE SUBSTRING(ubi_cod, 1, 2) = :ubi_dep_cod AND SUBSTRING(ubi_cod, 3, 2) = :ubi_pro_cod AND SUBSTRING(ubi_cod, 5, 2) <> \'00\' '
                . 'ORDER BY ubi_id ';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':ubi_dep_cod', $param_ubi_dep_cod, PDO::PARAM_STR);
            $stmt->bindParam(':ubi_pro_cod', $param_ubi_pro_cod, PDO::PARAM_STR);
            $stmt->execute();
            $ubigeoData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"ubigeoData": ' . json_encode($ubigeoData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});
