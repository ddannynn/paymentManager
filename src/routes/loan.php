<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Loans
$app->get('/v1/loan/all', function (Request $request, Response $response) {
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
      $sql = 'SELECT pre.pre_id, LPAD(pre.pre_id, 10, 0) pre_cod, pre.pre_mon, pre.pre_est, pre.pre_fec_reg, pag.pag_id, '
        . 'IFNULL(pag.pag_mon_tot, 0.00) pag_mon_tot, '
        . 'IFNULL(pag.pag_mon_pag, 0.00) pag_mon_pag, '
        . 'IFNULL(pag.pag_mon_pen, 0.00) pag_mon_pen, '
        . 'IFNULL(ROUND(pag.pag_mon_tot / tas.tas_pla, 2), 0.00) pag_mon_cuo, '
        . 'cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, '
        . 'tas.tas_des, tas.tas_pla '
        . 'FROM prestamo pre '
        . 'LEFT JOIN pago pag ON pag.pre_id = pre.pre_id AND pag.pag_est = 1 '
        . 'INNER JOIN cliente cli ON cli.cli_id = pre.cli_id '
        . 'INNER JOIN tasa tas ON tas.tas_id = pre.tas_id '
        . 'WHERE pre.pre_est <> 3 AND pag.pag_mon_pen > 0 ';
      if ($param_et_id && $param_et_id != '1') {
        $sql .= 'AND pre.pre_usu_reg = :usu_id ';
      }
      // if ($param_last_date || $param_filter) {
      if ($param_last_date) {
        $sql .= 'AND pre.pre_fec_reg < :last_date ';
      }
      if ($param_filter) {
        $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
      }
      if ($param_usu_reg && $param_usu_reg != '0') {
        $sql .= 'AND pre.pre_usu_reg = :pre_usu_reg ';
      }
      // }
      $sql .= 'ORDER BY pre.pre_fec_reg DESC '
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
        $stmt->bindParam(':pre_usu_reg', $param_usu_reg, PDO::PARAM_INT);
      }
      $stmt->execute();
      $loanData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $cnn->close();
      echo '{"loanData": ' . json_encode($loanData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Loan Detail
$app->get('/v1/loan/detail', function (Request $request, Response $response) {
  $param_usu_id = $request->getParam('usu_id');
  $param_token = $request->getParam('token');
  $param_pre_id = $request->getParam('pre_id');
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($param_usu_id);

    if ($param_token == $token) {
      $cn = $cnn->open();
      $sql = 'SELECT pre.pre_id, LPAD(pre.pre_id, 10, 0) pre_cod, pre.pre_mon, pre.pre_est, pre.pre_fec_reg, '
        . 'pag.pag_id, '
        . 'IFNULL(pag.pag_mon_tot, 0.00) pag_mon_tot, '
        . 'IFNULL(pag.pag_mon_pag, 0.00) pag_mon_pag, '
        . 'IFNULL(pag.pag_mon_pen, 0.00) pag_mon_pen, '
        . 'IFNULL(ROUND(pag.pag_mon_tot / tas.tas_pla, 2), 0.00) pag_mon_cuo, '
        . 'cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, '
        . 'tas.tas_des, tas.tas_pla '
        . 'FROM prestamo pre '
        . 'LEFT JOIN pago pag ON pag.pre_id = pre.pre_id AND pag.pag_est = 1 '
        . 'INNER JOIN cliente cli ON cli.cli_id = pre.cli_id '
        . 'INNER JOIN tasa tas ON tas.tas_id = pre.tas_id '
        . 'WHERE pre.pre_id = :pre_id ';
      $stmt = $cn->prepare($sql);
      $stmt->bindParam(':pre_id', $param_pre_id, PDO::PARAM_INT);
      $stmt->execute();
      $loanData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $cnn->close();
      echo '{"loanData": ' . json_encode($loanData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Add Loan
$app->post('/v1/loan/add', function (Request $request, Response $response) {
  $data = json_decode($request->getBody());
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($data->usu_id);
    $loanData = new stdClass;

    if ($data->token == $token) {
      $cn = $cnn->open();
      $cn->beginTransaction();
      $sql1 = 'INSERT INTO prestamo (pre_mon, pre_est, pre_fec_reg, pre_usu_reg, cli_id, tas_id) '
        . 'VALUES (:pre_mon, 1, :pre_fec_reg, :pre_usu_reg, :cli_id, :tas_id)';
      $stmt1 = $cn->prepare($sql1);
      $stmt1->bindParam(':pre_mon', $data->pre_mon, PDO::PARAM_STR);
      $stmt1->bindParam(':pre_fec_reg', $data->now, PDO::PARAM_STR);
      $stmt1->bindParam(':pre_usu_reg', $data->usu_id, PDO::PARAM_INT);
      $stmt1->bindParam(':cli_id', $data->cli_id, PDO::PARAM_INT);
      $stmt1->bindParam(':tas_id', $data->tas_id, PDO::PARAM_INT);
      $stmt1->execute();
      $pre_id = $cn->lastInsertId();

      $sql2 = "INSERT INTO pago (pag_mon_tot, pag_mon_pag, pag_mon_pen, pag_est, pag_fec_mod, pre_id) "
        . "VALUES (:pag_mon_tot, 0, :pag_mon_tot, 1, :pag_fec_mod, :pre_id)";
      $stmt2 = $cn->prepare($sql2);
      $stmt2->bindParam(':pag_mon_tot', $data->pag_mon_tot, PDO::PARAM_STR);
      $stmt2->bindParam(':pag_mon_tot', $data->pag_mon_tot, PDO::PARAM_STR);
      $stmt2->bindParam(':pag_fec_mod', $data->now, PDO::PARAM_STR);
      $stmt2->bindParam(':pre_id', $pre_id, PDO::PARAM_INT);
      if ($stmt2->execute()) {
        $loanData->status = "success";
      } else {
        $loanData->status = "fail";
      }
      $cn->commit();
      $cnn->close();
      echo '{"loanData": ' . json_encode($loanData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Loans History
$app->get('/v1/loan/history', function (Request $request, Response $response) {
  // $data = json_decode($request->getBody());
  $param_usu_id = $request->getParam('usu_id');
  $param_token = $request->getParam('token');
  $param_et_id = $request->getParam('et_id');
  $param_cli_id = $request->getParam('cli_id');
  $param_last_date = $request->getParam('last_date');
  $param_filter = $request->getParam('filter');
  $param_usu_reg = $request->getParam('usu_reg');
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($param_usu_id);

    if ($param_token == $token) {
      $cn = $cnn->open();
      $sql = 'SELECT pre.pre_id, LPAD(pre.pre_id, 10, 0) pre_cod, pre.pre_mon, pre.pre_est, pre.pre_fec_reg, pag.pag_id, '
        . 'IFNULL(pag.pag_mon_tot, 0.00) pag_mon_tot, '
        . 'IFNULL(pag.pag_mon_pag, 0.00) pag_mon_pag, '
        . 'IFNULL(pag.pag_mon_pen, 0.00) pag_mon_pen, '
        . 'IFNULL(ROUND(pag.pag_mon_tot / tas.tas_pla, 2), 0.00) pag_mon_cuo, '
        . 'cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, '
        . 'tas.tas_des, tas.tas_pla '
        . 'FROM prestamo pre '
        . 'LEFT JOIN pago pag ON pag.pre_id = pre.pre_id AND pag.pag_est = 1 '
        . 'INNER JOIN cliente cli ON cli.cli_id = pre.cli_id '
        . 'INNER JOIN tasa tas ON tas.tas_id = pre.tas_id '
        . 'WHERE TRUE ';
      if ($param_et_id && $param_et_id != '1') {
        $sql .= 'AND pre.pre_usu_reg = :usu_id ';
      }
      if ($param_last_date) {
        $sql .= 'AND pre.pre_fec_reg < :last_date ';
      }
      if ($param_filter) {
        $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
      }
      if ($param_cli_id) {
        $sql .= 'AND pre.cli_id = :cli_id ';
      }
      if ($param_usu_reg && $param_usu_reg != '0') {
        $sql .= 'AND pre.pre_usu_reg = :pre_usu_reg ';
      }
      $sql .= 'ORDER BY pre.pre_fec_reg DESC ';
      if (!$param_cli_id) {
        $sql .= 'LIMIT 10';
      }
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
      if ($param_cli_id) {
        $stmt->bindParam(':cli_id', $param_cli_id, PDO::PARAM_INT);
      }
      if ($param_usu_reg && $param_usu_reg != '0') {
        $stmt->bindParam(':pre_usu_reg', $param_usu_reg, PDO::PARAM_INT);
      }
      $stmt->execute();
      $loanData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $cnn->close();
      echo '{"loanData": ' . json_encode($loanData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Loan's State
$app->post('/v1/loan/state', function (Request $request, Response $response) {
  $data = json_decode($request->getBody());
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($data->usu_id);
    $loanData = new stdClass;

    if ($data->token == $token) {
      $cn = $cnn->open();
      $cn->beginTransaction();
      $sql1 = 'UPDATE pago '
        . 'SET pag_est = :pag_est '
        . 'WHERE pag_id = :pag_id ';
      $stmt1 = $cn->prepare($sql1);
      $stmt1->bindParam(':pag_est', $data->pre_est, PDO::PARAM_STR);
      $stmt1->bindParam(':pag_id', $data->pre_id, PDO::PARAM_INT);
      $stmt1->execute();

      $sql2 = 'UPDATE prestamo '
        . 'SET pre_est = :pre_est, '
        . 'pre_usu_eli = :pre_usu_eli, pre_fec_eli = :pre_fec_eli '
        . 'WHERE pre_id = :pre_id ';
      $stmt2 = $cn->prepare($sql2);
      $stmt2->bindParam(':pre_est', $data->pre_est, PDO::PARAM_STR);
      $stmt2->bindParam(':pre_usu_eli', $data->usu_id, PDO::PARAM_INT);
      $stmt2->bindParam(':pre_fec_eli', $data->now, PDO::PARAM_STR);
      $stmt2->bindParam(':pre_id', $data->pre_id, PDO::PARAM_INT);
      if ($stmt2->execute()) {
        $loanData->status = "success";
      } else {
        $loanData->status = "fail";
      }
      $cn->commit();
      $cnn->close();
      echo '{"loanData": ' . json_encode($loanData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});
