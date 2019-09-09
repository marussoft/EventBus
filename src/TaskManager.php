<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\DependencyInjection\Container;
use Marussia\EventBus\Entities\Task;
use Marussia\EventBus\Contracts\HandlerInterface;

class TaskManager extends Container
{
    // Тип участника или имя класса для обработчика задачи
    private $handler;
    
    private $filter;
    
    private $dispatcher;
    
    private $middlewareManager;
    
    private $repository;
    
    private $completeTasks = [];
    
    private $config;
    
    public function __construct(FilterManager $filter, MiddlewareManager $middlewareManager, Repository $repository, Dispatcher $dispatcher, ConfigProvider $config)
    {
        $this->filter = $filter;
        $this->middlewareManager = $middlewareManager;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }
    
    // Принимает задачу на обработку
    public function handle(Task $task) : void
    {
        if (!$this->repository->exist($task->layer . '.' . $task->memberName)) {
            // Исключение
        }
        
        $this->currentMember = $this->repository->getMember($task->layer . '.' . $task->memberName);
        
        if ($this->middlewareManager->isAccepted($task)) {
            $this->filterTask($task);
            $handler = $this->prepareHandler($task);
            $this->run($handler, $task);
        }
    }
    
    // Подготавливает обработчик
    private function prepareHandler(Task $task, Member $member) : HandlerInterface
    {
        if (!empty($member->handler)) {
            $handler = $member->handler;
        } else {
            $handler = $this->config->getDefaultHandler($member->layer);
        }
        
        if ($this->has($handler)) {
            return $this->get($handler);
        }
        return $this->instance($handler);
    }

    // Запускает фильтры для задачи
    private function filterTask(Task $task, Member $member)
    {
        if (!empty($member->filters)) {
            $this->filter->run($member->filters, $task);
        }
    }
    
    // Передает задачу в обработчик // Здесь происходит ожидание результата
    private function run(HandlerInterface $handler, Task $task) : void
    {
        try {
            $result = $this->get($handler)->run($task);
            
            if (is_null($result)) {
                $this->dispatcher->dispatchResultEvent($result, $task);
            }
        } catch(\Throwable $exception) {
            $this->dispatcher->rollback($task, $exception);
        }
    }
}
