<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

use Marussia\Components\DependencyInjection\Container as Container;

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
    public function register(string $type, string $name, string $layer, $handle = '')
    {
        $member = new Member($type, $name, $layer, $handle);
        
        $this->repository->register($member);
        
        return $member;
    }
    
    // Принимает новое событие
    public function dispatch(string $subject, string $event, $event_data = null)
    {
        $data = new \stdClass();
        
        $data->eventData = $event_data;
    
        $event = new Event($subject, $event, $data);
        
        $this->bus->event($event);
    }
    
    public function addLayer(string $layer)
    {
        $this->bus->addLayer($layer);
    }
    
    public function setHandle($handle, string $method)
    {
        $this->bus->setHandle($handle, $method);
    }
}
