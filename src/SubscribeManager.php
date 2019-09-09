<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class SubscribeManager
{
    private $subscribes;
    
    private $factory;
    
    // $subject = 'layer.member.action.status' 
    public function createSubscribe(string $subject, string $memberWithLayer, string $action)
    {
        $this->subscribes[$subject][] = Subscribe::create([
            'memberWithLayer' => $memberWithLayer,
            'action' => $action,
        ]);
    }
    
    public function getSubscribers(string $subject) : ?array
    {
        if (array_key_exists($subject, $this->subscribes)) {
            return $this->subscribes[$subject];
        }
    }
    
    public function conditions(string $subject, array $conditions) : void
    {
        if (!array_key_exists($subject, $this->subscribes)) {
            // Исключение
        }
        $this->subscribes[$subject]->conditions = $conditions;
    }
    
    public function requested(string $subject, array $requested) : void
    {
        if (!array_key_exists($subject, $this->subscribes)) {
            // Исключение
        }
        $this->subscribes[$subject]->requested = $requested;
    }
    
    public function rollback(string $subject, string $rollbackClassName)
    {
        if (!array_key_exists($subject, $this->subscribes)) {
            // Исключение
        }
        $this->subscribes[$subject]->rollback = $rollbackClassName;
    }
}
