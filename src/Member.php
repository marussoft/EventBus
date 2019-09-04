<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Member
{
    // Имя участника
    private $name;
    
    // Слой
    private $layer;
    
    // Класс обработчика задач
    private $handler;
    
    // Тип участника
    private $type;

    public function __construct(SubscribeManager $subscribeManager)
    {
        $this->subscribeManager = $subscribeManager;
    }

    // Создает новую подписку на событие Layer.Name.Event // $conditions
    public function subscribe(string $subject, array $conditions = []) : self
    {
        $this->subscribeManager->createSubscribe($subject, $this->layer . '.' . $this->name, $action, $conditions);
        
        return $this;
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
    
    public function getSubscribes()
    {
    
    }
    
    public function createTask($action, $conditions = [])
    {
        return new Task($this->name, $this->type, $action, $this->layer, $conditions, $this->handler);
    }
    
    public function name() : string
    {
        return $this->name;
    }
    
    public function layer() : string
    {
        return $this->layer;
    }
    
    public function type() : string
    {
        return $this->type;
    }
    
}
