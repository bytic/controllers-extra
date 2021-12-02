<?php

namespace ByTIC\Controllers\Behaviors;

use ByTIC\Common\Records\Traits\HasStatus\RecordsTrait as HasStatusRecordsTrait;
use ByTIC\Common\Records\Traits\HasStatus\RecordTrait;
use ByTIC\Common\Records\Traits\I18n\RecordsTrait as I18nRecordsTrait;
use Nip\Records\AbstractModels\Record;
use Nip\Records\AbstractModels\RecordManager;
use Nip\View;

/**
 * Class HasStatus
 * @package ByTIC\Controllers\Behaviors
 *
 * @method Record|RecordTrait getModelFromRequest
 * @method RecordManager|HasStatusRecordsTrait|I18nRecordsTrait getModelManager
 */
trait HasSmartProperty
{
    /**
     * @param $name
     */
    public function initViewProperty($name)
    {
        $definitionName = $this->getSmartPropertyDefinitionName($name);
        $this->getView()->set(
            inflector()->pluralize($name),
            $this->getModelManager()->getSmartPropertyItems($definitionName)
        );
    }

    protected function getSmartPropertyDefinitionName($name)
    {
        return inflector()->classify($name);
    }

    /**
     * @return View
     */
    abstract public function getView();

    public function changeSmartProperty()
    {
        $this->doChangeSmartProperty($_GET['property']);
    }

    /**
     * @param $name
     */
    protected function doChangeSmartProperty($name)
    {
        $item = $this->getModelFromRequest();
        $definitionName = $this->getSmartPropertyDefinitionName($name);
        $value = $_GET[$name];
        $availableValues = $this->getModelManager()->getSmartPropertyValues($definitionName, 'name');
        if (in_array($value, $availableValues)) {
            $item->updateSmartProperty($definitionName, $value);
            $this->changeSmartPropertyRedirect($name, $item);
        } else {
            $redirect = $_SERVER['HTTP_REFERER'];
            $this->flashRedirect(
                $this->getModelManager()->getMessage(inflector()->pluralize($name) . '.invalid-value'),
                $redirect,
                'error'
            );
        }
    }

    /**
     * @param string $name
     * @param Record $item
     */
    public function changeSmartPropertyRedirect($name, $item)
    {
        $redirect = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $item->getURL();
        $this->flashRedirect($this->getModelManager()->getMessage(inflector()->pluralize($name) . '.success'), $redirect);
    }

    /**
     * @param $message
     * @param $url
     * @param string $type
     * @param bool $name
     * @return mixed
     */
    abstract protected function flashRedirect($message, $url, $type = 'success', $name = false);
}
