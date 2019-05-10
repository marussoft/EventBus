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
    
    public function __construct(FilterManager $filter, EventDispatcher $dispatcher)
    {
        $this->filter = $filter;
        $this->dispatcher = $dispatcher;
    }

    public function setHandlersMap(array $map) : void
    {
        $this->classMap = $map;
    }
    
    // Принимает задачу на обработку
    public function handle(Task $task) : void
    {
        $this->task = $task;
        $this->prepare();
        $this->filter();
        
        if (is_null($this->task)) {
            return;
        }
        $this->run();
    }
    
    // Подготавливает принятую задачу в обработчик
    private function prepare($task) : void
    {
        if (empty($task->handler())) {
            $this->handler = $this->classMap[$task->type()];
        } else {
            $this->handler = $this->classMap[$task->handler()];
        }
        
        if (!$this->has($this->handler)) {
            $this->instance($this->handler);
        }
    }
    
    // Запускает фильтры для задачи
    private function filter()
    {
        $this->filter->run($this->task);
    }
    
    // Передает задачу в обработчик // Здесь происходит ожидание результата
    private function run() : void
    {
        // Исключение здесь
        $this->dispatcher->result($this->get($this->handler)->run($this->task));
    }
}
