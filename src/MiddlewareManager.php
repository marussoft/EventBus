<?php

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Task;

class MiddlewareManager
{
    private $dispatcher;
    
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function isAccepted(Task $task) : bool
    {
        return true;
    }
}
