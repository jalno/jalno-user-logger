<?php

namespace dnj\UserLogger;

use dnj\AAA\Models\User;

trait ModelHelpers
{
    protected function getUserTable(): ?string
    {
        return (new User())->getTable();
    }
}
