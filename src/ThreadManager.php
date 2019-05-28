<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class TheadManager
{
    // Менеджер подписок
    private $subscribeManager;
    
    private $threadFactory;
    
    private $threadStorage;
    
    private $currentThreadId;
    
    private $returnPoint;
    
    private $returnData;
    
    private $threadOwner;
    
    private $runAction;
    
    public function __construct(ThreadStorage $thread_storage, SubscribeManager $subscribe_manager, ThreadFactory $thread_factory)
    {
        $this->threadStorage = $thread_storage;
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

    // Добавляет задачу в тукущую нить
    public function addTask(Task $task, string $event) : void
    {
        if ($this->returnPoint === $event->subject . '.' . $event->action) {
            $this->returnData = $event->data;
        }
        $this->threadStorage->get($this->currentThreadId)->addTask($task);
    }
    
    // Из фасада Bus. Текущая задача еще не выполнена. Ожидает. Нужно создать таск в новой нити
    public function newThread(string $member, string $action, string $return_point) : void
    {
        $current_thread_id = $this->currentThreadId ?? $member;
    
        // Создаем нить содержащую id текущей нити
        $thread = $threadFactory->create($member, $current_thread_id);
        
        // Помещаем нить в хранилище
        $this->threadStorage->register($thread);
        
        // Устанавливаем id текущей ветки
        $this->currentThreadId = $member;
        
        // Устанавливаем точку возврата данных
        $this->returnPoint = $return_point;
        
        // Устанавливаем владельца нити
        $this->threadOwner = $member;
        
        // Устанавливаем стартовое действие
        $this->runAction = $action;
    }
    
    public function run()
    {
        // Подключаем новую нить
        $this->dispatcher->dispatchNewThread($this->threadOwner, $this->runAction);
        
        // Запускаем нить
        $this->thread->run();
        
        // Восстанавливаем id родидельской нити
        $this->currentThreadId = $thread->parrentThreadId;

        // Возвращаем результат работы нити
        return $this->returnData;
    }
}
