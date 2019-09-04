<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class LayerManager
{
    private $layers;
    
    private $config;
    
    public function __construct(ConfigProvider $config)
    {
        $this->config = $config;
    }
    
    public function setLayers(array $layers) : void
    {
        $this->layers = $layers;
    }
    
    // Возвращает допустимый массив слоев
    public function getAccessLayers(string $memberLayer) : array
    {
        $numberLayer = 0;
        
        foreach($this->layers as $layer) {
            if ($layer !== $memberLayer) {
                $numberLayer++;
            } else {
                $numberLayer++;
                break;
            }
        }
                
        // Получаем массив слоёв доступных для события
        return array_slice($this->layers, 0, $numberLayer, true);
    }
}
