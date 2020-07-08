<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;

//Exist User
$app->get('/v1/user/exist', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_filter = $request->getParam('filter');
    try {
        $cnn = new Connection();
        $cn = $cnn->open();
        $sql = 'SELECT usu_id FROM usuario WHERE usu_est = 1 AND usu_user = :filter ';
        $stmt = $cn->prepare($sql);
        $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cnn->close();
        echo json_encode($row);
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Login
$app->post('/v1/user/login', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $userData = new stdClass;
        $cnn = new Connection();
        $cn = $cnn->open();
        //        $userData = '';
        $sql = 'SELECT usu.usu_id, usu.usu_user, emp.emp_nom, emp.emp_ape, et.et_id, et.et_des '
            . 'FROM usuario usu '
            . 'INNER JOIN empleado emp ON emp.emp_id = usu.emp_id '
            . 'INNER JOIN empleado_tipo et ON et.et_id = emp.et_id '
            . 'WHERE usu.usu_user = :usu_user AND usu.usu_pass = :usu_pass';
        $stmt = $cn->prepare($sql);
        $stmt->bindParam(':usu_user', $data->usu_user, PDO::PARAM_STR);
        $usu_pass = hash('sha256', $data->usu_pass);
        $stmt->bindParam(':usu_pass', $usu_pass, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($userData)) {
            $usu_id = $userData->usu_id;
            $userData->token = $cnn->apiKey($usu_id);
            $userData->status = "success";
        }
        $cnn->close();
        echo '{"userData": ' . json_encode($userData) . '}';
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Login 2
$app->post('/v1/user/login2', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $userData = new stdClass;
        $cnn = new Connection();
        $cn = $cnn->open();
        //        $userData = '';
        $sql = 'SELECT usu.usu_id, usu.usu_user, emp.emp_nom, emp.emp_ape, et.et_id, et.et_des '
            . 'FROM usuario usu '
            . 'INNER JOIN empleado emp ON emp.emp_id = usu.emp_id '
            . 'INNER JOIN empleado_tipo et ON et.et_id = emp.et_id '
            . 'WHERE usu.usu_user = :usu_user';
        $stmt = $cn->prepare($sql);
        $stmt->bindParam(':usu_user', $data->usu_user, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($userData)) {
            $usu_id = $userData->usu_id;
            $userData->token = $cnn->apiKey($usu_id);
            $userData->status = "success";
        }
        $cnn->close();
        echo '{"userData": ' . json_encode($userData) . '}';
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Users
$app->get('/v1/user/all', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_last_date = $request->getParam('last_date');
    $param_filter = $request->getParam('filter');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT usu.usu_id, usu.usu_user, usu.usu_fec_reg, emp.emp_id, emp.emp_nom, emp.emp_ape, et.et_des '
                . 'FROM usuario usu '
                . 'INNER JOIN empleado emp ON emp.emp_id = usu.emp_id '
                . 'INNER JOIN empleado_tipo et ON et.et_id = emp.et_id '
                . 'WHERE usu.usu_est = 1 ';
            if ($param_last_date) {
                $sql .= 'AND usu.usu_fec_reg < :last_date ';
            }
            if ($param_filter) {
                $sql .= 'AND (usu.usu_user like :filter OR emp.emp_nom like :filter OR emp.emp_ape like :filter) ';
            }
            $sql .= 'ORDER BY usu.usu_fec_reg DESC '
                . 'LIMIT 10';
            $stmt = $cn->prepare($sql);
            //            $stmt->bindParam(':usu_id', $data->usu_id, PDO::PARAM_INT);
            if ($param_last_date) {
                $stmt->bindParam(':last_date', $param_last_date, PDO::PARAM_STR);
            }
            if ($param_filter) {
                $param_filter = $param_filter . '%';
                $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
                $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
                $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
            }
            $stmt->execute();
            $userData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"userData": ' . json_encode($userData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Verify User
$app->get('/v1/user/verify', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_filter = $request->getParam('filter');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT usu_id FROM usuario WHERE usu_est = 1 AND usu_user = :filter ';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
            $stmt->execute();
            // $clientData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cnn->close();
            // echo '{"clientData": ' . json_encode($clientData) . '}';
            echo json_encode($row);
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Add User
$app->post('/v1/user/add', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($data->usu_id);
        $userData = new stdClass;

        if ($data->token == $token) {
            $cn = $cnn->open();
            $cn->beginTransaction();
            $sql1 = 'INSERT INTO empleado (emp_nom, emp_ape, emp_est, emp_fec_reg, et_id) '
                . 'VALUES (:emp_nom, :emp_ape, 1, :emp_fec_reg, :et_id)';
            $stmt1 = $cn->prepare($sql1);
            $stmt1->bindParam(':emp_nom', $data->emp_nom, PDO::PARAM_STR);
            $stmt1->bindParam(':emp_ape', $data->emp_ape, PDO::PARAM_STR);
            $stmt1->bindParam(':emp_fec_reg', $data->now, PDO::PARAM_STR);
            $stmt1->bindParam(':et_id', $data->et_id, PDO::PARAM_INT);
            $stmt1->execute();
            $emp_id = $cn->lastInsertId();

            $sql2 = "INSERT INTO usuario (usu_user, usu_pass, usu_est, usu_fec_reg, usu_fec_mod, emp_id) "
                . "VALUES (:usu_user, :usu_pass, 1, :usu_fec_reg, :usu_fec_mod, :emp_id)";
            $stmt2 = $cn->prepare($sql2);
            $stmt2->bindParam(':usu_user', $data->usu_user, PDO::PARAM_STR);
            $usu_pass = hash('sha256', $data->usu_pass);
            $stmt2->bindParam(':usu_pass', $usu_pass, PDO::PARAM_STR);
            $stmt2->bindParam(':usu_fec_reg', $data->now, PDO::PARAM_STR);
            $stmt2->bindParam(':usu_fec_mod', $data->now, PDO::PARAM_STR);
            $stmt2->bindParam(':emp_id', $emp_id, PDO::PARAM_INT);
            if ($stmt2->execute()) {
                $userData->status = "success";
            } else {
                $userData->status = "fail";
            }
            $cn->commit();
            $cnn->close();
            echo '{"userData": ' . json_encode($userData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Type
$app->get('/v1/user/type', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT et_id, et_des FROM empleado_tipo WHERE et_est = 1 ORDER BY et_id';
            $stmt = $cn->prepare($sql);
            $stmt->execute();
            $userData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"userData": ' . json_encode($userData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Modify Password
$app->post('/v1/user/password', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($data->usu_id);
        $userData = new stdClass;

        if ($data->token == $token) {
            $cn = $cnn->open();
            $cn->beginTransaction();
            $sql = 'UPDATE usuario '
                . 'SET usu_pass = :usu_pass, usu_fec_mod = :usu_fec_mod '
                . 'WHERE usu_est = 1 AND usu_id = :usu_id ';
            $stmt = $cn->prepare($sql);
            $usu_pass = hash('sha256', $data->usu_pass);
            $stmt->bindParam(':usu_pass', $usu_pass, PDO::PARAM_STR);
            $stmt->bindParam(':usu_fec_mod', $data->now, PDO::PARAM_STR);
            $stmt->bindParam(':usu_id', $data->usu_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $userData->status = "success";
            } else {
                $userData->status = "fail";
            }
            $cn->commit();
            $cnn->close();
            echo '{"userData": ' . json_encode($userData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Users Select
$app->get('/v1/user/select', function (Request $request, Response $response) {
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT usu.usu_id, emp.emp_nom, emp.emp_ape '
                . 'FROM usuario usu '
                . 'INNER JOIN empleado emp ON emp.emp_id = usu.emp_id '
                . 'WHERE usu.usu_est = 1 '
                . 'ORDER BY usu.usu_id ';
            $stmt = $cn->prepare($sql);
            $stmt->execute();
            $userData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"userData": ' . json_encode($userData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});
