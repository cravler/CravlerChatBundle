<?php

namespace Cravler\ChatBundle\Connection;

use Cravler\RemoteBundle\Connection\ConnectionHandlerInterface;
use Cravler\RemoteBundle\Security\Token;
use Cravler\RemoteBundle\Proxy\RemoteProxy;
use Cravler\ChatBundle\Storage\UserStorage;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class ConnectionHandler implements ConnectionHandlerInterface
{
    /**
     * @var UserStorage
     */
    private $userStorage;

    /**
     * @var array
     */
    private $connections = array();

    /**
     * @param UserStorage $userStorage
     */
    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    /**
     * @param $type
     * @param Token $token
     * @param RemoteProxy $remote
     */
    public function handle($type, Token $token, RemoteProxy $remote)
    {
        if (ConnectionHandlerInterface::TYPE_CONNECT == $type) {
            $this->onConnect($token, $remote);
        } else if (ConnectionHandlerInterface::TYPE_DISCONNECT == $type) {
            $this->onDisconnect($token, $remote);
        }
    }

    /**
     * @param Token $token
     * @param RemoteProxy $remote
     */
    private function onConnect(Token $token, RemoteProxy $remote)
    {
        $remoteKey = $token->getRemoteKey();
        if (isset($remoteKey['session']) && $remoteKey['session']) {

            if (!isset($this->connections[$remoteKey['session']]) || $this->connections[$remoteKey['session']] < 0) {
                $this->connections[$remoteKey['session']] = 0;
            }
            $this->connections[$remoteKey['session']] += 1;

            $joined = false;
            $name = $this->userStorage->getName($remoteKey['session']);
            if (!$name) {
                $joined = true;
                $name  = $this->userStorage->uniqid('Guest');
                $this->userStorage->add($remoteKey['session'], $name);
            }
            $users = $this->userStorage->getNames(array($name));

            $remote->joinRoom($remoteKey, 'cravler_chat.room.user_' . $remoteKey['session']);

            $remote->dispatch('cravler_chat.room.user_' . $remoteKey['session'], array(
                'type' => 'cravler_chat.chat.user',
                'name' => 'init',
                'data' => array(
                    'name'  => $name,
                    'users' => $users,
                ),
            ));

            if ($joined) {
                $remote->dispatch(array(
                    'type' => 'cravler_chat.chat.user',
                    'name' => 'join',
                    'data' => array(
                        'name' => $name,
                    ),
                ));
            }

        }
    }

    /**
     * @param Token $token
     * @param RemoteProxy $remote
     */
    private function onDisconnect(Token $token, RemoteProxy $remote)
    {
        $remoteKey = $token->getRemoteKey();
        if (isset($remoteKey['session']) && $remoteKey['session']) {

            if (!isset($this->connections[$remoteKey['session']])) {
                $this->connections[$remoteKey['session']] = 0;
            }
            $this->connections[$remoteKey['session']] -= 1;

            $name = $this->userStorage->getName($remoteKey['session']);

            $self = $this;
            $remote->wait(function() use ($self, $remoteKey, $remote, $name) {
                if (isset($self->connections[$remoteKey['session']]) && $self->connections[$remoteKey['session']] < 1) {
                    unset($self->connections[$remoteKey['session']]);
                    $self->userStorage->remove($remoteKey['session']);

                    $remote->dispatch(array(
                        'type' => 'cravler_chat.chat.user',
                        'name' => 'left',
                        'data' => array(
                            'name' => $name,
                        ),
                    ));
                }
            }, 2);
        }
    }
}
