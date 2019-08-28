<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Loop
{
    // Массив отложенных задач
    private $held = [];
    
    // Очередь задач
    private $taskQueue;
    
    // Хранилище полученых событий
    private $storage;
    
    // Обработчик задач
    private $taskManager;

    private $parrentThreadId;
    
    private $returnPoint;
    
    private $returnData = null;
    
    public function __construct(Storage $storage, TaskManager $task_manager, string $parrent_thread_id, string $return_point, Queue $queue)
    {
        $this->storage = $storage;
        $this->taskManager = $task_manager;
        $this->parrentThreadId = $parrent_thread_id;
        $this->returnPoint = $return_point;
        $this->taskQueue = $queue;;
    }
    
    public function getParrentThreadId() : string
    {
        return $this->parrentThreadId;
    }
    
    // Обрабатывает задачи для слушателя
    public function addTask(Event $event, Task $task) : void
    {
        if ($this->returnPoint === $event->subject . '.' . $event->action) {
            $this->returnData = $event->data;
        }
    
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
        $this->taskManager->handle($this->taskQueue->pop());

        $this->iterate();
    }
    
    // Проверяет возможность выполнения отложенных задач
    private function checkHeld() : void
    {
        foreach($this->held as $key => $task) {
            // Проверяем выполнены ли условия
            if ($this->storage->exists($task->conditions())) {

                // Помещаем задачу в очередь задач на выполнение
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
 
