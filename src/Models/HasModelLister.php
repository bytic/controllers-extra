<?php

declare(strict_types=1);

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

        $this->indexPreparePaginator($filters);

        $query = $this->getModelManager()->filter($query, $filters);
        $this->getRecordPaginator()->paginate($query);

        $items = $this->indexFindItems($query);
        $this->indexPrepareItems($items);

        $this->payload()->with([
                                   'items' => $items,
                                   'filters' => $filters,
                                   'filtersManager' => $this->getModelManager()->getFilterManager(),
                                   'title' => $this->getModelManager()->getLabel('title')
                               ]);
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


        return $items;
    }

    /**
     * @param Collection $items
     */
    protected function indexPrepareItems($items)
    {
    }

    /**
     * @param $filters
     * @return void
     */
    protected function indexPreparePaginator($filters)
    {
        $pageNumber = intval($this->getRequest()->query->get('page', 1));
        $recordPaginator = $this->getRecordPaginator();
        $recordPaginator->setPage($pageNumber);

        $payloadPaginator = $this->getView()->Paginator();
        $payloadPaginator->setPaginator($this->getRecordPaginator());
        $payloadPaginator->setURL(
            $this->getModelManager()->getURL(is_object($filters) ? $filters->toArray() : $filters)
        );

//        $itemsPerPage = $recordPaginator->getItemsPerPage();
        //        if ($pageNumber * $itemsPerPage < $this->recordLimit) {
//        } else {
//            $this->payload()->set('recordLimit', true);
//        }
    }
}
