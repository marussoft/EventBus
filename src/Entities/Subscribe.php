<?php

declare(strict_types=1);

namespace Marussia\EventBus\Entities;

class Subscribe
{
    public $subject;

    public $memberWithLayer;
    
    public $action;
    
    public $conditions;
    
    public function __construct(string $subject, string $memberWithLayer, string $action, array $conditions)
    {
        $this->subject = $subject;
        $this->member = $memberWithLayer;
        $this->action = $action;
        $this->condition = $conditions;
    }
    
    public function create(array $data)
    {
        return new static($data['subject'], $data['memberWithLayer'], $data['action'], $data['conditions']);
    }
}

