<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Task;

class Loop
{
    private $taskManager;

    private $mainQueue;
    
    private $currentTask;
    
    private $upperQueue;

    public function __construct(TaskManager $taskManager)
    {
        $this->taskManager = $taskManager;
        
        $this->mainQueue = new TaskQueue();
        $this->mainQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        
        $this->upperQueue = new TaskQueue();
        $this->upperQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }
    
    public function addTask(Task $task)
    {
        $this->mainQueue->addTask($task);
    }
    
    public function addUpperTask(Task $task)
    {
        $this->upperQueue->enqueue($task);
    }
    
    public function getCurrentTask() : Task
    {
        return $this->currentTask;
    }
    
    public function run()
    {
        if ($this->mainQueue->isEmpty()) {
            // Исключение
        }
        
        $this->currentTask = $this->mainQueue->dequeue();
        $this->taskManager->run($this->currentTask);
    }
    
    public function next()
    {
        if ($this->mainQueue->isEmpty() && !$this->upperQueue->isEmpty()) {
            $this->mainQueue->addTask($this->upperQueue->dequeue());
        }
        
        if (!$this->mainQueue->isEmpty()) {
            $this->currentTask = $this->mainQueue->dequeue();
            $this->taskManager->run($this->currentTask);
        }
    }
}
