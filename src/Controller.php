<?php

namespace YezBot;

use GuzzleHttp\Client;
use Phalcon\Config;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller as PhalconController;
use Phalcon\Session\Adapter\Files as Session;

/**
 * Class Controller
 *
 * @package YezBot
 * @property Config   $config
 * @property Request  $request
 * @property Response $response
 * @property Session  $session
 * @property Client   $client
 */
class Controller extends PhalconController
{
    public function jsonResponse($data, $code = 200)
    {
        $this->response->setStatusCode($code);
        $this->response->setJsonContent($data);
    }

    public function sendRequest($method, $url, $options)
    {
        $res = $this->client->request($method, $url, $options);
        $json = $res->getBody()->getContents();

        return json_decode($json);
    }
}
