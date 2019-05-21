<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class TheadManager
{
    // Менеджер подписок
    private $subscribeManager;
    
    private $threadFactory;
    
    private $thread;
    
    public function __construct(Thread $thread, SubscribeManager $subscribe_manager, ThreadFactory $thread_factory)
    {
        $this->thread = $thread;
        $this->subscribeManager = $subscribe_manager;
        $this->threadFactory = $thread_factory;
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
            $this->thread->addTask($event, $task);
        }
    }

    public function dispatchTask($task)
    {
        $this->thread->addTask();
    }
    
    // Текущая задача еще не выполнена. Ожидает. Нужно создать таск в новой нити
    public function newThread(// started_service.action.even_return_data)
    {
        
        return // возврат данных по новой нити в сервис который запросил. Только тогда продолжится выполение корневой задачи
    }
    
    public function run()
    {
        $this->thread->run();
    }
}
