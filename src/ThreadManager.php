<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class TheadManager
{
    private $bus;

    // Менеджер подписок
    private $subscribeManager;
    
    private $thread;
    
    public function __construct(Bus $bus, SubscribeManager $subscribe_manager)
    {
        $this->bus = $bus;
        $this->subscribeManager = $subscribe_manager;
    }
    
    // Сохранять текущую если она не в нити
    // Происходит в некой задаче. В корневой или в нити
    // Находится ли оно в задаче. Если это нить, то метод выполняется внутри newThread
    // Нужно создавать новый объект Bus
    // Куда диспатчим?
    public function dispatchEvent(Event $event, array $members)
    {
        // Создаем задачи
        $tasks = $this->subscribeManager->createTasks($event, $members);
        
        // $bus->runned() ?
        
        foreach ($tasks as $task) {
            // текущая или нет. сохранить ы
            $this->bus->addTask($event, $task);
        }
    }

    // Текущая задача еще не выполнена. Ожидает. Нужно создать таск в новой нити
    public function newThread(// started_service.action.even_return_data)
    {
        
        return // возврат данных по новой нити в сервис который запросил. Только тогда продолжится выполение корневой задачи
    }
}
