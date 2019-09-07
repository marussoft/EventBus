<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\DependencyInjection\Container as Container;

class TaskManager extends Container
{
    // Задача на обработку
    private $task;
    
    // Массив ассоциаций типов участников с их классами
    private $classMap;
    
    // Тип участника или имя класса для обработчика задачи
    private $handler;
    
    private $filter;
    
    private $dispatcher;
    
    private $middlewareManager;
    
    private $repository;
    
    public function __construct(FilterManager $filter, Dispatcher $dispatcher, MiddlewareManager $middlewareManager, Repository $repository)
    {
        $this->filter = $filter;
        $this->dispatcher = $dispatcher;
        $this->middlewareManager = $middlewareManager;
        $this->repository = $repository;
    }
    
    // Принимает задачу на обработку
    public function handle(Task $task) : void
    {
        $this->task = $task;
        
        if (!$this->repository->exist($task->layer . '.' . $task->memberName)) {
            // Исключение
        }
        
        $this->member = $this->repository->getMember($task->layer . '.' . $task->memberName);
        
        if ($this->middlewareManager->isAccepted($task)) {
            $this->filterTask();
            $this->prepareTask();
            $this->run();
        }
    }
    
    // Подготавливает принятую задачу в обработчик
    private function prepareTask($task) : void
    {
        if (!empty($this->member->handler)) {
            $this->handler = $this->member->handler;
        }
        
        if (!$this->has($this->handler)) {
            $this->instance($this->handler);
        }
    }

    // Запускает фильтры для задачи
    private function filterTask()
    {
        if (!empty($this->currentMember->filters)) {
            $this->filter->run($this->currentMember->filters);
        }
    }
    
    // Передает задачу в обработчик // Здесь происходит ожидание результата
    private function run() : void
    {
        try {
            $result = $this->get($this->handler)->run($this->task);
            $this->dispatcher->dispatchResult($result);
        } catch(\Exception $e) {
            $this->dispatcher->rollback($this->task, $e);
        }
    }
}
