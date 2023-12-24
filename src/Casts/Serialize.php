<?php

namespace Jalno\UserLogger\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Serialize implements CastsAttributes
{
    private $lastUnserializeCallback = null;

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $this->preUnserialize();
        try {
            return unserialize($value);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->postUnserialize();
        }
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        throw new \DomainException('Can not save data to avoid data corruption');
    }

    protected function preUnserialize(): void
    {
        $this->lastUnserializeCallback = ini_get('unserialize_callback_func');
        ini_set('unserialize_callback_func', self::class . '::unserializeCallback');
    }

    protected function postUnserialize(): void
    {
        if ($this->lastUnserializeCallback) {
            ini_set('unserialize_callback_func', $this->lastUnserializeCallback);
        }
    }

    protected static function unserializeCallback($className)
    {
        class_alias(ArrayObject::class, $className);
    }
}
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
