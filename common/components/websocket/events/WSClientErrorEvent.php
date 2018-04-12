<?php
namespace common\components\websocket\events;

class WSClientErrorEvent extends WSClientEvent
{
    /**
     * @var \Exception $exception
     */
    public $exception;
}