<?php 

declare(strict_types=1);

namespace Marussia\EventBus;

use Marussia\DependencyInjection\Container as Container;

class Dispatcher
{
    // Репозиторий всех участников
    private $repository;
    
    // Шина событий
    private $bus;
    
    // Контейнер
    private $c;
    
    public function __construct()
    {
        $this->c = new Container;
        
        $this->repository = $this->c->instance(Repository::class);
        
        $this->bus = $this->c->instance(Bus::class);

    }

    // Регистрирует нового участника в шине событий
    public function register(string $type, string $name, string $layer, $handler = '')
    {
        $member = new Member($type, $name, $layer, $handler);
        
        $this->repository->register($member);
        
        return $member;
    }
    
    // Принимает новое событие
    public function dispatch(string $subject, string $event, $event_data = [])
    {
        $event = new Event($subject, $event, $event_data);
        
        $this->bus->event($event);
    }
    
    public function addLayer(string $layer)
    {
        $this->bus->addLayer($layer);
    }
    
    public function setHandler($handler)
    {
        $this->bus->setHandler($handler);
    }
}
