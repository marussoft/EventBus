<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Member
{
    // Имя участника
    private $name;
    
    // Слой
    private $layer;
    
    // Имя класса обработчика задач
    private $handler;
    
    // Тип участника
    private $type;
    
    // Массив подписок
    private $subscribe;

    public function __construct(string $type, string $name, string $layer, string $handler = '')
    {
        $this->name = $name;
        $this->layer = $layer;
        $this->type = $type;
        $this->handler = $handler;
    }

    // Создает новую подписку на событие Type.Name.Event
    public function subscribe(string $subject, string $action, array $conditions = [])
    {
        $this->subscribe[$subject][$action] = $conditions;
        
        return $this;
    }
    
    // Прверяет подписан ли участник на событие
    public function isSubscribed(string $subject, string $event)
    {
        return isset($this->subscribe[$subject . '.' . $event]);
    }
    
    // Возвращает задачи для события
    public function getTasks(string $subject, string $event, $data = null)
    {
        $subscribe = $this->subscribe[$subject . '.' . $event];
    
        foreach ($subscribe as $action => $conditions) {
            $tasks[$action] = $this->createTask($action, $conditions);
            $tasks[$action]->setData($data);
        }
        
        return $tasks;
    }
    
    public function createTask($action, $conditions = [])
    {
        $task = new Task($this->name, $this->type, $action, $this->layer, $conditions, $this->handler);

        return $task;
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
    
}
