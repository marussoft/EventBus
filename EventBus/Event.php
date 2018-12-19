<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Event
{
    private $subject;
    
    private $eventName;
    
    private $data;

    public function __construct(string $subject, string $event, $data = null)
    {
        $this->subject = $subject;
        $this->eventName = $event;
        $this->data = $data;
    }
    
    public function subject()
    {
        return $this->subject;
    }
    
    public function eventName()
    {
        return $this->eventName;
    }
    
    public function eventData()
    {
        return $this->data;
    }
}
