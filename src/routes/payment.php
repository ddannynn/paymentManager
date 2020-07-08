<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Payment Total
$app->get('/v1/payment/total', function (Request $request, Response $response) {
  // $data = json_decode($request->getBody());
  $param_usu_id = $request->getParam('usu_id');
  $param_token = $request->getParam('token');
  $param_et_id = $request->getParam('et_id');
  $param_rangeDate = $request->getParam('rangeDate');
  $param_filter = $request->getParam('filter');
  $param_usu_reg = $request->getParam('usu_reg');
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($param_usu_id);

    if ($param_token == $token) {
      $cn = $cnn->open();
      $sql = 'SELECT IFNULL(COUNT(pd.pd_id), 0) AS count, IFNULL(SUM(pd.pd_mon), 0.00) AS sum '
        . 'FROM pago_detalle pd '
        . 'INNER JOIN pago pag ON pag.pag_id = pd.pag_id '
        . 'INNER JOIN prestamo pre ON pre.pre_id = pag.pre_id AND pag.pag_est = 1 '
        . 'INNER JOIN cliente cli ON cli.cli_id = pre.cli_id '
        . 'WHERE pd.pd_est = 1 AND CAST(pd.pd_fec_reg AS DATE) = :rangeDate ';
      if ($param_et_id && $param_et_id != '1') {
        $sql .= 'AND pre.pre_usu_reg = :usu_id ';
      }
      if ($param_filter) {
        $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
      }
      if ($param_usu_reg && $param_usu_reg != '0') {
        $sql .= 'AND pre.pre_usu_reg = :pre_usu_reg ';
      }
      $stmt = $cn->prepare($sql);
      $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
      if ($param_et_id && $param_et_id != '1') {
        $stmt->bindParam(':usu_id', $param_usu_id, PDO::PARAM_INT);
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
      $reportData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $cnn->close();
      echo '{"reportData": ' . json_encode($reportData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Payments
$app->get('/v1/payment/all', function (Request $request, Response $response) {
  // $data = json_decode($request->getBody());
  $param_usu_id = $request->getParam('usu_id');
  $param_token = $request->getParam('token');
  $param_et_id = $request->getParam('et_id');
  $param_rangeDate = $request->getParam('rangeDate');
  $param_last_date = $request->getParam('last_date');
  $param_filter = $request->getParam('filter');
  $param_usu_reg = $request->getParam('usu_reg');
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($param_usu_id);

    if ($param_token == $token) {
      $cn = $cnn->open();
      $sql = 'SELECT pre.pre_id, LPAD(pre.pre_id, 10, 0) pre_cod, pre.pre_mon, pre.pre_est, pre.pre_fec_reg, '
        . 'pag.pag_id, pag.pag_fec_mod, '
        . 'IFNULL(pag.pag_mon_tot, 0.00) pag_mon_tot, '
        . 'IFNULL(pag.pag_mon_pag, 0.00) pag_mon_pag, '
        . 'IFNULL(pag.pag_mon_pen, 0.00) pag_mon_pen, '
        . 'IFNULL(ROUND(pag.pag_mon_tot / tas.tas_pla, 2), 0.00) pag_mon_cuo, '
        . 'IFNULL(SUM(pd.pd_mon), 0.00) pd_mon, '
        . 'cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, '
        . 'tas.tas_des, tas.tas_pla '
        . 'FROM prestamo pre '
        . 'INNER JOIN pago pag ON pag.pre_id = pre.pre_id AND pag.pag_est = 1 '
        . 'LEFT JOIN pago_detalle pd ON pd.pag_id = pag.pag_id AND pd.pd_est = 1 ';
      if ($param_rangeDate) {
        $sql .= 'AND CAST(pd.pd_fec_reg AS DATE) = :rangeDate ';
      }
      $sql .= 'INNER JOIN cliente cli ON cli.cli_id = pre.cli_id '
        . 'INNER JOIN tasa tas ON tas.tas_id = pre.tas_id '
        . 'WHERE (pre.pre_est = 1 OR pre.pre_est = 5) ';
      // . 'WHERE (pre.pre_est = 1 OR pre.pre_est = 5) ';
      if ($param_rangeDate) {
        $sql .= 'AND (CASE '
          . 'WHEN (pre.pre_est = 1 AND CAST(pre.pre_fec_reg AS DATE) <= :rangeDate) THEN 1 '
          . 'WHEN (pre.pre_est = 5 AND CAST(pre.pre_fec_pag AS DATE) >= :rangeDate) THEN 1 '
          . 'ELSE 0 '
          . 'END) = 1 ';
      }
      if ($param_et_id && $param_et_id != '1') {
        $sql .= 'AND pre.pre_usu_reg = :usu_id ';
      }
      if ($param_last_date) {
        $sql .= 'AND pag.pag_fec_mod < :last_date ';
      }
      // if ($param_last_date || $param_filter) {
      if ($param_filter) {
        $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
      }
      if ($param_usu_reg && $param_usu_reg != '0') {
        $sql .= 'AND pre.pre_usu_reg = :pre_usu_reg ';
      }
      // }
      $sql .= 'GROUP BY pre.pre_id '
        . 'ORDER BY pag.pag_fec_mod DESC '
        . 'LIMIT 10';
      $stmt = $cn->prepare($sql);
      if ($param_rangeDate) {
        $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
        $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
        $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
      }
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
      $paymentData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $cnn->close();
      echo '{"paymentData": ' . json_encode($paymentData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

// //Payments
// $app->get('/v1/payment/all', function (Request $request, Response $response) {
//     // $data = json_decode($request->getBody());
//     $param_usu_id = $request->getParam('usu_id');
//     $param_token = $request->getParam('token');
//     $param_et_id = $request->getParam('et_id');
//     $param_rangeDate = $request->getParam('rangeDate');
//     $param_last_date = $request->getParam('last_date');
//     $param_filter = $request->getParam('filter');
//     try {
//         $cnn = new Connection();
//         $token = $cnn->apiKey($param_usu_id);

//         if ($param_token == $token) {
//             $cn = $cnn->open();
//             $sql = 'SELECT IFNULL(pd.pd_mon, 0.00) pd_mon, pd.pd_fec_reg, pag.pag_id, IFNULL(pag.pag_mon_tot, 0.00) pag_mon_tot, '
//                 . 'IFNULL(pag.pag_mon_pag, 0.00) pag_mon_pag, '
//                 . 'IFNULL(pag.pag_mon_pen, 0.00) AS pag_mon_pen, pag.pag_fec_mod, '
//                 . 'IFNULL(ROUND(pag.pag_mon_tot / tas.tas_pla, 2), 0.00) pag_mon_cuo, '
//                 . 'pre.pre_id, LPAD(pre.pre_id, 10, 0) pre_cod, pre.pre_mon, pre.pre_est, pre.pre_fec_reg, '
//                 . 'cli.cli_id, cli.cli_nom, cli.cli_ape, cli.cli_dni, '
//                 . 'tas.tas_des, tas.tas_pla '
//                 . 'FROM prestamo pre '
//                 . 'INNER JOIN pago pag ON pag.pre_id = pre.pre_id AND pag.pag_est = 1 '
//                 . 'LEFT JOIN pago_detalle pd ON pd.pag_id = pag.pag_id AND pd.pd_est = 1 ';
//             if ($param_rangeDate) {
//                 $sql .= 'AND CAST(pd.pd_fec_reg AS DATE) = :rangeDate ';
//             }
//             $sql .= 'INNER JOIN cliente cli ON cli.cli_id = pre.cli_id '
//                 . 'INNER JOIN tasa tas ON tas.tas_id = pre.tas_id '
//                 . 'WHERE (pre.pre_est = 1 OR pre.pre_est = 5) ';
//             if ($param_et_id && $param_et_id != '1') {
//                 $sql .= 'AND pre.pre_usu_reg = :usu_id ';
//             }
//             if ($param_last_date) {
//                 $sql .= 'AND pag.pag_fec_mod < :last_date ';
//             }
//             // if ($param_last_date || $param_filter) {
//             if ($param_filter) {
//                 $sql .= 'AND (cli.cli_dni like :filter OR cli.cli_nom like :filter OR cli.cli_ape like :filter) ';
//             }
//             // }
//             $sql .= 'ORDER BY pag.pag_fec_mod DESC '
//                 . 'LIMIT 10';
//             $stmt = $cn->prepare($sql);
//             if ($param_rangeDate) {
//                 $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
//             }
//             if ($param_et_id && $param_et_id != '1') {
//                 $stmt->bindParam(':usu_id', $param_usu_id, PDO::PARAM_INT);
//             }
//             if ($param_last_date) {
//                 $stmt->bindParam(':last_date', $param_last_date, PDO::PARAM_STR);
//             }
//             if ($param_filter) {
//                 $param_filter = $param_filter . '%';
//                 $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
//                 $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
//                 $stmt->bindParam(':filter', $param_filter, PDO::PARAM_STR);
//             }
//             $stmt->execute();
//             $paymentData = $stmt->fetchAll(PDO::FETCH_OBJ);
//             $cnn->close();
//             echo '{"paymentData": ' . json_encode($paymentData) . '}';
//         }
//     } catch (PDOException $e) {
//         echo '{"error" : {"text":' . $e->getMessage() . '}';
//     }
// });

//Loan Detail
$app->get('/v1/payment/detail', function (Request $request, Response $response) {
  $param_usu_id = $request->getParam('usu_id');
  $param_token = $request->getParam('token');
  $param_pag_id = $request->getParam('pag_id');
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($param_usu_id);

    if ($param_token == $token) {
      $cn = $cnn->open();
      $sql = 'SELECT pd.pd_id, pd.pd_mon, pd.pd_fec_reg, '
        . 'emp.emp_nom, emp.emp_ape '
        . 'FROM pago_detalle pd '
        . 'INNER JOIN usuario usu ON usu.usu_id = pd.usu_id '
        . 'INNER JOIN empleado emp ON emp.emp_id = usu.emp_id '
        . 'WHERE pd.pd_est = 1 AND pd.pag_id = :pag_id '
        . 'ORDER BY pd.pd_fec_reg DESC';
      $stmt = $cn->prepare($sql);
      $stmt->bindParam(':pag_id', $param_pag_id, PDO::PARAM_INT);
      $stmt->execute();
      $paymentData = $stmt->fetchAll(PDO::FETCH_OBJ);
      $cnn->close();
      echo '{"paymentData": ' . json_encode($paymentData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Add Loan
$app->post('/v1/payment/add', function (Request $request, Response $response) {
  $data = json_decode($request->getBody());
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($data->usu_id);
    $paymentData = new stdClass;

    if ($data->token == $token) {
      $cn = $cnn->open();
      $cn->beginTransaction();
      $sql1 = 'INSERT INTO pago_detalle (pd_mon, pd_est, pd_fec_reg, pag_id, usu_id) '
        . 'VALUES (:pd_mon, 1, :pd_fec_reg, :pag_id, :usu_id) ';
      $stmt1 = $cn->prepare($sql1);
      $stmt1->bindParam(':pd_mon', $data->pd_mon, PDO::PARAM_STR);
      $stmt1->bindParam(':pd_fec_reg', $data->now, PDO::PARAM_STR);
      $stmt1->bindParam(':pag_id', $data->pag_id, PDO::PARAM_INT);
      $stmt1->bindParam(':usu_id', $data->usu_id, PDO::PARAM_INT);
      $stmt1->execute();

      $sql2 = 'UPDATE pago '
        . 'SET pag_mon_pag = pag_mon_pag + :pd_mon, '
        . '    pag_mon_pen = pag_mon_pen - :pd_mon, '
        . '    pag_fec_mod = :pag_fec_mod '
        . 'WHERE pag_id = :pag_id ';
      $stmt2 = $cn->prepare($sql2);
      $stmt2->bindParam(':pd_mon', $data->pd_mon, PDO::PARAM_STR);
      $stmt2->bindParam(':pd_mon', $data->pd_mon, PDO::PARAM_STR);
      $stmt2->bindParam(':pag_fec_mod', $data->now, PDO::PARAM_STR);
      $stmt2->bindParam(':pag_id', $data->pag_id, PDO::PARAM_INT);
      if ($data->tot && $data->tot == '1') {
        $stmt2->execute();

        $sql3 = 'UPDATE prestamo '
          . 'SET pre_est = 5, '
          . '    pre_fec_pag = :pre_fec_pag, '
          . '    pre_usu_pag = :pre_usu_pag '
          . 'WHERE pre_id = :pre_id ';
        $stmt3 = $cn->prepare($sql3);
        $stmt3->bindParam(':pre_fec_pag', $data->now, PDO::PARAM_STR);
        $stmt3->bindParam(':pre_usu_pag', $data->usu_id, PDO::PARAM_INT);
        $stmt3->bindParam(':pre_id', $data->pre_id, PDO::PARAM_INT);
        if ($stmt3->execute()) {
          $paymentData->status = "success";
        } else {
          $paymentData->status = "fail";
        }
      } else {
        if ($stmt2->execute()) {
          $paymentData->status = "success";
        } else {
          $paymentData->status = "fail";
        }
      }
      $cn->commit();
      $cnn->close();
      echo '{"paymentData": ' . json_encode($paymentData) . '}';
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});

//Verify Payments
$app->get('/v1/payment/verify', function (Request $request, Response $response) {
  $param_usu_id = $request->getParam('usu_id');
  $param_token = $request->getParam('token');
  $param_pre_id = $request->getParam('pre_id');
  try {
    $cnn = new Connection();
    $token = $cnn->apiKey($param_usu_id);

    if ($param_token == $token) {
      $cn = $cnn->open();
      $sql = 'SELECT COUNT(pd.pd_id) AS count '
        . 'FROM pago_detalle pd '
        . 'INNER JOIN pago pag ON pag.pag_id = pd.pag_id '
        . 'INNER JOIN prestamo pre ON pre.pre_id = pag.pre_id '
        . 'WHERE pd.pd_est = 1 AND pre.pre_id = :pre_id';
      $stmt = $cn->prepare($sql);
      $stmt->bindParam(':pre_id', $param_pre_id, PDO::PARAM_INT);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $cnn->close();
      echo json_encode($row);
    }
  } catch (PDOException $e) {
    echo '{"error" : {"text":' . $e->getMessage() . '}';
  }
});
