<?php

namespace ByTIC\Controllers\Behaviors\Models;

use Exception;
use Nip\Records\AbstractModels\RecordManager;
use Nip\Records\Locator\ModelLocator;

/**
 * Class HasManagerTrait
 * @package ByTIC\Controllers\Behaviors\Models
 */
trait HasModelManagerTrait
{
    protected $model = null;

    protected $modelManager = null;

    /**
     * Set the model manager in view
     *
     * @return void
     * @throws Exception
     */
    protected function initViewModelManager()
    {
        if (!$this->getView()->has('modelManager')) {
            $this->getView()->set('modelManager', $this->getModelManager());
        }
    }

    /**
     * Get Records Model Manager
     *
     * @return RecordManager
     * @throws Exception
     */
    protected function getModelManager()
    {
        if ($this->modelManager == null) {
            $this->initModelManager();
        }

        return $this->modelManager;
    }

    /**
     * Init the Model Manager
     *
     * @throws Exception
     * @return void
     */
    protected function initModelManager()
    {
        $managerClass = $this->getModel();
        $modelManager = $this->newModelManagerInstance($this->getModel());
        if ($modelManager instanceof RecordManager) {
            $this->modelManager = $this->newModelManagerInstance($this->getModel());
        } else {
            throw new Exception(
                "invalid ModelManager name [$managerClass] 
                for controller [".$this->getClassName()."]"
            );
        }
    }

    /**
     * Get the model name
     *
     * @return null|string
     */
    public function getModel()
    {
        if ($this->model === null) {
            $this->initModel();
        }

        return $this->model;
    }

    /**
     * Set the model manager name
     *
     * @param string $model Model name
     *
     * @return void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Init the model manager name
     *
     * @return void
     */
    protected function initModel()
    {
        $this->setModel($this->generateModelName());
    }

    /**
     * Generate the model name from controller name
     *
     * @return string
     */
    protected function generateModelName()
    {
        $name = str_replace(["async-", "modal-"], '', $this->getName());
        $manager = ModelLocator::get($name);

        return get_class($manager);
    }

    /**
     * Get Model Manager Instance
     * @param string $managerName
     * @return RecordManager
     */
    protected function newModelManagerInstance($managerName)
    {
        return ModelLocator::get($managerName);
    }
}
