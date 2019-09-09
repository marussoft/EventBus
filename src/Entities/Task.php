<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

class Task
{
    public $memberName;
    
    public $action;
    
    public $layer;
    
    public $data;
    
    public $conditions = [];
    
    public $handler = '';

    public $requested = [];
    
    public $rollback = '';
}
