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
    public function register(string $name, string $layer) : Member
    {
        $member = $this->memberFactory->create(compact($name, $layer));
        
        $this->repository->save($member);
        
        $this->layerManager->register($name, $layer);
        
        return $member;
    }
    
    // Добавляет слои
    public function setLayers(array $layer) : self
    {
        $this->layerManager->setLayers($layer);
        return $this;
    }
    
    public function upLayer(string $member, string $action) : void
    {
    
    }
    
    // Устанавливает обработчики для менеджера задач
    public function setDefaultHandlersMap(array $map) : self
    {
        $this->taskManager->setDefaultHandlersMap($map);
        return $this;
    }
    
    public function run($data = null)
    {
        return $this->dispatcher->startLoop($data);
    }
}
