<?php
namespace common\components\websocket\events;

class WSClientMessageEvent extends WSClientEvent
{
    /**
     * @var string $message
     */
    public $message;
}