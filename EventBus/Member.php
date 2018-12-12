<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Member
{
    // Имя участника
    private $name;
    
    // Слой
    private $layer;
    
    // Имя класса обработчика задач
    private $handle;
    
    // Тип участника
    private $type;
    
    // Массив подписок
    private $subscribe;

    public function __construct(string $type, string $name, string $layer, string $handle = '')
    {
        $this->name = $name;
        $this->layer = $layer;
        $this->type = $type;
        $this->handle = $handle;
    }

    // Создает новую подписку на событие Type.Name.Event
    public function subscribe(string $subject, string $action, array $conditions = [])
    {
        $this->subscribe[$subject] = $this->createTask($action, $conditions);
        
        return $this;
    }
    
    // Возвращает задачи для события
    public function getTask(string $subject, string $event)
    {
        if (isset($this->subscribe[$subject . '.' . $event])) {
            return $this->subscribe[$subject . '.' . $event];
        }
    }
    
    public function name()
    {
        return $this->name;
    }
    
    public function layer()
    {
        return $this->layer;
    }
    
    public function type()
    {
        return $this->type;
    }
    
    // Создает задачу
    private function createTask(string $action, array $conditions = [])
    {
        $task = new Task($this->name, $action, $this->layer, $conditions, $this->handle);

        return $task;
    }
    
}
