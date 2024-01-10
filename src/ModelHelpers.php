<?php

namespace Jalno\UserLogger;

use Jalno\AAA\Models\User;

trait ModelHelpers
{
    protected function getUserTable(): ?string
    {
        return (new User())->getTable();
    }
}
