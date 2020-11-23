<?php

namespace ByTIC\Controllers\Behaviors;

use Nip\View;

/**
 * Class PageTrait
 * @package KM42\Common\Modules\Frontend\Controllers\Traits
 */
trait PageTrait
{
    protected function beforeAction()
    {
        parent::beforeAction();
        $this->initViewFlashMessages();
        $this->setBreadcrumbs();
    }

    protected function initViewFlashMessages()
    {
        $messages = [];
        $messages["error"] = flash_get("error");
        $messages["success"] = flash_get("success");
        $messages["warning"] = flash_get("warning");
        $this->getView()->set("messages", $messages);
    }

    /**
     * @return View
     */
    public abstract function getView();

    protected function setBreadcrumbs()
    {
        $this->getView()->Breadcrumbs()->addItem("Home", BASE_URL);
    }

    /**
     * @return View
     */
    protected abstract function getViewObject();
}
