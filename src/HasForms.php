<?php

namespace ByTIC\Controllers\Behaviors;

use ByTIC\Common\Records\Traits\HasForms\RecordTrait as HasFormsRecord;
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
     * @param HasFormsRecord $model
     * @param null $action
     * @return Form
     */
    public function getModelForm($model, $action = null)
    {
        return $model->getForm($action);
    }

}