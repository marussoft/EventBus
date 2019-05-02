<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\DependencyInjection\Container as Container;
use Marussia\EventBus\Contracts\FilterInterface;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Менеджер подписок
    private $threadManager;
    
    // Менеджер слоев
    private $layerManager;
    
    // Менеджер фильтров
    private $filter;
    
    // Менеджер задач
    private $taskManager;
    
    // Фабрика событий
    private $factory;
    
    // Контейнер
    private $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
        
        $this->repository = $this->container->instance(Repository::class);
        
        $this->threadManager = $this->container->instance(ThreadManager::class);
        
        $this->layerManager = $this->container->instance(LayerManager::class);
        
        $this->filter = $this->container->instance(FilterManager::class);
        
        $this->taskManager = $this->container->instance(TaskManager::class);
        
        $this->factory = $this->container->instance(EventFactory::class);
    }
    
    // Добавляет новый слой событий
    public function addLayer(string $layer) : void
    {
        $this->layerManager->addLayer($layer);
    }
    
    // Устанавливает обработчики для менеджера задач
    public function setHandlersMap(array $map) : void
    {
        $this->taskManager->setHandlersMap($map);
    }
    
    // Добавляет фильтр в менеджер фильтров
    public function addFilter(FilterInterface $filter) : void
    {
        $this->filter->addFilter($filter);
    }
    
    // Регистрирует нового участника в шине событий
    public function register(string $type, string $name, string $layer, $handler = '') : Member
    {
        $member = new Member($type, $name, $layer, $handler);
        
        $this->repository->register($member);
        
        $this->layerManager->register($type . '.' . $name, $layer);
        
        return $member;
    }
    
    // Принимает новое событие. Нить неизвестна
    public function dispatch(string $subject, string $event, $event_data = []) : void
    {
        $event = $this->factory->create($subject, $event, $event_data);
        
        $access_layers = $this->layerManager->getAccessLayers($event);
        
        // Получаем участников из допустимых слоёв
        $members = $this->repository->getMembersByLayers($access_layers);
        
        // Создаем задачи. Текущая или корневая
        $this->threadManager->dispatchEvent($event, $members);
    }
    
    // Текущая задача еще не выполнена. Ожидает.
    public function newThread(// started_service.action.even_return_data)
    {
        return // возврат данных по новой нити в сервис который запросил. Только тогда продолжится выполение корневой задачи
    }
}
