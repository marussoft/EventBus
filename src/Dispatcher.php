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
        $this->fileResource->plugLayer($this->config->getStartedLayer());
        
        $startedMember = $this->repository->getMember($this->config->getStartedMember());
        
        $this->loop->addTask($this->taskFactory->createTask($startedMember, $this->config->getStartedAction()));
        
        $this->process();
    }
    
    // Обрабатывает результат текущего таска
    public function dispatchResult(ResultInterface $result, Task $doneTask)
    {
        $event = $this->eventFactory->create($result, $doneTask);
        
        $this->eventStorage->add($event); // зависим от EventStorage
        
        // Получаем допустимые слои для события
        $accessLayers = $this->layerManager->getAccessLayers($doneTask->member->layer); // ассоциативный по слоям
        
        // Получаем подписчиков
        $subscribers = $this->subscribeManager->getSubscribers($event); // массив (содержат conditions)

        foreach ($subscribers as $subscriber) {
            if (!isset($accessLayers[$subscriber->layer])) {
                // Исключение
            }
            $this->loop->addTask($this->taskFactory->createTask($this->repository->getMember($subscriber->member), $subscriber, $result));
        }
    }
    
    public function dispatchSatelliteEvent(SatelliteEvent $event)
    {
    
    }
    
    private function process()
    {

    }
}
