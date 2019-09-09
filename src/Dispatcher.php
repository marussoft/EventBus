<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Result;
use Marussia\EventBus\Exceptions\ActionIsNotAccessedForMemberException;

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
    
    private $rollback;
    
    private const RESULT_STATUS = 'await';
    
    public function __construct(
        TaskFactory $taskFactory, 
        LayerManager $layerManager, 
        Loop $loop, 
        ConfigProvider $config, 
        FileResource $fileResource,
        Rollback $rollback
    )
    {
        $this->loop = $loop;
        $this->layerManager = $layerManager;
        $this->taskFactory = $taskFactory;
        $this->config = $config;
        $this->fileResource = $fileResource;
        $this->rollback = $rollback;
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
    public function dispatchResultEvent(Result $result, Task $resultTask)
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
    public function upLayer(string $member, string $action, $data = null)
    {
        $currentTask = $this->loop->getCurrentTask();
        
        $upperMemberLayer = array_shift(explode('.', $member));
        
        $accessLayers = $this->getAccessLayers($currentTask->layer);
        
        if (array_search($upperMemberLayer, $accessLayers) === false) {
            throw new ActionIsNotAccessedForMemberException($member, $action);
        }
        
        $this->fileResource->plugLayer($member);
        $task = $this->taskFactory->createUpper($member, $action, $data);
        $this->loop->addUpperTask($task);
    }
    
    public function getTaskData(string $taskName) : array
    {
        if (array_key_exists($taskName, $this->completeTasks)) {
            // Исключение
        }
        return $this->completeTasks[$taskName]->data;
    }
    
    public function rollback(Task $task, \Throwable $exception) : void
    {
        $this->saveCompleteTask($task);
        
        $this->rollback->run($this->completeTasks);
        
        throw $exception;
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
                $task = $this->taskFactory->createSubscribed($subscribe, $result);
                
                if ($this->isSatisfied($task)) {
                    $this->loop->addTask($task);
                } else {
                    $this->heldTasks[$task->layer . '.' . $task->member . '.' . $task->action . '.' . $result->status] = $task;
                }
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
        $conditions = array_intersect($task->conditions, $this->completeTasks);
        
        if (count($conditions) !== count($task->conditions)) {
            return false;
        }

        // Проверка на наличие нужных данных из выполненных тасков
        $requestedTasksData = array_intersect($task->requested, $this->completeTasks);
        
        if (count($requestedTasksData) !== count($task->requested)) {
            return false;
        }
        
        $accessLayers = $this->getAccessLayers($task->layer);
        
        foreach($requestedTasksData as $completeTask) {
            if (array_search($completeTask->layer, $accessLayers) === false) {
                // Исключение
            }
        
            $task->data = array_merge($task->data, $completeTask->data);
        }
        return true;
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
        if ($result->status === self::RESULT_STATUS) {
            $task->timeout = (microtime() + $result->timeout());
            $this->retryTasks[] = $task;
        }
    }
}
