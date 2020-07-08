<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Clients
$app->get('/v1/client/all', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_et_id = $request->getParam('et_id');
    $param_last_date = $request->getParam('last_date');
    $param_filter = $request->getParam('filter');
    $param_usu_reg = $request->getParam('usu_reg');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, cli.cli_tel, cli.cli_dir, '
                . 'cli.cli_num_cue, cli.cli_gir_neg, cli.cli_obs, cli.cli_est, cli.cli_fec_reg, emp.emp_nom, emp.emp_ape, '
                . 'cli.cli_fec_apr, CONCAT(emp2.emp_nom, \' \', emp2.emp_ape) AS cli_usu_apr, '
                . 'cli.cli_fec_mod, CONCAT(emp3.emp_nom, \' \', emp3.emp_ape) AS cli_usu_mod, '
                . 'SUBSTRING(cli.cli_ubi, 1, 2) AS ubi_dep_cod, ubi.ubi_dep, '
                . 'SUBSTRING(cli.cli_ubi, 3, 2) AS ubi_pro_cod, ubi.ubi_pro, '
                . 'SUBSTRING(cli.cli_ubi, 5, 2) AS ubi_dis_cod, ubi.ubi_dis '
                . 'FROM cliente cli '
                . 'INNER JOIN usuario usu ON usu.usu_id = cli.cli_usu_reg '
                . 'INNER JOIN empleado emp ON emp.emp_id = usu.emp_id '
                . 'LEFT JOIN usuario usu2 ON usu2.usu_id = cli.cli_usu_apr '
                . 'LEFT JOIN usuario usu3 ON usu3.usu_id = cli.cli_usu_mod '
                . 'LEFT JOIN empleado emp2 ON emp2.emp_id = usu2.emp_id '
                . 'LEFT JOIN empleado emp3 ON emp3.emp_id = usu3.emp_id '
                . 'LEFT JOIN ubigeo ubi ON ubi.ubi_cod = cli.cli_ubi '
                // . 'WHERE cli.cli_est = 1 ';
                // . 'WHERE cli.cli_est <> 3 ';
                . 'WHERE true ';
            if ($param_et_id && $param_et_id != '1') {
                $sql .= 'AND cli.cli_est <> 3 AND cli.cli_usu_reg = :usu_id ';
            }
            // if ($param_last_date || $param_filter) {
            if ($param_last_date) {
                $sql .= 'AND cli.cli_fec_reg < :last_date ';
            }
            if ($param_filter) {
                $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
            }
            if ($param_usu_reg && $param_usu_reg != '0') {
                $sql .= 'AND cli.cli_usu_reg = :cli_usu_reg ';
            }
            // }
            $sql .= 'ORDER BY cli.cli_fec_reg DESC '
                . 'LIMIT 10';
            $stmt = $cn->prepare($sql);
            //            $stmt->bindParam(':usu_id', $data->usu_id, PDO::PARAM_INT);
            if ($param_et_id && $param_et_id != '1') {
                $stmt->bindParam(':usu_id', $param_usu_id, PDO::PARAM_INT);
            }
            if ($param_last_date) {
                $stmt->bindParam(':last_date', $param_last_date, PDO::PARAM_STR);
            }
            if ($param_filter) {
                $param_filter = $param_filter . '%';
                $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
                $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
                $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
            }
            if ($param_usu_reg && $param_usu_reg != '0') {
                $stmt->bindParam(':cli_usu_reg', $param_usu_reg, PDO::PARAM_INT);
            }
            $stmt->execute();
            $clientData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"clientData": ' . json_encode($clientData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Search Client
$app->get('/v1/client/search', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_et_id = $request->getParam('et_id');
    $param_last_date = $request->getParam('last_date');
    $param_filter = $request->getParam('filter');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, cli.cli_est '
                . 'FROM cliente cli '
                . 'WHERE cli.cli_est <> 3 ';
            if ($param_et_id && $param_et_id != '1') {
                $sql .= 'AND cli.cli_usu_reg = :usu_id ';
            }
            if ($param_last_date) {
                $sql .= 'AND cli.cli_fec_reg < :last_date ';
            }
            if ($param_filter) {
                $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
            }
            $sql .= 'ORDER BY cli.cli_fec_reg DESC '
                . 'LIMIT 10';
            $stmt = $cn->prepare($sql);
            if ($param_et_id && $param_et_id != '1') {
                $stmt->bindParam(':usu_id', $param_usu_id, PDO::PARAM_INT);
            }
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
            $clientData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"clientData": ' . json_encode($clientData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Verify Client
$app->get('/v1/client/verify', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_filter = $request->getParam('filter');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT cli_id, cli_nom, cli_ape FROM cliente WHERE (cli_est = 0 OR cli_est = 1) AND cli_dni = :filter ';
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

//Verify Client State
$app->get('/v1/client/verify2', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_filter = $request->getParam('filter');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT cli_id, cli_nom, cli_ape FROM cliente WHERE cli_est = 1 AND cli_dni = :filter ';
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

//Add Client
$app->post('/v1/client/add', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($data->usu_id);
        $clientData = new stdClass;

        if ($data->token == $token) {
            $cn = $cnn->open();
            $cn->beginTransaction();
            $sql = 'INSERT INTO cliente (cli_nom, cli_ape, cli_dni, cli_tel, cli_dir, cli_ubi, cli_num_cue, cli_gir_neg, cli_obs, cli_est, cli_fec_reg, cli_usu_reg) '
                . 'VALUES (:cli_nom, :cli_ape, :cli_dni, :cli_tel, :cli_dir, :cli_ubi, :cli_num_cue, :cli_gir_neg, :cli_obs, 0, :cli_fec_reg, :cli_usu_reg)';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':cli_nom', $data->cli_nom, PDO::PARAM_STR);
            $stmt->bindParam(':cli_ape', $data->cli_ape, PDO::PARAM_STR);
            $stmt->bindParam(':cli_dni', $data->cli_dni, PDO::PARAM_STR);
            $stmt->bindParam(':cli_tel', $data->cli_tel, PDO::PARAM_STR);
            $stmt->bindParam(':cli_dir', $data->cli_dir, PDO::PARAM_STR);
            $stmt->bindParam(':cli_ubi', $data->cli_ubi, PDO::PARAM_STR);
            $stmt->bindParam(':cli_num_cue', $data->cli_num_cue, PDO::PARAM_STR);
            $stmt->bindParam(':cli_gir_neg', $data->cli_gir_neg, PDO::PARAM_STR);
            $stmt->bindParam(':cli_obs', $data->cli_obs, PDO::PARAM_STR);
            $stmt->bindParam(':cli_fec_reg', $data->now, PDO::PARAM_STR);
            $stmt->bindParam(':cli_usu_reg', $data->usu_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $clientData->status = "success";
            } else {
                $clientData->status = "fail";
            }
            $cn->commit();
            $cnn->close();
            echo '{"clientData": ' . json_encode($clientData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Update Client
$app->post('/v1/client/update', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($data->usu_id);
        $clientData = new stdClass;

        if ($data->token == $token) {
            $cn = $cnn->open();
            $cn->beginTransaction();
            $sql = 'UPDATE cliente '
                . 'SET cli_nom = :cli_nom, '
                . 'cli_ape = :cli_ape, '
                . 'cli_dni = :cli_dni, '
                . 'cli_tel = :cli_tel, '
                . 'cli_dir = :cli_dir, '
                . 'cli_ubi = :cli_ubi, '
                . 'cli_num_cue = :cli_num_cue, '
                . 'cli_gir_neg = :cli_gir_neg, '
                . 'cli_obs = :cli_obs, '
                . 'cli_fec_mod = :cli_fec_mod, '
                . 'cli_usu_mod = :cli_usu_mod '
                . 'WHERE cli_id = :cli_id';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':cli_nom', $data->cli_nom, PDO::PARAM_STR);
            $stmt->bindParam(':cli_ape', $data->cli_ape, PDO::PARAM_STR);
            $stmt->bindParam(':cli_dni', $data->cli_dni, PDO::PARAM_STR);
            $stmt->bindParam(':cli_tel', $data->cli_tel, PDO::PARAM_STR);
            $stmt->bindParam(':cli_dir', $data->cli_dir, PDO::PARAM_STR);
            $stmt->bindParam(':cli_ubi', $data->cli_ubi, PDO::PARAM_STR);
            $stmt->bindParam(':cli_num_cue', $data->cli_num_cue, PDO::PARAM_STR);
            $stmt->bindParam(':cli_gir_neg', $data->cli_gir_neg, PDO::PARAM_STR);
            $stmt->bindParam(':cli_obs', $data->cli_obs, PDO::PARAM_STR);
            $stmt->bindParam(':cli_fec_mod', $data->now, PDO::PARAM_STR);
            $stmt->bindParam(':cli_usu_mod', $data->usu_id, PDO::PARAM_INT);
            $stmt->bindParam(':cli_id', $data->cli_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $clientData->status = "success";
            } else {
                $clientData->status = "fail";
            }
            $cn->commit();
            $cnn->close();
            echo '{"clientData": ' . json_encode($clientData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Aprove Client
$app->post('/v1/client/aprove', function (Request $request, Response $response) {
    $data = json_decode($request->getBody());
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($data->usu_id);
        $clientData = new stdClass;

        if ($data->token == $token) {
            $cn = $cnn->open();
            $cn->beginTransaction();
            $sql = 'UPDATE cliente '
                . 'SET cli_est = :cli_est, ';
            if ($data->cli_est == '3') {
                $sql .= 'cli_usu_eli = :cli_usu_eli, cli_fec_eli = :cli_fec_eli ';
            } else {
                $sql .= 'cli_usu_apr = :cli_usu_apr, cli_fec_apr = :cli_fec_apr ';
            }
            $sql .= 'WHERE cli_id = :cli_id ';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':cli_est', $data->cli_est, PDO::PARAM_STR);
            if ($data->cli_est == '3') {
                $stmt->bindParam(':cli_usu_eli', $data->usu_id, PDO::PARAM_INT);
                $stmt->bindParam(':cli_fec_eli', $data->now, PDO::PARAM_STR);
            } else {
                $stmt->bindParam(':cli_usu_apr', $data->usu_id, PDO::PARAM_INT);
                $stmt->bindParam(':cli_fec_apr', $data->now, PDO::PARAM_STR);
            }
            $stmt->bindParam(':cli_id', $data->cli_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $clientData->status = "success";
            } else {
                $clientData->status = "fail";
            }
            $cn->commit();
            $cnn->close();
            echo '{"clientData": ' . json_encode($clientData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Verify Client's Loan
$app->get('/v1/client/loan', function (Request $request, Response $response) {
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_cli_id = $request->getParam('cli_id');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT COUNT(cli.cli_id) AS count '
                . 'FROM cliente cli '
                . 'INNER JOIN prestamo pre ON pre.cli_id = cli.cli_id '
                . 'INNER JOIN pago pag ON pag.pre_id = pre.pre_id '
                . 'WHERE cli.cli_id = :cli_id AND pag.pag_est = 1 AND pag.pag_mon_pen > 0';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':cli_id', $param_cli_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cnn->close();
            echo json_encode($row);
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});
