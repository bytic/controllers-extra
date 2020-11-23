<?php

namespace ByTIC\Controllers\Behaviors\Models;

use Nip\Database\Query\Select as SelectQuery;
use Nip\Records\Collections\Collection;
use Nip\Records\RecordManager;

/**
 * Class HasModelLister
 *
 * @package ByTIC\Controllers\Behaviors\Models
 *
 * @method RecordManager getModelManager()
 */
trait HasModelLister
{
    use \Nip\Records\Filters\Controllers\HasFiltersTrait;

    /**
     * Does the model listing
     *
     * @return void
     */
    protected function doModelsListing()
    {
        $query = $this->newIndexQuery();
        $filters = $this->getRequestFilters();
        $query = $this->getModelManager()->filter($query, $filters);

        $pageNumber = intval($this->getRequest()->query->get('page', 1));
        $itemsPerPage = $this->getRecordPaginator()->getItemsPerPage();

        $this->getRecordPaginator()->setPage($pageNumber);
        $this->getRecordPaginator()->paginate($query);

        $items = $this->indexFindItems($query);
        $this->indexPrepareItems($items);

        $this->getView()->with([
            'filters' => $filters,
            'filtersManager'  => $this->getModelManager()->getFilterManager(),
            'title'  => $this->getModelManager()->getLabel('title')
        ]);

        $this->getView()->Paginator()->setPaginator($this->getRecordPaginator());
        $this->getView()->Paginator()->setURL($this->getModelManager()->getURL(is_object($filters) ? $filters->toArray() : $filters));

//        if ($pageNumber * $itemsPerPage < $this->recordLimit) {
//        } else {
//            $this->getView()->set('recordLimit', true);
//        }
    }

    /**
     * @return \Nip\Database\Query\Select
     */
    protected function newIndexQuery()
    {
        return $this->getModelManager()->paramsToQuery();
    }

    /**
     * @param null $session
     * @return \Nip\Records\Filters\Sessions\Session
     */
    protected function getRequestFilters($session = null)
    {
        $filterManager = $this->getModelManager()->getFilterManager();
        $filterManager->setRequest($this->getRequest());
        return $filterManager->getSession($session);
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
}
