<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Task
{
    private $name;
    
    private $action;
    
    private $layer;
    
    private $data;
    
    private $conditions;
    
    private $handler;

    public function __construct(string $name, string $action, string $layer, array $conditions = [], string $handler = '')
    {
        $this->name = $name;
        $this->action = $action;
        $this->layer = $layer;
        $this->conditions = $conditions;
        $this->handler = $handler;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function name()
    {
        return $this->name;
    }
    
    public function type()
    {
        return $this->type;
    }
    
    public function action()
    {
        return $this->action;
    }
    
    public function layer()
    {
        return $this->layer;
    }
    
    public function data()
    {
        return $this->data;
    }
    
    public function conditions()
    {
        return $this->conditions;
    }
    
    public function handler()
    {
        return $this->handler;
    }
}
