<?php

declare(strict_types=1);

namespace Marussia\EventBus\Exceptions;

class ActionIsNotAccessedForMemberException extends \Exception
{
    public function __construct(string $member, string $action)
    {
        parent::__construct('This action ' . $action . ' is not accessed for member ' . $member);
    }
}
