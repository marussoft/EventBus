<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class LayerManager
{
    private $layers;
    
    public function setLayers(array $layers) : void
    {
        foreach($layers as $layer) {
            $this->layers[] = $layer;
        }
    }
    
    // Возвращает допустимый массив слоев
    public function getAccessLayers(string $memberLayer) : array
    {
        // Получаем ключ слоя
        $key = array_search($memberLayer, $this->layers);
        
        // Получаем массив слоёв доступных для события
        return array_slice($this->layers, 0, $key + 1);
    }
}
