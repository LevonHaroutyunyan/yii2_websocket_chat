<?php

namespace common\components\websocket;

use common\models\Services;
use common\components\websocket\events\WSClientMessageEvent;
use Ratchet\ConnectionInterface;
use Yii;

class ServerCommands extends WebSocketServer
{
    protected $request;

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_CLIENT_MESSAGE, function (WSClientMessageEvent $e) {
            $e->client->send( $e->message );
        });
    }

    /**
     * override method getCommand( ... )
     *
     * For example, we think that all user's message is a command
     */
    protected function getCommand(ConnectionInterface $from, $msg)
    {
        $this->request = json_decode($msg, true);
        return !empty($this->request['action']) ? $this->request['action'] : parent::getCommand($from, $msg);
    }

    /**
     * @param ConnectionInterface $client
     * @param $msg
     */
    public  function commandChat(ConnectionInterface $client, $msg)
    {
        foreach ($this->clients as $chatClient) {
            if ($chatClient != $client) {
                $chatClient->send(json_encode([
                    'message' => $this->request['message']
                ]));
            }
        }
    }
}