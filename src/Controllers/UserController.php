<?php

namespace YezBot\Controllers;

use YezBot\Controller;
use YezBot\Exceptions\AppException;
use YezBot\Models\Users;

/**
 * Class UserController
 *
 * @package YezBot\Controllers
 */
class UserController extends Controller
{
    /**
     * @param $channel
     * @throws AppException
     */
    public function getAction(string $channel)
    {
        $user = $this->get($channel);

        if (!$user) {
            throw new AppException("Invalid channel, User not fount", 400);
        }

        $this->jsonResponse($user->toArray());
    }

    /**
     * @param string $channel
     * @return Users
     */
    protected function get(string $channel)
    {
        return Users::findFirstByChannel($channel);
    }

    /**
     * @param $channel
     * @return Users
     * @throws AppException
     */
    protected function add($channel)
    {
        $name = $channel->display_name;
        if (empty($name)) {
            $name = $channel->name;
        }

        $user = new Users();
        $user->name = $name;
        $user->channel_id = $channel->_id;
        $user->channel = $channel->name;

        if (!$user->save()) {
            $messages = $user->getMessages();

            throw new AppException(implode(" ", $messages), 400);
        }

        return $user;
    }
}
