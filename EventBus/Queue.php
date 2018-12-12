<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Queue
{
    private $splQueue;

    public function __construct()
    {
        $this->splQueue = new \SplQueue;
        $this->splQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }

    public function enqueue($param)
    {
        $this->splQueue->enqueue($param);
    }
    
    public function pop()
    {
        return $this->splQueue->pop();
    }
}
