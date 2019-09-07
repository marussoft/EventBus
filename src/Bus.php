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
    
    private $taskManager;
    
    private $config;
    
    private $repository;
    
    private $layerManager;
    
    private $filterManager;
    
    private $memberBuilder;
    
    private $container;
    
    public function __construct()
    {
        $this->container = new Container();
        $this->repository = $this->container->instance(Repository::class);
        $this->layerManager = $this->container->instance(LayerManager::class);
        $this->dispatcher = $this->container->instance(Dispatcher::class);
        $this->filterManager = $this->container->instance(FilterManager::class);
        $this->taskManager = $this->container->instance(TaskManager::class);
        $this->config = $this->container->instance(ConfigProvider::class);
        $this->memberBuilder = $this->container->instance(MemberBuilder::class);
    }
    
    // Переписать на MemberBuildr
    // Регистрирует нового участника в шине событий
    public function register(string $name, string $layer) : Member
    {
        $member = $this->container->instance(Member::class, compact('name', 'layer'), false);
        
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
