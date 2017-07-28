<?php

namespace YezBot\Controllers;

use YezBot\Controller;
use YezBot\Exceptions\AppException;

class AuthController extends Controller
{
    /**
     *
     */
    public function index()
    {
        $token = $this->getToken();
        $channel = $this->getChannel($token);

        if (!$this->getUser($channel->name)) {
            $this->setUser($channel);
        }

        $this->setToken($token);

        $this->jsonResponse([
            'token' => sha1($this->config->key . $token),
        ]);
    }

    /**
     * @return mixed
     * @throws AppException
     */
    private function getToken()
    {
        $code = $this->request->get('code');
        if (!$code) {
            throw new AppException("Missing authorization code", 400);
        }

        $state = substr(sha1(microtime()), 0, 21);

        $data = $this->sendRequest('POST', 'oauth2/token', [
            'query' => [
                'client_id'     => $this->config->client->client_id,
                'client_secret' => $this->config->client->client_secret,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->config->client->redirect_uri,
                'code'          => $code,
                'state'         => $state,
            ],
        ]);

        //var_dump($data);

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


    private function setToken($token)
    {
        $this->session->set('token', $token);
    }

    private function getUser($channel)
    {
        return DB::selectOne('SELECT * FROM users WHERE channel = ?', [
            $channel,
        ]);
    }

    private function setUser($channel)
    {
        $name = $channel->display_name;
        if (empty($name)) {
            $name = $channel->name;
        }

        DB::insert('INSERT INTO users (channel_id, channel, name, email, created_at, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)', [
            $channel->_id,
            $channel->name,
            $name,
            $channel->email,
        ]);

        return DB::getPdo()->lastInsertId();
    }
}
