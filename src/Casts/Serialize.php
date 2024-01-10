<?php

namespace Jalno\UserLogger\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Jalno\UserLogger\Helpers\ArrayObject;


class Serialize implements CastsAttributes
{
    private ?string $lastUnserializeCallback = null;

    /**
     * {@inheritdoc}
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
        if ($model->exists or $model->getKey()) {
            throw new \DomainException(sprintf(
                'Can not save data on existing model (%s:%s) to avoid data corruption',
                get_class($model),
                $model->getKey()
            ));
        }
        return serialize(json_decode(
            json_encode(
                $value,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            true
        ));
    }

    protected function preUnserialize(): void
    {
        $this->lastUnserializeCallback = ini_get('unserialize_callback_func');
        ini_set('unserialize_callback_func', self::class . '::unserializeCallback');
    }

    protected function postUnserialize(): void
    {
        ini_set('unserialize_callback_func', $this->lastUnserializeCallback ?: "");
    }

    protected static function unserializeCallback(string $className): void
    {
        class_alias(ArrayObject::class, $className);
    }
}
