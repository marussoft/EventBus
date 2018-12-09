<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Шина событий
    private $bus;

    public function __construct(Repository $repository, Bus $bus)
    {
        $this->repository = $repository;
        
        $this->bus = $bus;
    }

    // Регистрирует нового участника в шине событий
    public function register(string $type, string $name, string $layer, $handle = '')
    {
        $member = new Member($type, $name, $layer);
        
        $this->repository->register($member);
        
        return $member;
    }
    
    // Принимает новое событие
    public function dispatch(string $subject, string $event, $data = null)
    {
        $event = new Event($subject, $event, $data);
        
        $this->bus->event($event);
    }
    
}
