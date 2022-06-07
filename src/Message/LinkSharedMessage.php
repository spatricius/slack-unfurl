<?php

namespace App\Message;

class LinkSharedMessage
{
    protected object $eventObject;

    public function __construct(object $eventObject)
    {
        $this->eventObject = $eventObject;
    }

    public function getEventObject()
    {
        return $this->eventObject;
    }
}