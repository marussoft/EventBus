<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Bus
{
    // Массив отложенных задач
    private $held = [];
    
    // Очередь задач
    private $taskQueue;
    
    // Хранилище полученых событий
    private $storage;
    
    // Обработчик задач
    private $handler;

    public function __construct(Storage $storage, FilterManager $filter)
    {
        $this->storage = $storage;
        $this->filter = $filter;
    
        $this->taskQueue = new \SplQueue;
        $this->taskQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }
    
    // Обрабатывает задачи для слушателя
    public function addTask(Event $event, Task $task) : void
    {
        $this->storage->register($event);
    
        $this->checkHeld();
    
        if ($this->storage->exists($task->conditions())) {
            // Помещаем задачу в очередь задач на выполнение
            $this->taskQueue->enqueue($task);
            return;
        }
        // Иначе помещаем в отложенные
        $this->held[] = $task;
    }
    
    public function setHandler(HandlerInterface $handler) : void
    {
        $this->handler = $handler;
    }
    
    public function addFilter(FilterInterface $filter)
    {
        $this->filter->addFilter($filter);
    }
    
    // Запускает очередь задач
    public function run() : void
    {
        foreach ($this->iterate() as $task) {
            $$this->filter->run($task);
            
            $this->handler->run($task);
        }
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
    private function iterate() : \Traversable
    {
        while(!$this->taskQueue->isEmpty()) {
            yield $this->taskQueue->pop();
        }
    }
}
