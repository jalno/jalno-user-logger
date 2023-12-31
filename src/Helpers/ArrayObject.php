<?php

namespace Jalno\UserLogger\Helpers;

class ArrayObject extends \stdClass implements \Serializable
{
    public function serialize(): string
    {
        return serialize(get_object_vars($this));
    }

    public function unserialize(string $data): void
    {
        foreach (unserialize($data) as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
