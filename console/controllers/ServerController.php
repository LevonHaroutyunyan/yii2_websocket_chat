<?php

namespace console\controllers;

use common\components\websocket\ServerCommands;
use common\components\websocket\WebSocketServer;
use yii\console\Controller;
use Ratchet\Client;

class ServerController extends Controller
{
    public $message;
    public $user;

    protected $errors = [];
    protected $connection;
    protected $port = 8081;

    /**
     * Start websocket server for chat
     * @command "php yii server/start"
     */
    public function actionStart()
    {
        $server = new ServerCommands();
        $server->port = $this->port; //This port must be busy by WebServer and we handle an error

        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN_ERROR, function($e) use($server) {
            echo "Error opening port " . $server->port . "\n";
            $this->port += 1;
            $server->port = $this->port; //Try next port to open
            $server->start();
        });

        $server->on(WebSocketServer::EVENT_WEBSOCKET_OPEN, function($e) use($server) {
            echo "Server started at port " . $server->port;
        });

        $server->start();
    }


    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'user'
        ]);
    }

    /**
     * Displaying chat messages
     * @command "php yii server/display"
     */
    public function actionDisplay()
    {
        Client\connect('ws://wstest.loc:' . $this->port)->then(function ($conn) {
            $this->connection = $conn;
            $this->connection->on('message', function ($msg) use ($conn) {
                $message = json_decode($msg, true);
                echo $message['message'] . "\n";
            });
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }

    /**
     * Client chat connection
     * user option is required
     * @command "php yii server/chat --user=username"
     */
    public function actionChat()
    {
        if (empty($this->user)) {
            $this->errors[] = "User is required\n";
        }

        if (empty($this->errors)) {
            $this->stdout('message: ');
            $this->message = $this->stdin();
            $this->sendToChat();
        } else {
            echo implode('', $this->errors);
        }
    }

    /**
     * This method is sended new message to chat server from client
     */
    protected function sendToChat()
    {
        Client\connect('ws://wstest.loc:' . $this->port)->then(function ($conn) {
            $conn->on('message', function ($msg) use ($conn) {
                $conn->close();
                $this->actionChat();
            });

            $message = json_encode([
                'action' => 'chat',
                'message' => $this->user . ': ' . $this->message
            ]);

            $conn->send($message);
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }

    protected function stdin()
    {
        return \yii\helpers\BaseConsole::stdin();
    }
}
