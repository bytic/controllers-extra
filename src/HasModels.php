<?php

namespace ByTIC\Controllers\Behaviors;

use ByTIC\Controllers\Behaviors\Models\HasModelFinder;
use ByTIC\Controllers\Behaviors\Models\HasModelManagerTrait;
use Nip\Dispatcher\Dispatcher;
use Nip\Request;

/**
 * Class HasModels
 * @package ByTIC\Controllers\Behaviors
 *
 * @method string getName
 * @method Request getRequest
 * @method Dispatcher getDispatcher
 *
 * @method mixed call($action = false, $controller = false, $module = false, $params = [])
 */
trait HasModels
{
    use HasModelManagerTrait;
    use HasModelFinder;

    /**
     * Get Model namespace
     *
     * @return string
     */
    public function getModelNamespace()
    {
        return $this->getRootNamespace().'Models\\';
    }

    /**
     * @return string
     */
    abstract public function getRootNamespace();

    /**
     * Is namespaced controller
     *
     * @return bool
     */
    abstract public function isNamespaced();

    /**
     * @return string
     */
    abstract public function getClassName();
}