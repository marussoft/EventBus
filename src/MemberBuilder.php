<?php

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\EventBus\Entities\Member;

class MemberBuiler
{
    private $subscribeManager;
    
    private $member;
    
    private $subject = '';
    
    public function __construct(SubscribeManager $subscribeManager)
    {
        $this->subscribeManager = $subscribeManager;
    }
    
    public function create(string $layer, string $memberName)
    {
        return $this->member = new Member($layer, $memberName);
    }
    
    public function subscribe(string $subject, string $action)
    {
        $this->subject = $subject;
        $this->subscribeManager->createSubscribe($subject, $this->member->layer . '.' . $this->member->name, $action);
        return $this;
    }
    
    public function conditions(array $conditions)
    {
        $this->subscribeManager->conditions($this->subject, $conditions);
        return $this;
    }
    
    public function requested(array $requested)
    {
        $this->subscribeManager->requested($this->subject, $requested);
        return $this;
    }
    
    public function rollback(string $rollbackClassName)
    {
        $this->subscribeManager->requested($this->subject, $rollbackClassName);
        return $this;
    }
    
    public function filters(array $filters)
    {
        $this->member->filters = $filters;
        return $this;
    }
    
    public function handler(string $handler)
    {
        $this->member->handler = $handler;
        return $this;
    }
}
