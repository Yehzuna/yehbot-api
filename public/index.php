<?php

use Phalcon\Config\Adapter\Php as PhpConfig;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Mvc\Micro\Collection;
use GuzzleHttp\Client;

require_once '../vendor/autoload.php';

// Services
$di = new FactoryDefault();

$di->setShared('config', new PhpConfig('../config/config.php'));

$config = $di->get('config');

$di->setShared('db', function () use ($config) {
    return new Mysql([
        'host'     => $config->db->host,
        'username' => $config->db->username,
        'password' => $config->db->password,
        'dbname'   => $config->db->dbname,
    ]);
});

$di->setShared('session', function () {
    $session = new Session();
    $session->start();

    return $session;
});

$di->setShared('client', function () {
    return new Client([
        'base_uri' => 'https://api.twitch.tv/kraken/',
        //'headers'  => array('Accept' => 'application/vnd.twitchtv.v5+json')
    ]);
});

// Local server
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $_GET['_url'] = $_SERVER['REQUEST_URI'];
}


// Init the app
$app = new Micro();

$app->setDI($di);

// Routes
$app->get('/', function () {
    //var_dump($this->get('config'));

    //throw new Exception("Error", 500);

    /** @var Mysql $db */
    $db = $this->db;
    var_dump($db->query('SELECT * FROM users'));

    /** @var \Phalcon\Http\Request $request */
    $request = $this->request;
    var_dump($request->getURI());
});

$app->get('/auth', [
    new YezBot\Controllers\AuthController(),
    'index'
]);

$app->get('/users/{id}', function ($id) {
    var_dump($id);
});


$app->error(function (\Exception $exception) {
    if ($e instanceof JsonException) {
        return new JsonResponse([
            'error' => $e->getStatus(),
            'message' => $e->getMessage(),
        ], $e->getCode());
    }

    $response = new \Phalcon\Http\Response();
    $response->setStatusCode(500, 'Internal Server Error');
    $response->setJsonContent([
        'code'    => $exception->getCode(),
        'message' => $exception->getMessage(),
        'file'    => $exception->getFile(),
        'line'    => $exception->getLine(),
    ]);

    return $response;
});

$app->notFound(function () {
    $response = new \Phalcon\Http\Response();
    $response->setStatusCode(404, 'Not Found');
    $response->setJsonContent([
        'code'    => 404,
        'message' => "Route not found",
    ]);

    return $response;
});

$app->handle();
