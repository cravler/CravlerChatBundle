<?php

namespace Cravler\ChatBundle\Endpoint;

use Cravler\RemoteBundle\Security\Token;
use Cravler\RemoteBundle\Proxy\RemoteProxy;
use Cravler\ChatBundle\Storage\UserStorage;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Chat
{
    /**
     * @var UserStorage
     */
    private $userStorage;

    /**
     * @param UserStorage $userStorage
     */
    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * @param string $name
     * @param $cb
     * @param Token $token
     * @param RemoteProxy $remote
     */
    public function changeName($name = '', $cb = null, Token $token, RemoteProxy $remote)
    {
        $resp = false;
        $oldName = '';
        if ($name && !$this->userStorage->exists($name)) {
            $remoteKey = $token->getRemoteKey();
            if (isset($remoteKey['session']) && $remoteKey['session']) {
                $resp = true;
                $oldName = $this->userStorage->getName($remoteKey['session']);
                $this->userStorage->add($remoteKey['session'], $name);
            }
        }

        if (is_callable($cb)) {
            $cb($resp);
        }

        if ($resp) {
            $remote->dispatch(array(
                'type' => 'cravler_chat.chat.user',
                'name' => 'name_changed',
                'data' => array(
                    'oldName' => $oldName,
                    'newName' => $name,
                ),
            ));
        }
    }

    /**
     * @param string $message
     * @param $cb
     * @param Token $token
     * @param RemoteProxy $remote
     */
    public function sendMessage($message = '', $cb = null, Token $token, RemoteProxy $remote)
    {
        $remoteKey = $token->getRemoteKey();
        $name  = $this->userStorage->getName($remoteKey['session']);

        $resp = false;
        if ($name) {
            $resp = true;
        }

        if (is_callable($cb)) {
            $cb($resp);
        }

        if ($resp) {
            $event = array(
                'type' => 'cravler_chat.chat.message',
                'name' => 'added',
                'data' => array(
                    'user' => $name,
                    'text' => $message,
                ),
            );
            
            $rooms = array();
            if (preg_match("/\B\@([\w\-]+)/im", $message, $matches)) {
                $id = $this->userStorage->getId($matches[1]);
                if ($id) {
                    $rooms[] = 'cravler_chat.room.user_' . $id;
                    $rooms[] = 'cravler_chat.room.user_' . $remoteKey['session'];
                }
            }
            
            if (count($rooms)) {
                foreach($rooms as $room) {
                    $remote->dispatch($room, $event);
                }
            } else {
                $remote->dispatch($event);
            }
        }
    }
}
