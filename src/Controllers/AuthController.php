<?php

namespace YezBot\Controllers;

use YezBot\Controller;

class AuthController extends Controller
{
    public function index()
    {
        $token = $this->getToken();
        //$channel = $this->getChannel($token);

        /*
        if (!$user = $this->getUser($channel->name)) {
            $this->setUser($channel);

            $user = $this->getUser($channel->name);
        }

        $this->setToken($token, $user->id);

        return response()->json([
            'hash' => Hash::make(env('APP_KEY') . $token),
            'user' => $user
        ]);*/
    }


    private function getToken()
    {
        $code = $this->request->get('code');
        if (!$code) {
            throw new \Exception("Missing authorization code", 400);
        }

        $state = substr(sha1(microtime()), 0, 21);

        $data = $this->sendRequest('POST', 'oauth2/token', [
            'query' => [
                'client_id'     => env('CLIENT_ID'),
                'client_secret' => env('CLIENT_SECRET'),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => env('REDIRECT_URI'),
                'code'          => $code,
                'state'         => $state,
            ],
        ]);

        /*
        //var_dump($data);
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
                'Client-ID'     => env('CLIENT_ID'),
                'Authorization' => "OAuth $token",
            ],
        ]);

        return $data;
    }


    private function setToken($token, $user_id)
    {
        DB::insert('INSERT INTO token (token, user_id) VALUES (?, ?, )', [
            $token,
            $user_id,
        ]);
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
