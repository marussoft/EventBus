<?php 

declare(strict_types=1);

namespace Marussia\EventBus\Entites;

class Member
{
    // Имя участника
    public $name;
    
    // Слой
    public $layer;
    
    // Класс обработчика задач
    public $handler = '';
    
    //  Массив фильтров для всех задач участника
    public $filters = [];

}
