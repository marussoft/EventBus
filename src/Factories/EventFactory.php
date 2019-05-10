<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class EventFactory
{
    public function create(string $subject, string $event, $event_data = [])
    {
        return new Event($subject, $event, $event_data);
    }
}
