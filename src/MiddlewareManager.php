<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Contracts\FilterInterface;

class Filter
{
    // Массив фильтров
    private $filters;
    
    // Задача на обработку
    private $task;
    
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }
    
    // Запускает фильтрацию задачи
    public function run($task) : void
    {
        $this->task = $task;
        
        if (empty($this->filters)) {
            return;
        }
        
        reset($this->filters);
        $this->runFilter();
    }
    
    // Запускает следующий фильтр
    public function next() : void
    {
        if ($this->task === null) {
            return;
        }
    
        if (next($this->filters) === false) {
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
}
 
