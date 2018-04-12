<?php
namespace common\components\websocket\events;

class WSClientCommandEvent extends WSClientEvent
{
    /**
     * @var string $command
     */
    public $command;

    /**
     * @var mixed $result
     */
    public $result;
}