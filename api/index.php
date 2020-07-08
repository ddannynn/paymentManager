<?php
//use \Psr\Http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/dbconection.php';

$config = ["settings" => [
	"displayErrorDetails" => true
]];

$app = new \Slim\App($config);

//Rutas
require '../src/routes/user.php';
require '../src/routes/client.php';
require '../src/routes/loan.php';
require '../src/routes/rate.php';
require '../src/routes/payment.php';
require '../src/routes/menu.php';
require '../src/routes/report.php';
require '../src/routes/ubigeo.php';

$app->run();

