<?php

namespace Arad\Araduser\Tasks\Eloquent;

trait Connectionable
{
    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return config(
            sprintf('araduser-tasks.database.models-connection.%s', static::class),
            config('araduser-tasks.database.models-connection-default', $this->connection)
        );
    }
}
