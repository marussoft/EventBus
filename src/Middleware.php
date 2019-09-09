<?php

declare(srtict_types=1);

namespace Marussia\EventBus;

class Middleware
{
    private $dispatcher;
    
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function getResultData(string $event)
    {
        return $this->dispatcher->getTaskData($event);
    }
}
