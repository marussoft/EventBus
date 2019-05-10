<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Thread
{
    // Массив отложенных задач
    private $held = [];
    
    // Очередь задач
    private $taskQueue;
    
    // Хранилище полученых событий
    private $storage;
    
    // Обработчик задач
    private $handler;

    public function __construct(Storage $storage, TaskManager $task_manager)
    {
        $this->storage = $storage;
        $this->handler = $task_manager;
    
        $this->taskQueue = new \SplQueue;
        $this->taskQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }
    
    // Обрабатывает задачи для слушателя
    public function addTask(Event $event, Task $task) : void
    {
        $this->storage->save($event);
    
        $this->checkHeld();
    
        if ($this->storage->exists($task->conditions())) {
            // Помещаем задачу в очередь задач на выполнение
            $this->taskQueue->enqueue($task);
            return;
        }
        // Иначе помещаем в отложенные
        $this->held[] = $task;
    }
    
    // Запускает обработку задачи
    public function run() : void
    {
        $this->handler->handle($this->taskQueue->pop());
        // runed = true
        
        $this->iterate();
    }
    
    // Проверяет возможность выполнения отложенных задач
    private function checkHeld() : void
    {
        foreach($this->held as $key => $task) {
            // Проверяем выполнены ли условия
            if ($this->storage->exists($task->conditions())) {

                // Помещаем задачу в массив задач на выполнение
                $this->taskQueue->enqueue($task);
                // Удаляем задачу из отложенных
                unset($this->held[$key]);
            }
        }
    }
    
    // Итерирует задачи в очереди
    private function iterate() : void
    {
        if (!$this->taskQueue->isEmpty()) {
            $this->run();
        }
    }
}
 
