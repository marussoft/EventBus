<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class Repository
{
    // Массив участников
    private $members;
    
    // Регистрирует участника в репозитории шины
    public function save(Member $member) : void
    {
        $this->members[$member->layer . '.' . $member->name()] = $member;
    }
    
    // Возвращает участника по layer.name
    public function getMember(string $memberWithLayer) : Member
    {
        return $this->members[$memberWithLayer];
    }
    
    public function has(string $memberWithLayer) : bool
    {
        return array_key_exists($memberWithLayer, $this->members);
    }
}
