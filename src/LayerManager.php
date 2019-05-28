<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class LayerManager
{
    private $layers;
    
    private $members;
    
    public function __construct()
    {
        $this->layers[] = 'App';
    }
    
    public function register($subject, $layer)
    {
        $this->members[$subject] = $layer;
    }
    
    public function addLayer(string $layer) : void
    {
        $this->layers[] = $layer;
    }
    
    // Возвращает допустипый массив слоев
    private function getAccessLayers(string $subject) : array
    {
        // Получаем имя слоя по владельцу события
        $layer = $this->members[$subject];

        // Получаем ключ слоя
        $num = array_search($layer, $this->layers);
        
        // Получаем массив слоёв доступных для события
        return array_slice($this->layers, 0, $num + 1);
    }
}
