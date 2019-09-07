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
        $this->saveCompleteTask($result, $resultTask);
    
        // Проверить отложенные
        $this->checkHeld();
    
        // Проверить повторы
        $this->checkRetry();
    
        $this->checkForRetry($result, $resultTask);
    
        $this->prepareTasks($result, $resultTask);
        
        $this->loop->checkUppperQueue();
        
        $this->loop->next();
    }
    
    // Запускает переход на следующий слой
    public function upLayer(string $member, string $action)
    {
        $currentTask = $this->loop->getCurrentTask();
        
        $upperMemberLayer = array_shift(explode('.', $member));
        
        $accessLayers = $this->getAccessLayers($currentTask->layer);
        
        if (array_search($upperMemberLayer, $accessLayers) === false) {
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
    
    public function rollback(Task $task)
    {
    
    }
    
    public function getTaskData(string $taskResult)
    {
    
    }
    
    private function prepareTasks(Result $result, Task $resultTask)
    {
        // Получаем допустимые слои для события
        $accessLayers = $this->getAccessLayers($resultTask->layer); // ассоциативный по слоям
        
        $subject = $resultTask->layer . '.' . $resultTask->member . '.' . $resultTask->action . '.' . $result->status;
        
        // Получаем подписчиков // массив (содержат conditions)
        $this->subscribes[$subject] = $this->subscribeManager->getSubscribes($subject);

        if (!empty($this->subscribes[$subject])) {
            foreach ($this->subscribes[$subject] as $subscribe) {
                if (array_search($subscribe->layer, $accessLayers) === false) {
                    // Исключение
                }
                $this->fileResource->plugLayer($subscribe->memberWithLayer); // Проверить на ошибку
                $this->loop->addTask($this->taskFactory->createSubscribed($subscribe, $result));
            }
        }
    }
    
    private function checkHeld()
    {
        foreach($this->heldTasks as $task) {
            if ($this->isSatisfied($task)) {
                $this->loop->addTask($task);
                unset($this->heldTasks[current($this->heldTasks)]);
            }
        }
    }
    
    private function isSatisfied(Task $task) : bool
    {
        $intersections = array_intersect($task->conditions, $this->heldTasks);
        
        $conditions = count($intersections) === count($conditions);
        
        // Проверка на наличие нужных данных из SubscribeManager
        $subject = $task->layer . '.' . $task->member . '.' . $task->action . '.' . $task->status; // беда / Доработать SubscribeManager
        
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
                unset($this->retryTasks[current($this->retryTasks)]);
            }
        }
    }
    
    private function saveCompleteTask(Result $result, Task $task)
    {
        $this->completeTasks[$task->layer . '.' . $task->member . '.' . $task->action . '.' . $result->status] = $task;
    }
    
    private function checkForRetry(Result $result, Task $task)
    {
        if ($result->status === 'await') {
            $task->timeout = (microtime() + $result->timeout());
            $this->retryTasks[] = $task;
        }
    }
}
