<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class MemberBuiler
{
    private $subscribeManager;
    
    public function __construct(SubscribeManager $subscribeManager)
    {
        $this->subscribeManager = $subscribeManager;
    }
    
    public function subscribe()
    {
    
    }
    
    public function conditions()
    {
    
    }
    
    public function request()
    {
    
    }
}
