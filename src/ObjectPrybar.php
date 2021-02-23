<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use ReflectionClass;

/**
 * @see https://github.com/livewire/livewire/blob/d1fc4f0f5fc57e93b19f5b59a3440083377fadda/src/ObjectPrybar.php
 */
class ObjectPrybar
{
    protected object $obj;

    protected ReflectionClass $reflected;

    public function __construct($obj)
    {
        $this->obj = $obj;
        $this->reflected = new ReflectionClass($obj);
    }

    public function getProperty($name)
    {
        $property = $this->reflected->getProperty($name);

        $property->setAccessible(true);

        return $property->getValue($this->obj);
    }

    public function setProperty($name, $value)
    {
        $property = $this->reflected->getProperty($name);

        $property->setAccessible(true);

        $property->setValue($this->obj, $value);
    }
}
