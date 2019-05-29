<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Менеджер подписок
    private $threadManager;
    
    // Менеджер слоев
    private $layerManager;
    
    // Фабрика событий
    private $factory;
    
    private $currentMember;
    
    private $currentTask;
    
    private $currentTaskData;
    
    public function __construct(Repository $repository, ThreadManager $thread_manager, EventFactory $factory, LayerManager $layer_manager)
    {
        $this->repository = $repository;
        
        $this->threadManager = $thread_manager;
        
        $this->layerManager = $layer_manager;
        
        $this->factory = $factory;
    }
    
    public function command(string $member, string $action, $data = null)
    {
        // Будет проверка на слои и передача данных в таск
        $task = $this->repository->getMember($member)->createTask($action);
        $threadManager->addTask($task);
    }
    
    // Подключает нить // Начало, доделать
    public function dispatchThread(string $member, string $task, $data)
    {

        $this->eventFactory->create($result->subject, $result->event, $result->eventData);
        
        $this->currentMember = $member;
        $this->currentTask = $task;
        $this->currentTaskData = $data;
    }
    
    // Обрабатывает результат текущего таска
    public function resolveResult(ResultInterface $result)
    {
        $event = $this->eventFactory->create($result->subject, $result->event, $result->eventData);
        
        // Исключение здесь
        $this->dispatch($event);
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
}
