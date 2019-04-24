<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\DependencyInjection\Container as Container;
use Marussia\EventBus\Contracts\FilterInterface;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Шина событий
    private $bus;
    
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
    
    public function __construct()
    {
        $this->container = new Container;
        
        $this->repository = $this->container->instance(Repository::class);
        
        $this->bus = $this->container->instance(Bus::class);
        
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
    
    // Принимает новое событие
    public function dispatch(string $subject, string $event, $event_data = []) : void
    {
        $event = $this->factory->create($subject, $event, $event_data);
        
        $access_layers = $this->layerManager->getAccessLayers($event);
        
        // Получаем участников из допустимых слоёв
        $members = $this->repository->getMembersByLayers($access_layers);
        
        // Создаем задачи
        $this->createTasks($event, $members);
    }
    
    // Создает задачи для события // Ошибка
    private function createTasks(Event $event, array $members) : void
    {
        // Проходим по всем допустимым слушателям
        foreach($members as $member) {
        
            if (!$member->isSubscribed($event->subject(), $event->name())) {
                continue;
            }
            
            $tasks = $member->getTasks($event->subject(), $event->name(), $event->data());
            
            // Создаем задачи если участник подписан на событие
            foreach ($tasks as $task) {
                $this->bus->addTask($event, $task);
            }
        }
    }
}
