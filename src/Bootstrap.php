<?php

namespace YezBot;

use GuzzleHttp\Client;
use Phalcon\Config\Adapter\Php as PhpConfig;
use Phalcon\Crypt;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Session\Adapter\Files as Session;
use YezBot\Controllers\AuthController;
use YezBot\Controllers\UserController;
use YezBot\Exceptions\ExceptionRender;
use YezBot\Responses\AppResponse;

/**
 * Class Bootstrap
 *
 * @package YezBot
 */
class Bootstrap
{
    public $di;

    public function __construct()
    {
        $this->di = new FactoryDefault();
        $this->setConfig();
        $this->setServices();
    }

    public function run()
    {
        $app = new Micro();
        $app->setDI($this->di);
        $this->setRoutes($app);
        $app->handle();
    }

    public function setConfig()
    {
        $this->di->setShared('config', new PhpConfig('../config/config.php'));
    }

    public function setServices()
    {
        $config = $this->di->get('config');

        $this->di->setShared('db', function () use ($config) {
            return new Mysql([
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname,
            ]);
        });

        $this->di->setShared('session', function () {
            $session = new Session();
            $session->start();

            return $session;
        });

        $this->di->setShared('crypt', function () use ($config) {
            $config = $this->di->get('config');

            $crypt = new Crypt();
            $crypt->setKey($config->key);

            return $crypt;
        });

        $this->di->setShared('client', function () {
            return new Client([
                'base_uri' => 'https://api.twitch.tv/kraken/',
                'headers'  => [
                    'Accept' => 'application/vnd.twitchtv.v5+json',
                ],
            ]);
        });
    }

    /**
     * @param $app Micro
     */
    public function setRoutes($app)
    {
        $app->get('/', function () {
            echo "test";
        });

        $app->get('/auth', [
            new AuthController(),
            'indexAction',
        ]);

        $users = new Collection();
        $users->setHandler(new UserController());
        $users->setPrefix('/users');
        $users->get('/{channel}', 'getAction');
        $users->put('/', 'addAction');
        $app->mount($users);

        $app->error(function (\Exception $exception) {
            $render = new ExceptionRender($exception);

            return $render->render();
        });

        $app->notFound(function () {
            return AppResponse::response([
                'error'             => "not_found",
                'error_description' => "Route not found",
            ], 404);
        });
    }
}
