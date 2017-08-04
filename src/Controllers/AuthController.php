<?php

namespace YezBot\Controllers;

use YezBot\Controller;
use YezBot\Exceptions\AppException;

/**
 * Class AuthController
 *
 * @package YezBot\Controllers
 */
class AuthController extends Controller
{
    /**
     *
     */
    public function indexAction()
    {
        $token = $this->getTwitchToken();
        $channel = $this->getChannel($token);

        if (!$this->getUser($channel->name)) {
            $this->setUser($channel);
        }

        $this->setToken($token);

        $this->jsonResponse([]);
    }

    /**
     * @return mixed
     * @throws AppException
     */
    private function getTwitchToken()
    {
        $code = $this->request->get('code');
        if (!$code) {
            throw new AppException("Missing authorization code", 400);
        }

        $state = $this->crypt->encryptBase64(microtime());

        $data = $this->sendRequest('POST', 'oauth2/token', [
            'query' => [
                'client_id'     => $this->config->twitch->client_id,
                'client_secret' => $this->config->twitch->client_secret,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->config->twitch->redirect_uri,
                'code'          => $code,
                'state'         => $state,
            ],
        ]);

        var_dump($data);

        /*
        if ($state != $data['state']) {
            throw new JsonException("Invalid state", 400);
        }
        */

        return $data->access_token;
    }

    private function getChannel($token)
    {
        $data = $this->sendRequest('GET', 'channel', [
            'headers' => [
                'Accept'        => "application/vnd.twitchtv.v5+json",
                'Client-ID'     => $this->config->client->client_id,
                'Authorization' => "OAuth $token",
            ],
        ]);

        return $data;
    }

}
