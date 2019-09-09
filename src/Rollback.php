<?php

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\DependencyInjection\Container;
use Marussia\EventBus\Contracts\RollbackInterface;

class Rollback extends Container
{
    public function run(array $completeTasks)
    {
        foreach ($completeTasks as $task) {
            if (empty($task->rollback)) {
                continue;
            }
            $this->rollback($this->instance($task->rollback), $task->data);
        }
    }
    
    private function rollback(RollbackInterface $rollback, array $taskData)
    {
        call_user_func_array([$rollback, 'run'], $taskData);
    }
}

