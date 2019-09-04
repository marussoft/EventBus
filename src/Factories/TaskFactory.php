<?php

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Task;

class TaskFactory
{
    private $config;
    
    public function __construct(ConfigProvider $config)
    {
        $this->config = $config;
    }
    
    public function createTask(string $memberWithLayer, string $action, $data = null) : Task
    {
        $task = new Task();
    }
}

