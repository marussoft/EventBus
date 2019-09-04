<?php

declare(strict_types=1);

namespace Marussia\EventBus\Factories;

use Marussia\EventBus\Entities\Task;
use Marussia\EventBus\Entities\Subscribe;
use Marussia\EventBus\Entities\Result;
use Marussia\EventBus\ConfigProvider;
use Marussia\EventBus\Repository;

class TaskFactory
{
    // Репозиторий всех участников
    private $repository;

    private $config;
    
    public function __construct(Repository $repository, ConfigProvider $config)
    {
        $this->repository = $repository;
        $this->config = $config;
    }
    
    public function createSubscribed(Subscribe $subscribe, Result $result) : Task
    {
        $task = new Task();
        $task->action = $subscribe->action;
        $task->contitions = $subscribe->conditions;
        $task->data = $result->data;
        
        $this->prepareMemberParams($task, $subscribe->memberWithLayer);
        
        return $task;
    }
    
    public function createUpper(string $memberWithLayer, string $action, $data = null)
    {
        $task = new Task();
        $task->action = $action;
        $task->contitions = [];
        $task->data = $data = null;
        
        $this->prepareMemberParams($task, $memberWithLayer);
        
        return $task;
    }
    
    private function prepareMemberParams(Task $task, string $memberWithLayer)
    {
        $member = $this->repository-getMember($memberWithLayer);
        $task->layer = $member->layer;
        $task->memberName = $member->name;
        $task->handler = $member->handler;
        $task->filters = $member->filters;
    }
}

