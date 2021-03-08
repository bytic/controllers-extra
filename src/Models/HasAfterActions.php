<?php

namespace ByTIC\Controllers\Behaviors\Models;

use Nip\Records\AbstractModels\Record;

/**
 * Trait HasAfterActions
 * @package ByTIC\Controllers\Behaviors\Models
 */
trait HasAfterActions
{
    protected $_urls = [];
    protected $_flash = [];

    /**
     * @param Record $item
     */
    public function addRedirect($item)
    {
        $this->afterActionRedirect('add', $item);
    }

    /**
     * @param Record $item
     */
    public function duplicateRedirect($item)
    {
        $this->afterActionRedirect('duplicate', $item);
    }

    /**
     * @param Record $item
     */
    protected function deleteRedirect($item)
    {
        $this->afterActionRedirect('delete', $item);
    }

    /**
     * @param Record|boolean $item
     */
    protected function viewRedirect($item = null)
    {
        $this->afterActionRedirect('edit', $item);
    }

    /**
     * @param string $type
     * @param Record $item
     * @return mixed
     */
    protected function afterActionRedirect($type, $item)
    {
        if ($item == null) {
            $item = $this->getModelFromRequest();
            trigger_error('$item needed in afterActionRedirect', E_USER_DEPRECATED);
        }

        $action_name = 'after-' . $type;

        $url = $this->getAfterUrl($action_name, $item->getURL());
        $flash_name = $this->getAfterFlashName($action_name, $this->getModelManager()->getController());

        return $this->flashRedirect($this->getModelManager()->getMessage($type), $url, 'success', $flash_name);
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return string
     */
    protected function getAfterUrl($key, $default = null)
    {
        return isset($this->_urls[$key]) && $this->_urls[$key] ? $this->_urls[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setAfterUrl($key, $value = null)
    {
        $this->_urls[$key] = $value;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return string
     */
    protected function getAfterFlashName($key, $default = null)
    {
        return isset($this->_flash[$key]) && $this->_flash[$key] ? $this->_flash[$key] : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setAfterFlashName($key, $value = null)
    {
        $this->_flash[$key] = $value;
    }
}