<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Result;

class Dispatcher
{
    // Менеджер слоев
    private $layerManager;
    
    // Фабрика задач
    private $taskFactory;
    
    private $config;
    
    private $fileResource;

    private $loop;
    
    public function __construct(
        TaskFactory $taskFactory, 
        LayerManager $layerManager, 
        Loop $loop, 
        ConfigProvider $config, 
        FileResource $fileResource
    )
    {
        $this->loop = $loop;
        $this->layerManager = $layerManager;
        $this->taskFactory = $taskFactory;
        $this->config = $config;
        $this->fileResource = $fileResource;
    }
    
    // Вызывается из фасада Bus
    public function startLoop($data = null)
    {
        $this->fileResource->plugLayer($this->config->getStartedMemberLayer()); // Подключает участников (регистрируя в Repository)
        
        $this->loop->addTask($this->taskFactory->createTask($this->config->getStartedMember(), $this->config->getStartedAction(), $data));
        
        $this->loop->run();
    }
    
    // Обрабатывает результат текущего таска. Вызывается из TaskManager
    public function dispatchResult(Result $result, Task $doneTask)
    {
        if ($this->isContinued($result)) {
            $doneTask->timeout = $result->timeout();
            $this->loop->retry($doneTask);
        }
    
        // Получаем допустимые слои для события
        $accessLayers = $this->getAccessLayers($doneTask->layer); // ассоциативный по слоям
        
        $subject = $doneTask->layer . '.' . $doneTask->member . '.' . $doneTask->action . '.' . $result->status;
        
        // Получаем подписчиков // массив (содержат conditions)
        $subscribes = $this->subscribeManager->getSubscribes($subject);

        if (!empty($subscribes)) {
            foreach ($subscribes as $subscribe) {
                if (!isset($accessLayers[$subscribe->layer])) {
                    // Исключение
                }
                $this->fileResource->plugLayer($subscribe->memberWithLayer);
                $this->loop->addSubscribedTask($this->taskFactory->createSubscribed($subscribe, $result));
            }
        }
        
        $this->loop->next();
    }
    
    // Запускает переход на следующий слой
    public function upLayer(string $member, string $action)
    {
        $currentTask = $this->loop->getCurrentTask();
        
        $accessLayers = $this->getAccessLayers($currentTask->layer);
        
        if (!isset($accessLayers[$currentTask->layer])) {
            // Исключение
        }
        
        $this->fileResource->plugLayer($member);
        $task = $this->taskFactory->createUpper();
        $this->loop->addUpperTask($task);
    }
    
    // Работа с хуками. Не реализовано
    public function dispatchHook(HookEvent $event)
    {
        $this->fileResource->plugHooks($this->config->getHookListeners());
    }
    
    private function isContinued(Result $result)
    {
        if ($result->status === 'await' or $result->status === 'fail' and !empty($result->timeout)) {
            return true;
        }
        return false;
    }
    
    private function getAccessLayers(string $layer) : array
    {
        return $this->layerManager->getAccessLayers($layer); // ассоциативный по слоям
    }
}
