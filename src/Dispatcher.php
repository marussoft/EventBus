<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Менеджер слоев
    private $layerManager;
    
    // Фабрика событий
    private $factory;
    
    private $currentMember;
    
    private $currentTask;
    
    private $currentAction;
    
    private $config;
    
    private $fileResource;
    
    public function __construct(
        Repository $repository, 
        EventFactory $factory, 
        LayerManager $layerManager, 
        Loop $loop, 
        ConfigProvider 
        $config, 
        FileResource $fileResource
    )
    {
        $this->repository = $repository;
        $this->loop = $loop;
        $this->layerManager = $layerManager;
        $this->factory = $factory;
        $this->config = $config;
        $this->fileResource = $fileResource;
    }
    
    // Вызывается из фасада Bus
    public function startLoop($data = null)
    {
        $this->currentAction = $this->config->getStartedAction();

        $this->fileResource->plugLayer($this->config->getStartedLayer());
        
        $this->currentMember = $this->repository->getMember($this->config->getStartedMember());
    
        $this->currentTask = $this->taskManager->createTask($this->currentMember, $this->currentAction);
        
        $this->loop->addTask($this->currentTask);
        
        $this->process();
    }
    
    // Обрабатывает результат текущего таска // Начало
    public function dispatchResult(ResultInterface $result, Task $currentTask)
    {
        $event = $this->eventFactory->create($result, $this->currentAction, $this->currentMember);
        
        $this->eventStorage->add($event); // зависим от EventStorage
        
        $this->currentTask = $currentTask;
        
        // Получаем допустимые слои для события
        $accessLayers = $this->layerManager->getAccessLayers($this->currentMember->layer); // ассоциативный по слоям
        
        $subscribes = $this->subscribeManager->getSubscribers($this->currentMember, $this->currentAction); // мвссив (содержат conditions)
        
        // Получаем подписчиков
        $subscribers = $this->subscribeManager->getSubscribers($event->subject);

        $tasks = [];
        
        foreach ($subscribers as $subscriber) {
            if (!isset($accessLayers[$subscriber->layer])) {
                // Исключение
            }
            $tasks[] = $this->taskManager->createTask($this->repository->getMember($subscriber->member), $subscriber->member->action);
        }
        
        foreach($tasks as $task) {
            $this->loop->addTask($this->$task);
        }
        
    }
    
    
    
    
    
    
    
    
    // Принимает новое событие. Нить неизвестна // Первая задача // Вторая задача новая ветка внутри newThread
    private function dispatch(Event $event) : void
    {
        // Фильтруем событие по слою
        $access_layers = $this->layerManager->getAccessLayers($event);
        
        // Получаем участников из допустимых слоёв
        $members = $this->repository->getMembersByLayers($access_layers);
        
        // Создаем задачи
        $tasks = $this->subscribeManager->createTasks($event, $members);

        foreach ($tasks as $task) {
            $this->threadManager->addTask($event, $task);
        }
    }
    
    private function process()
    {

    }
}
