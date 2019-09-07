<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class SubscribeManager
{
    private $subscribes;
    
    private $factory;
    
    // $subject = 'layer.member.action.status' 
    public function createSubscribe(string $subject, string $memberWithLayer, string $action, array $conditions = [])
    {
        $this->subscribes[$subject][] = Subscribe::create([
            'memberWithLayer' => $memberWithLayer,
            'action' => $action,
            'conditions' => $conditions
        ]);
    }
    
    public function getSubscribers(string $subject) : ?array
    {
        if (array_key_exists($subject, $this->subscribes)) {
            return $this->subscribes[$subject];
        }
    }
    
    public function conditions(array $conditions)
    {

    }
    
    public function request(array $conditions)
    {
    
    }
}
