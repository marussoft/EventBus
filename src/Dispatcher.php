<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Менеджер слоев
    private $layerManager;
    
    // Фабрика задач
    private $taskFactory;
    
    private $config;
    
    private $fileResource;

    private $loop;
    
    public function __construct(
        Repository $repository, 
        TaskFactory $taskFactory, 
        LayerManager $layerManager, 
        Loop $loop, 
        ConfigProvider $config, 
        FileResource $fileResource
    )
    {
        $this->repository = $repository;
        $this->loop = $loop;
        $this->layerManager = $layerManager;
        $this->taskFactory = $taskFactory;
        $this->config = $config;
        $this->fileResource = $fileResource;
    }
    
    // Вызывается из фасада Bus
    public function startLoop($data = null)
    {
        $this->fileResource->plugLayer($this->config->getStartedLayer());
        
        $startedMember = $this->repository->getMember($this->config->getStartedMember());
        
        $this->loop->addTask($this->taskFactory->createTask($startedMember, $this->config->getStartedAction()));
        
        $this->loop->run();
    }
    
    // Обрабатывает результат текущего таска
    public function dispatchResult(ResultInterface $result, Task $doneTask)
    {
        // Получаем допустимые слои для события
        $accessLayers = $this->layerManager->getAccessLayers($doneTask->layer); // ассоциативный по слоям
        
        // Получаем подписчиков // массив (содержат conditions)
        $subscribes = $this->subscribeManager->getSubscribers($doneTask->layer . '.' . $doneTask->member . '.' . $doneTask->action . '.' . $result->status);

        if (!empty($subscribes)) {
            foreach ($subscribes as $subscribe) {
                if (!isset($accessLayers[$subscribe->layer])) {
                    // Исключение
                }
                $this->loop->addTask($this->taskFactory->createTask($subscribe->memberWithLayer, $subscribe->action, $result->data));
            }
        }
        
        $this->loop->next();
    }
    
    public function dispatchSatelliteEvent(SatelliteEvent $event)
    {
        $this->fileResource->plugHooks($this->config->getHookListeners());
    }
}
