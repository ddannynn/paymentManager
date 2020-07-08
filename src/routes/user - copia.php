<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;

//Sign up
$app->post('/v1/user/signup', function(Request $request, Response $response) {
//    $usu_user = $request->getParam('usu_user');
//    $usu_pass = $request->getParam('usu_pass');
    $data = json_decode($request->getBody());

    try {
        $cnn = new Connection();
        $cn = $cnn->open();
        $sql = "INSERT INTO usuario (usu_user, usu_pass, usu_est, usu_fec_reg) "
                . "VALUES (:usu_user, :usu_pass, 1, NOW())";
        $stmt = $cn->prepare($sql);
        $stmt->bindParam(':usu_user', $data->usu_user, PDO::PARAM_STR);
        $usu_pass = hash('sha256', $data->usu_pass);
        $stmt->bindParam(':usu_pass', $usu_pass, PDO::PARAM_STR);
        $stmt->execute();
//        echo json_encode("Usuario registrado");

        $sql2 = "SELECT usu_id, usu_user FROM usuario WHERE usu_user = :usu_user AND usu_pass = :usu_pass";
        $stmt2 = $cn->prepare($sql2);
        $stmt2->bindParam(':usu_user', $data->usu_user, PDO::PARAM_STR);
        $stmt2->bindParam(':usu_pass', $usu_pass, PDO::PARAM_STR);
        $stmt2->execute();
        $userData = $stmt2->fetch(PDO::FETCH_OBJ);
        if (!empty($userData)) {
            $usu_id = $userData->usu_id;
            $userData->token = $cnn->apiKey($usu_id);
            $userData->status = "success";
//            echo '{"userData": ' . json_encode($userData) . '}';
        } else {
            $userData->status = "fail";
        }
        echo '{"userData": ' . json_encode($userData) . '}';
        $cnn->close();
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Login
$app->post('/v1/user/login', function(Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $cnn = new Connection();
        $cn = $cnn->open();
//        $userData = '';
        $sql = "SELECT usu_id, usu_user FROM usuario WHERE usu_user = :usu_user AND usu_pass = :usu_pass";
        $stmt = $cn->prepare($sql);
        $stmt->bindParam(':usu_user', $data->usu_user, PDO::PARAM_STR);
        $usu_pass = hash('sha256', $data->usu_pass);
        $stmt->bindParam(':usu_pass', $usu_pass, PDO::PARAM_STR);
        $stmt->execute();
        $mainCount = $stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($userData)) {
            $usu_id = $userData->usu_id;
            $userData->token = $cnn->apiKey($usu_id);
            $userData->status = "success";
//            echo '{"userData": ' . json_encode($userData) . '}';
        } else {
            $userData->status = "fail";
            //echo json_encode('No existen coincidencias.');
        }
        echo '{"userData": ' . json_encode($userData) . '}';
        $cnn->close();
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
    // $sql = "SELECT * FROM usuario";
    // try {
    //     $cn = new Connection();
    //     $cn = $cn->open();
    //     $result = $cn->query($sql);
    //     if ($result->rowCount() > 0){
    //         $user = $result->fetchAll(PDO::FETCH_OBJ);
    //         echo json_encode($user);
    //     }else {
    //         echo json_encode("Sin resultado.");
    //     }
    // } catch (PDOException $e) {
    //     echo '{"error" : {"text":' . $e->getMessage() . '}';
    // }
});

