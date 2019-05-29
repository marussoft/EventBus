<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class TheadManager
{
    // Менеджер подписок
    private $subscribeManager;
    
    private $threadFactory;
    
    private $threadStorage;
    
    private $currentThreadId = null;
    
    private $returnPoint;
    
    private $returnData;
    
    private $memberThreadOwner;
    
    private $runAction;
    
    public function __construct(ThreadStorage $thread_storage, SubscribeManager $subscribe_manager, ThreadFactory $thread_factory)
    {
        $this->threadStorage = $thread_storage;
        $this->subscribeManager = $subscribe_manager;
        $this->threadFactory = $thread_factory;
    }
    
    // Добавляет задачу в тукущую нить
    public function addTask(Task $task, string $event) : void
    {
        $this->threadStorage->get($this->currentThreadId)->addTask($task, $event);
    }
    
    // Из фасада Bus. Текущая задача еще не выполнена. Ожидает. Нужно создать таск в новой нити
    public function newThread(string $member, string $action, string $return_point = '', $data = null) : void
    {
        // Устанавливаем владельца нити
        $this->memberThreadOwner = $member;
        
        // Устанавливаем стартовый action
        $this->runAction = $action;
        
        // Присваеваем данные для таска
        $this->runData = $data;
        
        // Если null то это первичная нить
        $current_thread_id = $this->currentThreadId ?? $member;
    
        // Создаем нить содержащую id текущей нити
        $thread = $threadFactory->create($member, $current_thread_id, $return_point);
        
        // Устанавливаем id новой текущей ветки
        $this->currentThreadId = $member;
        
        // Помещаем нить в хранилище
        $this->threadStorage->register($thread);
    }
    
    // Запускает последнюю добавленную нить
    public function run()
    {
        // Подключаем новую нить
        $this->dispatcher->dispatchThread($this->memberThreadOwner, $this->runAction, $this->runData);
        
        // Запускаем нить
        $result = $this->thread->run();
        
        // Восстанавливаем id родидельской нити
        $this->currentThreadId = $thread->getParrentThreadId();

        // Возвращаем результат работы нити
        return $result;
    }
}
