<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Filter
{
    // Массив фильтров
    private $filters;
    
    // задача на обработку
    private $task;
    
    // Слудующий обработчик
    private $handler;

    public function __construct()
    {
        $this->filters = [];
    }
    
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }
    
    // Запускает фильтрацию задачи
    public function run($task) : void
    {
        $this->task = $task;
        
        if (empty($this->filters)) {
        
            $this->nextHandler();
            
        } else {
        
            reset($this->filters);
            $this->runFilter();
        }
    }
    
    // Запускает следующий фильтр
    public function next() : void
    {
        if ($this->task === null) {
            return;
        }
    
        if (next($this->filters) === false) {
            $this->nextHandler();
            return;
        }
        
        $this->runFilter();
    }
    
    // Останавливает обработку задачи
    public function break() : void
    {
        $this->task = null;
    }
    
    // Запускает текущий фильтр
    private function runFilter() : void
    {
        $filter = current($this->filters);

        $filter->run($this->task, $this);
    }
    
    // Передает задачу в следующий обработчик
    private function nextHandler() : void
    {
        $this->handler->run($this->task);
    }
    
}
 
