<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Event
{
    private $subject;
    
    private $name;
    
    private $data;

    public function __construct(string $subject, string $event, $data = null)
    {
        $this->subject = $subject;
        $this->name = $event;
        $this->data = $data;
    }
    
    public function subject()
    {
        return $this->subject;
    }
    
    public function name()
    {
        return $this->name;
    }
    
    public function data()
    {
        return $this->data;
    }
}
