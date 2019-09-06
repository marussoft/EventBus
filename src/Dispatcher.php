<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Result;

class Dispatcher
{
    private $layerManager;
    
    private $taskFactory;
    
    private $config;
    
    private $fileResource;

    private $loop;
    
    private $heldTasks = [];
    
    private $completeTasks = [];
    
    private $retryTasks = [];
    
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
        $this->fileResource->plugLayer($this->config->getStartedMemberLayer());
        
        $task = $this->taskFactory->createTask($this->config->getStartedMember(), $this->config->getStartedAction(), $data);
        
        $this->loop->addTask($task);
        
        $this->loop->run();
    }
    
    // Обрабатывает результат текущего таска. Вызывается из TaskManager
    public function dispatchResult(Result $result, Task $resultTask)
    {
        $this->saveToListCompleteTask($result, $resultTask);
    
        // Проверить отложенные
        $this->checkHeld();
    
        // Проверить повторы
        $this->checkRetry();
    
        $this->checkForRetry($result, $resultTask);
    
        $this->prepareTasks($result, $resultTask);
        
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
    
    private function prepareTasks(Result $result, Task $resultTask)
    {
        // Получаем допустимые слои для события
        $accessLayers = $this->getAccessLayers($resultTask->layer); // ассоциативный по слоям
        
        $subject = $resultTask->layer . '.' . $resultTask->member . '.' . $resultTask->action . '.' . $result->status;
        
        // Получаем подписчиков // массив (содержат conditions)
        $subscribes = $this->subscribeManager->getSubscribes($subject);

        if (!empty($subscribes)) {
            foreach ($subscribes as $subscribe) {
                if (!isset($accessLayers[$subscribe->layer])) {
                    // Исключение
                }
                $this->fileResource->plugLayer($subscribe->memberWithLayer);
                $this->loop->addTask($this->taskFactory->createSubscribed($subscribe, $result));
            }
        }
    }
    
    private function checkHeld()
    {
        foreach($this->heldTasks as $task) {
            if ($this->isSatisfied($task->conditions)) {
                $this->loop->addTask($task);
            }
        }
    }
    
    private function isSatisfied(array $conditions)
    {

    }
    
    private function getAccessLayers(string $layer) : array
    {
        return $this->layerManager->getAccessLayers($layer); // ассоциативный по слоям
    }
    
    private function checkRetry()
    {
        foreach ($this->retryTasks as $task) {
            if ($task->timeout <= microtime()) {
                $this->loop->addTask($task);
            }
        }
    }
    
    private function saveToListCompleteTask(Result $result, Task $task)
    {
        $this->completeTasks[] = $task->layer . '.' . $task->member . '.' . $task->action . '.' . $result->status;
    }
    
    private function checkForRetry(Result $result, Task $task)
    {
        if ($result->status === 'await') {
            $task->timeout = (microtime() + $result->timeout());
            $this->retryTasks[] = $task;
        }
    }
}
