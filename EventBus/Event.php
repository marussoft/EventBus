<?php

namespace Marussia\Components\EventBus;

class Event
{
    private static $repository;

    public static function repository(Repository $repository)
    {
        static::$repository = $repository;
    }

    // Регистрирует нового участника событий
    public static function register($type, $name, $layer, $handle = '')
    {
        // Должно выбрасывать исключение если репозиторий не передан
        $member = new Member($name, $type, $layer, $handle);
        
        static::$repository->register($member);
        
        return $member;
    }
}
