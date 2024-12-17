<?php

namespace ByTIC\Controllers\Behaviors;

use ByTIC\Records\Behaviors\HasForms\HasFormsRecordTrait;
use Nip_Form_Model as Form;

/**
 * Trait HasForms
 * @package ByTIC\Controllers\Behaviors
 */
trait HasForms
{
    /**
     * @var Form[]
     */
    protected $forms;

    /**
     * @param $name
     * @param Form $form
     */
    protected function addForm($name, $form)
    {
        $this->forms[$name] = $form;
    }

    /**
     * @param $name
     * @return Form
     */
    protected function getForm($name)
    {
        return $this->forms[$name];
    }

    /**
     * @return Form[]
     */
    protected function getForms()
    {
        return $this->forms;
    }

    /**
     * @param HasFormsRecordTrait $model
     * @param null $action
     * @return Form
     */
    public function getModelForm($model, $action = null)
    {
        $action = $action ?? $this->getAction();
        $form = $this->generateModelFormByController($model, $action);
        if ($form) {
            return $form;
        }
        return $this->generateModelFormByModel($model, $action);
    }

    /**
     * @param $model
     * @param $action
     * @return mixed|null
     */
    protected function generateModelFormByController($model, $action = null)
    {
        $action = $action ?? $this->getAction();
        if (!method_exists($this, 'getModelFormClass')) {
            return null;
        }
        $class = $this->getModelFormClass($model, $action);
        if (!class_exists($class)) {
            return null;
        }
        $form = new $class();
        $form->setModel($model);
        return $form;
    }

    protected function generateModelFormByModel($model, $action)
    {
        $action = $action ?? $this->getAction();
        $class = $model->getManager()->getFormClassName($action);
        if (class_exists($class)) {
            return $model->getForm($action);
        }

        return $model->getForm('Details');
    }
}
