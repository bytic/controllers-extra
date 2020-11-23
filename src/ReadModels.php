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

    protected function doModelsListing()
    {
        $query = $this->newIndexQuery();
        $filters = $this->getRequestFilters();
        $query = $this->getModelManager()->filter($query, $filters);

        $pageNumber = intval($_GET['page']);
        $itemsPerPage = $this->getRecordPaginator()->getItemsPerPage();

        if ($pageNumber * $itemsPerPage < $this->recordLimit) {
            $this->getRecordPaginator()->setPage($pageNumber);
            $this->getRecordPaginator()->paginate($query);

            $items = $this->indexFindItems($query);
            $this->indexPrepareItems($items);

            $this->getView()->set('filters', $filters);
            $this->getView()->set('title', $this->getModelManager()->getLabel('title'));

            $this->getView()->Paginator()->setPaginator($this->getRecordPaginator());
            $this->getView()->Paginator()->setURL($this->getModelManager()->getURL($filters));
        } else {
            $this->getView()->set('recordLimit', true);
        }
    }

    /**
     * @return \Nip\Database\Query\Select
     */
    protected function newIndexQuery()
    {
        return $this->getModelManager()->paramsToQuery();
    }

    /**
     * @return mixed
     */
    protected function getRequestFilters()
    {
        return $this->getModelManager()->requestFilters($this->getRequest());
    }

    /**
     * @param SelectQuery $query
     * @return Collection
     */
    protected function indexFindItems($query)
    {
        $items = $this->getModelManager()->findByQuery($query);
        $this->getRecordPaginator()->count();

        $this->getView()->set('items', $items);

        return $items;
    }

    /**
     * @param Collection $items
     */
    protected function indexPrepareItems($items)
    {
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

    protected function initViewModelManager()
    {
        if (!$this->getView()->has('modelManager')) {
            $this->getView()->set('modelManager', $this->getModelManager());
        }
    }

    protected function setBreadcrumbs()
    {
        parent::setBreadcrumbs();
        $this->setClassBreadcrumbs();
    }

    /**
     * @param bool $parent
     */
    protected function setClassBreadcrumbs($parent = false)
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
    protected function setItemBreadcrumbs($item = false)
    {
        $item = $item ? $item : $this->item;
        $this->getView()->Breadcrumbs()->addItem($item->getName(), $item->getURL());

        $this->getView()->Meta()->prependTitle($item->getName());
    }
}
