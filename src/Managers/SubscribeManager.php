<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class SubscribeManager
{
    // Создает задачи для события // Ошибка
    public function createTasks(Event $event, array $members) : void
    {
        $tasks = [];
    
        // Проходим по всем допустимым слушателям
        foreach($members as $member) {
        
            if (!$member->isSubscribed($event->subject(), $event->name())) {
                continue;
            }
            
            // ошибка. нужно получать по одной задаче
            $tasks[] = $member->getTasks($event->subject(), $event->name(), $event->data());
        }
    }
}
