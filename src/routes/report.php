<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Report
$app->get('/v1/report/total', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_startDate = $request->getParam('startDate');
    $param_endDate = $request->getParam('endDate');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT IFNULL(COUNT(pd.pd_id), 0) AS count, IFNULL(SUM(pd.pd_mon), 0.00) AS sum, '
                . 'IFNULL(ROUND(SUM(pd.pd_mon - (pd.pd_mon * (pag.pag_mon_tot - pre.pre_mon) / pag.pag_mon_tot)), 2), 0.00) AS sum_cap, '
                . 'IFNULL(ROUND(SUM(pd.pd_mon * (pag.pag_mon_tot - pre.pre_mon) / pag.pag_mon_tot), 2), 0.00) AS sum_int '
                . 'FROM pago_detalle pd '
                . 'INNER JOIN pago pag ON pag.pag_id = pd.pag_id '
                . 'INNER JOIN prestamo pre ON pre.pre_id = pag.pre_id '
                . 'WHERE pd.pd_est = 1 AND CAST(pd.pd_fec_reg AS DATE) BETWEEN :startDate AND :endDate';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':startDate', $param_startDate, PDO::PARAM_STR);
            $stmt->bindParam(':endDate', $param_endDate, PDO::PARAM_STR);
            $stmt->execute();
            $reportData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"reportData": ' . json_encode($reportData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Report Detail
$app->get('/v1/report/detail', function (Request $request, Response $response) {
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_startDate = $request->getParam('startDate');
    $param_endDate = $request->getParam('endDate');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT CAST(pd.pd_fec_reg AS DATE) AS fecha, IFNULL(COUNT(pd.pd_id), 0) AS count, IFNULL(SUM(pd.pd_mon), 0.00) AS sum, '
                . 'IFNULL(ROUND(SUM(pd.pd_mon - (pd.pd_mon * (pag.pag_mon_tot - pre.pre_mon) / pag.pag_mon_tot)), 2), 0.00) AS sum_cap, '
                . 'IFNULL(ROUND(SUM(pd.pd_mon * (pag.pag_mon_tot - pre.pre_mon) / pag.pag_mon_tot), 2), 0.00) AS sum_int '
                . 'FROM pago_detalle pd '
                . 'INNER JOIN pago pag ON pag.pag_id = pd.pag_id '
                . 'INNER JOIN prestamo pre ON pre.pre_id = pag.pre_id '
                . 'WHERE pd.pd_est = 1 AND CAST(pd.pd_fec_reg AS DATE) BETWEEN :startDate AND :endDate '
                . 'GROUP BY CAST(pd.pd_fec_reg AS DATE) '
                . 'ORDER BY CAST(pd.pd_fec_reg AS DATE) DESC';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':startDate', $param_startDate, PDO::PARAM_STR);
            $stmt->bindParam(':endDate', $param_endDate, PDO::PARAM_STR);
            $stmt->execute();
            $reportData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"reportData": ' . json_encode($reportData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Collected
$app->get('/v1/report/ctotal', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_rangeDate = $request->getParam('rangeDate');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT IFNULL(COUNT(pd.pd_id), 0) AS count, IFNULL(SUM(pd.pd_mon), 0.00) AS sum '
                . 'FROM pago_detalle pd '
                . 'WHERE pd.pd_est = 1 AND CAST(pd.pd_fec_reg AS DATE) = :rangeDate';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
            $stmt->execute();
            $reportData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"reportData": ' . json_encode($reportData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});

//Collected Detail
$app->get('/v1/report/cdetail', function (Request $request, Response $response) {
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_rangeDate = $request->getParam('rangeDate');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT CAST(pd.pd_fec_reg AS DATE) AS fecha, IFNULL(pd.pd_mon, 0.00) AS pd_mon, '
                . 'CONCAT(cli.cli_nom, \' \', cli.cli_ape) AS cli_nom '
                . 'FROM pago_detalle pd '
                . 'INNER JOIN pago pag ON pag.pag_id = pd.pag_id '
                . 'INNER JOIN prestamo pre ON pre.pre_id = pag.pre_id '
                . 'INNER JOIN cliente cli ON pre.cli_id = cli.cli_id '
                . 'WHERE pd.pd_est = 1 AND CAST(pd.pd_fec_reg AS DATE) = :rangeDate '
                . 'ORDER BY CAST(pd.pd_fec_reg AS DATE) DESC';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':rangeDate', $param_rangeDate, PDO::PARAM_STR);
            $stmt->execute();
            $reportData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"reportData": ' . json_encode($reportData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});
