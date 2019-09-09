<?php

declare(strict_types=1);

namespace Marussia\EventBus;

class ConfigProvider
{
    private $startedMember;
    
    private $startedAction;
    
    private $startedLayer;

    private $memberDirPath;
    
    private $defaultHandlersMap;

    public function setMemberDirPath(string $memberDirPath) : void
    {
        $this->memberDirPath = $memberDirPath;
    }
    
    public function setDefaultHandlersMap(array $defaultHandlersMap) : void
    {
        $this->defaultHandlersMap = $defaultHandlersMap;
    }
    
    public function setStartingTask(string $startingTask) : void
    {
        $segments = explode('.', $startingTask);
        $this->startedLayer = $segments[0];
        $this->startedMember = $segments[1];
        $this->startedAction = $segments[2];
    }
    
    public function getStartedLayer() : string
    {
        return $this->startedLayer;
    }
    
    public function getStartedAction() : string
    {
        return $this->startedAction;
    }
    
    public function getStartedMember() : string
    {
        return $this->startedMember;
    }
    
    public function getDefaultHandler(string $layer) : string
    {
        return $this->defaultHandlersMap[$layer];
    }
}

