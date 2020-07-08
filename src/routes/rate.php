<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Rates
$app->get('/v1/rate/all', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT tas_id, tas_des, tas_val, tas_pla FROM tasa ORDER BY tas_id';
            $stmt = $cn->prepare($sql);
            $stmt->execute();
            $rateData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"rateData": ' . json_encode($rateData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});
