<?php

namespace Jalno\UserLogger\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return config(
            sprintf('jalno-user-logger.database.models-connection.%s', static::class),
            config('jalno-user-logger.database.models-connection-default', $this->connection)
        );
    }

    public function getTable()
    {
        return $this->getConnection()->getDatabaseName().'.'.parent::getTable();
    }
}
