<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Task;

class Loop
{
    private $taskManager;

    private $subscribeQueue;
    
    private $heldTasks = [];
    
    private $currentTask;
    
    private $retryTasks = [];
    
    private $completeTasks = [];
    
    private $upperTasks;

    public function __construct(TaskManager $taskManager)
    {
        $this->taskManager = $taskManager;
        
        $this->subscribeQueue = new TaskQueue();
        $this->subscribeQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        
        $this->upperTasks = new TaskQueue();
        $this->upperTasks->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }
    
    public function addSubscribedTask(Task $task)
    {
        if ($this->isSatisfied($task)) {
            $this->subscribeQueue->addTask($task);
        } else {
            $this->heldTasks[] = $task;
        }
    }
    
    public function addUpperTask(Task $task)
    {
        $this->upperTasks->enqueue($task);
    }

    public function retry(Task $task)
    {
        $this->retryTasks[] = $task;
    }
    
    public function getCurrentTask() : Task
    {
        return $this->currentTask;
    }
    
    public function run()
    {
        $this->currentTask = $this->subscribeQueue->dequeue();
        
        $this->taskManager->run($this->currentTask);
    }
    
    public function next()
    {
        // Проверить отложенные
        
        // Проверить повторы
    
        $this->archiveTask();
        
        $this->currentTask = $this->subscribeQueue->dequeue();
        
        if ($this->subscribeQueue->isEmpty() && !$this->upperTasks->isEmpty()) {
            $this->subscribeQueue->addTask($this->upperTasks->dequeue());
        }
        
        $this->taskManager->run($this->currentTask);
    }
    
    private function archiveTask()
    {
        $this->completeTasks[] = $this->currentTask;
    }
    
    private function checkHeld()
    {
    
    }
    
    private function isSatisfied(Task $task)
    {
    
    }
}
 
