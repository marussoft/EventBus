<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Task
{
    private $name;
    
    private $type;
    
    private $action;
    
    private $layer;
    
    private $data;
    
    private $conditions;
    
    private $handle;

    public function __construct(string $name, string $type, string $action, string $layer, array $conditions = [], string $handle = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->action = $action;
        $this->layer = $layer;
        $this->conditions = $conditions;
        $this->handle = $handle;
    }
    
    public function setData(\stdClass $data)
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
    
    public function handle()
    {
        return $this->handle;
    }
}
