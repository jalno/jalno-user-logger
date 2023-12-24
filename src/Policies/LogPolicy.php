<?php

namespace Jalno\UserLogger\Policies;

use dnj\UserLogger\Policies\LogPolicy as Base;
use Jalno\UserLogger\Contracts\ILog;

class LogPolicy extends Base
{
    public function getModel(): string
    {
        return ILog::class;
    }
}
