<?php

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Task;

class TaskQueue extends \SplQueue
{
    public function taskIsExist(Task $task)
    {
        return $this->offsetExists($task->layer . '.' . $task->memberName . '' . $task->action);
    }
    
    public function addTask(Task $task) : void
    {
        $offset = $task->layer . '.' . $task->memberName . '' . $task->action;
    
        if ($this->offsetExists($offset)) {
            $this->offsetSet($offset, $task);
        } else {
            $this->add($offset, $task);
        }
    }
}

