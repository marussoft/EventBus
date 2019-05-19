<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Contracts\FilterInterface;
use Marussia\EventBus\Factories\MemberFactory;
use Marussia\EventBus\Factories\ResultFactory;
use Marussia\EventBus\Entities\Result;
use Marussia\DependencyInjection\Container;

class Bus
{
    private $memberFactory;

    public function __construct(
        MemberFactory $member_factory,
        ResultFactory $result_factory,
        Repository $repository, 
        LayerManager $layer_manager,
        EventDispatcher $dispatcher,
        TaskManager $task_manager,
        FilterManager $filter_manager
    ){
        $this->memberFactory = $member_factory;
        $this->resultFactory = $result_factory;
        $this->repository = $repository;
        $this->layerManager = $layer_manager;
        $this->dispatcher = $dispatcher;
        $this->filterManager = $filter_manager;
        $this->taskManager = $task_manager;
    }
    
    public static function create() : Bus
    {
        $container = new Container;
        
        return $container->instance(Bus::class);
    }
    
    // Регистрирует нового участника в шине событий
    public function register(string $type, string $name, string $layer, string $handler = '') : Member
    {
        $member = $this->memberFactory->create(compact($type, $name, $layer, $handler));
        
        $this->repository->save($member);
        
        $this->layerManager->register($type . '.' . $name, $layer);
        
        return $member;
    }
    
    // Текущая задача еще не выполнена она запущена в Bus. Ожидает. Нужно знать владельца (текущую задачу)
    public function newThread(// started_service.action.even_return_data)
    {
        $member = $this->repository->getMember($starter);
        
        $this->threadManager->newThread(// Создать таск из started_service.action.even_return_data);
        return // возврат данных по новой нити в сервис который запросил. Только тогда продолжится выполение корневой задачи
    }
    
    // Добавляет новый слой событий
    public function addLayer(string $layer) : self
    {
        $this->layerManager->addLayer($layer);
        return $this;
    }
    
    // Устанавливает обработчики для менеджера задач
    public function setHandlersMap(array $map) : self
    {
        $this->taskManager->setHandlersMap($map);
        return $this;
    }
    
    // Принимает новое событие. Нить неизвестна // Первая задача // Вторая задача новая ветка внутри newThread
    public function dispatch(string $subject, string $event, $event_data = []) : void
    {
        $this->dispatcher->dispatch(string $subject, string $event, $event_data = []);
    }
    
    // Возвращает объект результата для задачи
    public function result($data) : Result
    {
        return $this->dispatcher->result($data);
    }
    
    public function command(string $member, string $action)
    {
    
    }
}
