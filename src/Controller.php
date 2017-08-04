<?php

namespace YezBot;

use GuzzleHttp\Client;
use Phalcon\Config;
use Phalcon\Crypt;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller as PhalconController;
use Phalcon\Session\Adapter\Files as Session;

/**
 * Class Controller
 *
 * @package YezBot
 * @property Config   $config
 * @property Crypt    $crypt
 * @property Client   $client
 * @property Request  $request
 * @property Response $response
 * @property Session  $session
 */
class Controller extends PhalconController
{
    /**
     * @param array  $data
     * @param int    $code
     * @param string $status
     */
    public function jsonResponse(array $data, int $code = 200, string $status = 'Ok')
    {
        $this->response->setStatusCode($code, $status);
        $this->response->setJsonContent($data);
        $this->response->send();
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     * @return array
     */
    public function sendRequest($method, $url, $options)
    {
        $res = $this->client->request($method, $url, $options);
        $json = $res->getBody()->getContents();

        return json_decode($json);
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->session->set('token', $this->crypt->encrypt($token));
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $token = $this->session->get('token');

        return $this->crypt->decrypt($token);
    }
}
