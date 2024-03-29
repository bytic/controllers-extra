<?php

declare(strict_types=1);

namespace ByTIC\Controllers\Behaviors\Models;

use Nip\Records\AbstractModels\Record;
use Nip\Utility\Arr;
use Nip\Utility\Url;

/**
 * Trait HasAfterActions
 * @package ByTIC\Controllers\Behaviors\Models
 */
trait HasAfterActions
{
    protected $afterActionsTypes = [
        'after-add',
        'after-edit',
        'after-delete',
    ];

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

        $url = $this->getAfterUrl(
            $action_name,
            $this->afterActionUrlDefault($type, $item)
        );
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
     * @param string $url
     * @param string $flash
     */
    protected function setAfterUrlFlash($url, $flash, $types = null)
    {
        $types = $types ?: $this->afterActionsTypes;
        foreach ($types as $type) {
            $this->setAfterUrl($type, $url);
            $this->setAfterFlashName($type, $flash);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setAfterUrl($key, $value = null)
    {
        $this->_urls[$key] = $value;
    }

    protected function setAfterUrlIfNotSet($key, $value = null)
    {
        if (isset($this->_urls[$key])) {
            return;
        }
        $this->setAfterUrl($key, $value);
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

    /**
     * @param $type
     * @param $item
     * @return mixed|string|null
     */
    protected function afterActionUrlDefault($type, $item = null)
    {
        switch ($type) {
            case 'delete':
                return $this->getModelManager()->compileURL('index');
        }
        return $item->compileURL('view', Arr::only($this->getRequest()->query->all(), ['_format']));
    }
}
