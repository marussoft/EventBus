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

    private $memberDirPath;
    
    private $dispatcher;
    
    private $config;
    
    public function __construct(
        ResultFactory $result_factory,
        Repository $repository, 
        LayerManager $layer_manager,
        Dispatcher $dispatcher,
        TaskManager $task_manager,
        FilterManager $filter_manager,
        ConfigProvider $config
    ){
        $this->resultFactory = $result_factory;
        $this->repository = $repository;
        $this->layerManager = $layer_manager;
        $this->dispatcher = $dispatcher;
        $this->filterManager = $filter_manager;
        $this->taskManager = $task_manager;
        $this->config = $config;
    }
    
    public static function create() : Bus
    {
        $container = new Container;
        
        return $container->instance(Bus::class);
    }
    
    // Регистрирует нового участника в шине событий
    public function register(string $name, string $layer) : Member
    {
        $member = Member::create(compact('name', 'layer'));
        
        $this->repository->save($member);
        
        return $member;
    }
    
    // Добавляет слои
    public function setLayers(array $layers) : self
    {
        $this->layerManager->setLayers($layers);
        return $this;
    }
    
    public function upLayer(string $member, string $action) : void
    {
    
    }
    
    public function setMemberDirPath(string $memberDirPath)
    {
        $this->memberDirPath = $memberDirPath;
    }
    
    // Устанавливает обработчики для менеджера задач
    public function setDefaultHandlersMap(array $map) : self
    {
        $this->taskManager->setDefaultHandlersMap($map);
        return $this;
    }
    
    // $startingTask Layer.MemberName.Action
    public function setStartingTask(string $startingTask)
    {
        $this->config->setStartingTask($startingTask);
    }
    
    public function run($data = null)
    {
        return $this->dispatcher->startLoop($data);
    }
}
