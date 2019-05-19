<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class EventDispatcher
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
    
    public function __construct(Repository $repository, ThreadManager $thread_manager, EventFactory $factory, LayerManager $layer_manager)
    {
        $this->repository = $repository;
        
        $this->threadManager = $thread_manager;
        
        $this->layerManager = $layer_manager;
        
        $this->factory = $factory;
    }
    
    // Принимает новое событие. Нить неизвестна // Первая задача // Вторая задача новая ветка внутри newThread
    public function dispatch(Event $event) : void
    {
        // Фильтруем событие по слою
        $access_layers = $this->layerManager->getAccessLayers($event);
        
        // Получаем участников из допустимых слоёв
        $members = $this->repository->getMembersByLayers($access_layers);
        
        // Создаем задачи. Текущая или корневая // Первая задача // Вторая задача новая ветка внутри newThread
        $this->threadManager->dispatchEvent($event, $members);
    }
    
    // Возвращает объект ответа
    public function result($data) : Result
    {
        return $this->resultFactory->create($this-currentMember, $this->currentTask, $data);
    }
}
