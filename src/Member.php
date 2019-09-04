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

    public function __construct(SubscribeManager $subscribeManager)
    {
        $this->subscribeManager = $subscribeManager;
    }

    // Создает новую подписку на действие Layer.Name.Action.Status
    public function subscribe(string $subject, string $action, array $conditions = []) : self
    {
        $this->subscribeManager->createSubscribe($subject, $this->layer . '.' . $this->name, $action, $conditions);
        
        return $this;
    }
    
    public function name() : string
    {
        return $this->name;
    }
    
    public function layer() : string
    {
        return $this->layer;
    }
    
}
