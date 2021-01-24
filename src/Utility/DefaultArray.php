<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

/**
 * An array that has a default values automatically set for it whenever a key
 * that does not exist is accessed.
 *
 * @template TKey
 * @template TValue
 */
class DefaultArray implements \ArrayAccess, \IteratorAggregate, \JsonSerializable
{
    /** @var array<TKey, TValue> */
    private $store = [];
    private $defaultValue;

    /**
     * @param callable|mixed $defaultValue
     */
    public function __construct($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    public function getAsArray(): array
    {
        return $this->store;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->store);
    }

    public function jsonSerialize()
    {
        return $this->store;
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function &offsetGet($offset)
    {
        if (!isset($this->store[$offset])) {
            $this->store[$offset] = $this->getDefaultValue();
        }

        return $this->store[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->store[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->store[$offset]);
    }

    private function getDefaultValue()
    {
        $callback = $this->defaultValue;

        if (is_callable($callback)) {
            return $callback();
        }

        if (is_string($callback) && class_exists($callback)) {
            return new $callback();
        }

        return $callback;
    }
}
