<?php

declare(strict_types=1);

namespace Marussia\EventBus\Entities;

use Marussia\EventBus\ResultInterface;

class Result implements ResultInterface
{
    public $status;
    
    public $data = [];
    
    public $timeout;
}
