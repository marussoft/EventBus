<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class LayerManager
{
    private $layers;
    
    private $members;
    
    private $config;
    
    public function __construct(ConfigProvider $config)
    {
        $this->config = $config;
    }
    
    public function register($subject, $layer)
    {
        $this->members[$subject] = $layer;
    }
    
    public function setLayers(array $layers) : void
    {
        $this->layers = $layers;
    }
    
    // Возвращает допустипый массив слоев
    private function getAccessLayers(string $layer) : array
    {
        // Получаем имя слоя по владельцу события
        $layer = $this->members[$subject];

        // Получаем ключ слоя
        $num = array_search($layer, $this->layers);
        
        // Получаем массив слоёв доступных для события
        return array_slice($this->layers, 0, $num + 1);
    }
}
