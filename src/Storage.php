<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Storage
{
    private $events = [];

    public function register(Event $event)
    {
        $this->events[$event->subject() . '.' . $event->eventName()] = $event->eventData();
    }
    
    public function exists(array $data)
    {
        if (empty($data)) {
            return true;
        }
        
        foreach ($data as $subject => $event) {
            if (!array_key_exists($subject . '.' . $event, $this->events)) {
                return false;
            }
        }
        return true;
    }
    
    public function data(string $subject, string $event)
    {
        return $this->events[$subject . '.' . $event] ?? null;
    }

}
