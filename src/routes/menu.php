<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//$app = new \Slim\App;
//Menu Lateral
$app->get('/v1/menu/all', function (Request $request, Response $response) {
    // $data = json_decode($request->getBody());
    $param_usu_id = $request->getParam('usu_id');
    $param_token = $request->getParam('token');
    $param_et_id = $request->getParam('et_id');
    $param_menu = $request->getParam('menu');
    try {
        $cnn = new Connection();
        $token = $cnn->apiKey($param_usu_id);

        if ($param_token == $token) {
            $cn = $cnn->open();
            $sql = 'SELECT m.title, m.icon, m.component '
                . 'FROM rol r '
                . 'LEFT JOIN menu m ON m.menu_id = r.menu_id '
                . 'WHERE m.menu_est = 1 AND m.menu_tipo = :menu_tipo AND r.et_id = :et_id '
                . 'ORDER BY m.menu_id ';
            $stmt = $cn->prepare($sql);
            $stmt->bindParam(':menu_tipo', $param_menu, PDO::PARAM_INT);
            $stmt->bindParam(':et_id', $param_et_id, PDO::PARAM_INT);
            $stmt->execute();
            $menuData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $cnn->close();
            echo '{"menuData": ' . json_encode($menuData) . '}';
        }
    } catch (PDOException $e) {
        echo '{"error" : {"text":' . $e->getMessage() . '}';
    }
});
