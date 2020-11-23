<?php

namespace ByTIC\Controllers\Behaviors;

use Nip\Database\Query\Select as SelectQuery;
use Nip\Records\Collections\Collection;
use Nip\Records\Record;
use Nip\Records\RecordManager;
use Nip\Request;
use Nip\View;
use Nip_Form as Form;

/**
 * Class ModelsTrait
 * @package ByTIC\Controllers\Behaviors
 *
 * @method string getModel()
 * @method RecordManager getModelManager()
 * @method View getView()
 * @method Request getRequest()
 * @method Form getModelForm($model, $action = null)
 * @method Record getModelFromRequest($key = false)
 * @method string flashRedirect($message, $url, $type = 'success', $name = false)
 */
trait ReadModels
{
    use HasRecordPaginator;
    use Models\HasModelLister;

    protected $urls = [];

    protected $recordLimit = 1001;

    public function index()
    {
        $this->doModelsListing();
    }

    public function view()
    {
        $item = $this->getViewItemFromRequest();
        $this->getView()->set('item', $item);
        $this->getView()->Meta()->prependTitle($item->getName());
    }

    /**
     * @return Record
     */
    protected function getViewItemFromRequest()
    {
        return $this->getModelFromRequest();
    }

    protected function beforeAction()
    {
        parent::beforeAction();
        $this->getView()->set('section', inflector()->underscore($this->getModel()));
    }

    protected function afterAction()
    {
        $this->initViewModelManager();
        parent::afterAction();
    }

    protected function setBreadcrumbs()
    {
        parent::setBreadcrumbs();
        $this->setClassBreadcrumbs();
    }

    /**
     * @param bool $parent
     */
    public function setClassBreadcrumbs($parent = false)
    {
        $this->getView()->Breadcrumbs()->addItem(
            $this->getModelManager()->getLabel('title'),
            $this->getModelManager()->getURL()
        );
        $this->getView()->Meta()->prependTitle($this->getModelManager()->getLabel('title'));
    }

    /**
     * @param bool|Record $item
     */
    public function setItemBreadcrumbs($item = false)
    {
        $item = $item ? $item : $this->item;
        $this->getView()->Breadcrumbs()->addItem($item->getName(), $item->getURL());

        $this->getView()->Meta()->prependTitle($item->getName());
    }
}
