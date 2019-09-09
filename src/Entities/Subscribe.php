<?php

declare(strict_types=1);

namespace Marussia\EventBus\Entities;

class Subscribe
{
    public $subject;

    public $memberWithLayer;
    
    public $action;
    
    public $conditions = [];
    
    public $requested = [];
    
    public function __construct(string $subject, string $memberWithLayer, string $action)
    {
        $this->subject = $subject;
        $this->member = $memberWithLayer;
        $this->action = $action;
    }
    
    public function create(array $data)
    {
        return new static($data['subject'], $data['memberWithLayer'], $data['action']);
    }
}
