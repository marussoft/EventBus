<?php

namespace Marussia\Components\EventBus;

class Member
{
    private $name;
    private $layer;
    private $handle;
    private $subscribe;
    private $conditions;

    public function __construct(string $type, string $name, string $layer)
    {
        $this->name = $name;
        $this->layer = $layer;
        $this->type = $type;
    }

    // Создает новую подписку на событие
    public function subscribe(string $subject, string $event, string $action, array $conditions = [])
    {
        $task = new stdClass();
        $task->name = $this->name;
        $task->action = $this->subscribe[$event];
        $task->conditions = $conditions;
        $task->layer = $this->layer;
        $task->handle = $this->handle;

        $this->subscribe[$subject . $event] = $task;
        
        return $this;
    }
    
    // Возвращает задачи для события
    public function getTask(string $subject, string $event)
    {
        if (isset($this->subscribe[$subject . $event])) {
            return $this->subscribe[$subject . $event];
        }
    }
    
    public function createTask(string $action)
    {
        $task = new Task;
        $task->name = $this->name;
        $task->action = $action;
        $task->conditions = [];
        $task->layer = $this->layer;
        $task->handle = $this->handle;
        return $task;
    }
    
    // Устанавливает обработчик для участника
    public function handle(string $handle)
    {
        $this->handle = $handle;
        
        return $this;
    }
    
}
